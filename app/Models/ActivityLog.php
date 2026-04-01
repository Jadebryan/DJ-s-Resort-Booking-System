<?php

namespace App\Models;

use App\Models\TenantModel\Tenant as TenantUser;
use App\Models\TenantUserModel\RegularUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;

class ActivityLog extends Model
{
    public const GENESIS = 'GENESIS';

    protected $connection = 'tenant';

    protected $table = 'activity_logs';

    protected $fillable = [
        'tenant_user_id',
        'regular_user_id',
        'action',
        'description',
        'entity_type',
        'entity_id',
        'payload',
        'previous_hash',
        'row_hash',
        'ip_address',
        'actor_type',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (): bool {
            return false;
        });

        static::deleting(function (): bool {
            return false;
        });
    }

    public function user()
    {
        return $this->belongsTo(TenantUser::class, 'tenant_user_id');
    }

    public function regularUser()
    {
        return $this->belongsTo(RegularUser::class, 'regular_user_id');
    }

    /**
     * Append-only audit entry with HMAC-SHA256 chain over canonical payload JSON.
     *
     * @param  array<string, mixed>  $context
     *   entity_type, entity_id, metadata, actor_type, tenant_user_id, regular_user_id, ip
     */
    public static function log(string $action, ?string $description = null, array $context = []): self
    {
        $entityType = $context['entity_type'] ?? null;
        $entityId = isset($context['entity_id']) ? (int) $context['entity_id'] : null;
        $metadata = is_array($context['metadata'] ?? null) ? $context['metadata'] : [];
        $ip = $context['ip'] ?? null;

        $resolved = static::resolveActors($context);

        return DB::connection('tenant')->transaction(function () use (
            $action,
            $description,
            $entityType,
            $entityId,
            $metadata,
            $ip,
            $resolved
        ) {
            return static::insertChainedRow(
                $action,
                $description,
                $entityType,
                $entityId,
                $metadata,
                $ip,
                $resolved
            );
        });
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $resolved
     */
    protected static function insertChainedRow(
        string $action,
        ?string $description,
        ?string $entityType,
        ?int $entityId,
        array $metadata,
        ?string $ip,
        array $resolved
    ): self {
        $last = static::query()->orderByDesc('id')->lockForUpdate()->first();
        $prevRowHash = $last && filled($last->row_hash) ? (string) $last->row_hash : null;
        $previousStored = $prevRowHash;
        $materialPrefix = $prevRowHash ?? self::GENESIS;

        $eventUuid = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $payloadBody = [
            'v' => 1,
            'uuid' => $eventUuid,
            'ts' => $timestamp,
            'action' => $action,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'tenant_user_id' => $resolved['tenant_user_id'],
            'regular_user_id' => $resolved['regular_user_id'],
            'actor_type' => $resolved['actor_type'],
            'metadata' => $metadata,
        ];
        $payloadBody = static::stripNulls($payloadBody);
        $canonical = static::canonicalJson($payloadBody);
        $rowHash = hash_hmac('sha256', $materialPrefix . '|' . $canonical, static::hmacKey());

        return static::query()->create([
            'tenant_user_id' => $resolved['tenant_user_id'],
            'regular_user_id' => $resolved['regular_user_id'],
            'action' => $action,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload' => $payloadBody,
            'previous_hash' => $previousStored,
            'row_hash' => $rowHash,
            'ip_address' => $ip ?? request()->ip(),
            'actor_type' => $resolved['actor_type'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function verifyChain(): array
    {
        $errors = [];
        $warnings = [];
        $prevRowHash = null;

        $rows = static::query()->orderBy('id')->get();

        foreach ($rows as $row) {
            if (! filled($row->row_hash)) {
                $warnings[] = 'Row #' . $row->id . ' has no row_hash (legacy).';

                continue;
            }

            if ($prevRowHash !== null && (string) $row->previous_hash !== (string) $prevRowHash) {
                $errors[] = 'Row #' . $row->id . ': previous_hash does not match prior row_hash (chain break).';
            }

            if ($prevRowHash === null && filled($row->previous_hash)) {
                $errors[] = 'Row #' . $row->id . ': previous_hash set without a prior chained row.';
            }

            $prefix = filled($row->previous_hash) ? (string) $row->previous_hash : self::GENESIS;
            $payload = is_array($row->payload) ? $row->payload : [];
            try {
                $canonical = static::canonicalJson($payload);
            } catch (JsonException $e) {
                $errors[] = 'Row #' . $row->id . ': invalid payload JSON: ' . $e->getMessage();
                continue;
            }

            $expected = hash_hmac('sha256', $prefix . '|' . $canonical, static::hmacKey());
            if (! hash_equals((string) $row->row_hash, $expected)) {
                $errors[] = 'Row #' . $row->id . ': row_hash mismatch (tamper or key change).';
            }

            $prevRowHash = (string) $row->row_hash;
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    protected static function hmacKey(): string
    {
        $dedicated = config('integrity.audit_hmac_key');
        if (is_string($dedicated) && $dedicated !== '') {
            return $dedicated;
        }

        return (string) config('app.key');
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected static function resolveActors(array $context): array
    {
        $actorType = $context['actor_type'] ?? null;
        if ($actorType === null) {
            if (auth('tenant')->check()) {
                $actorType = 'tenant_staff';
            } elseif (auth('regular_user')->check()) {
                $actorType = 'guest';
            } else {
                $actorType = 'system';
            }
        }

        $tenantUserId = null;
        $regularUserId = null;

        if (array_key_exists('tenant_user_id', $context)) {
            $v = $context['tenant_user_id'];
            $tenantUserId = ($v !== null && $v !== '') ? (int) $v : null;
        } elseif ($actorType === 'tenant_staff') {
            $tid = auth('tenant')->id();
            $tenantUserId = $tid ? (int) $tid : null;
        }

        if (array_key_exists('regular_user_id', $context)) {
            $v = $context['regular_user_id'];
            $regularUserId = ($v !== null && $v !== '') ? (int) $v : null;
        } elseif ($actorType === 'guest') {
            $rid = auth('regular_user')->id();
            $regularUserId = $rid ? (int) $rid : null;
        }

        if ($actorType === 'guest') {
            $tenantUserId = null;
        }
        if ($actorType === 'tenant_staff') {
            $regularUserId = null;
        }
        if ($actorType === 'system') {
            if (! array_key_exists('tenant_user_id', $context)) {
                $tenantUserId = null;
            }
            if (! array_key_exists('regular_user_id', $context)) {
                $regularUserId = null;
            }
        }

        return [
            'actor_type' => $actorType,
            'tenant_user_id' => $tenantUserId,
            'regular_user_id' => $regularUserId,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function stripNulls(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v === null) {
                continue;
            }
            if (is_array($v)) {
                $nested = static::stripNulls($v);
                if ($nested !== []) {
                    $out[$k] = $nested;
                }
                continue;
            }
            $out[$k] = $v;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected static function canonicalJson(array $body): string
    {
        $sorted = static::ksortRecursive($body);

        return json_encode($sorted, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>  $arr
     * @return array<string, mixed>
     */
    protected static function ksortRecursive(array $arr): array
    {
        ksort($arr, SORT_STRING);
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = static::ksortRecursive($v);
            }
        }

        return $arr;
    }
}
