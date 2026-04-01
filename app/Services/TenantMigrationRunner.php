<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantUpdateLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TenantMigrationRunner
{
    /**
     * Run tenant-schema migrations for one landlord tenant database. Logs to
     * storage/logs/tenant-updates.log and tenant_update_logs. Does not bump
     * tenants.version (callers do that after success when appropriate).
     *
     * @return array{success: bool, message: string}
     */
    public function run(Tenant $landlordTenant, string $targetVersion): array
    {
        $tenantId = $landlordTenant->id;
        $status = 'failure';
        $message = '';

        try {
            config([
                'database.connections.tenant.database' => $landlordTenant->database_name,
                'database.connections.tenant.host' => env('DB_HOST', '127.0.0.1'),
                'database.connections.tenant.port' => env('DB_PORT', '3306'),
                'database.connections.tenant.username' => env('DB_USERNAME', 'root'),
                'database.connections.tenant.password' => env('DB_PASSWORD', ''),
            ]);
            DB::purge('tenant');
            DB::reconnect('tenant');

            $exitCode = Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force' => true,
            ]);

            $output = trim(Artisan::output());
            if ($exitCode !== 0) {
                throw new \RuntimeException($output !== '' ? $output : 'Migration exited with code '.$exitCode);
            }

            $status = 'success';
            $message = $output !== '' ? $output : 'Migrations completed.';
        } catch (\Throwable $e) {
            $message = $e->getMessage();
        } finally {
            $this->appendTenantUpdatesLog($tenantId, $status, $message);
            TenantUpdateLog::query()->create([
                'tenant_id' => $tenantId,
                'version' => $targetVersion,
                'status' => $status,
                'message' => $message,
            ]);
        }

        return [
            'success' => $status === 'success',
            'message' => $message,
        ];
    }

    private function appendTenantUpdatesLog(int $tenantId, string $status, string $message): void
    {
        $payload = json_encode([
            'tenant_id' => $tenantId,
            'status' => $status,
            'error_message' => $message,
            'timestamp' => now()->toIso8601String(),
        ], JSON_UNESCAPED_UNICODE);

        File::append(storage_path('logs/tenant-updates.log'), $payload.PHP_EOL);
    }
}
