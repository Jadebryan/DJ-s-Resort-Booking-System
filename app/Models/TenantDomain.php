<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDomain extends Model
{
    use HasFactory;

    /**
     * Allowed values for the `domain` column: a single DNS label (e.g. jeddsresort) or a full hostname
     * (e.g. www.example.com). The configured tenant_host_suffix is not stored.
     */
    public const STORED_DOMAIN_REGEX = '/^(?:([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)|([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9.-]+)$/i';

    protected $table = 'tenant_domains';

    protected $fillable = [
        'tenant_id',
        'domain',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the tenant that owns this domain.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Resolve a row from the HTTP Host header (exact match, or host ending with ".{tenant_host_suffix}").
     */
    public static function forRequestHost(string $requestHost): ?self
    {
        $host = strtolower($requestHost);

        $exact = static::query()->where('domain', $host)->with('tenant')->first();
        if ($exact) {
            return $exact;
        }

        $suffix = strtolower(trim((string) config('tenancy.tenant_host_suffix', 'localhost')));
        if ($suffix === '' || ! str_ends_with($host, '.'.$suffix)) {
            return null;
        }

        $without = substr($host, 0, -strlen($suffix) - 1);

        return $without === ''
            ? null
            : static::query()->where('domain', $without)->with('tenant')->first();
    }
}
