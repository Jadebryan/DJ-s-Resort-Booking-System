<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantUpdateLog;
use App\Services\TenantMigrationRunner;
use Illuminate\Console\Command;

class TenantsMigrateSafeCommand extends Command
{
    protected $signature = 'tenants:migrate:safe
                            {--tenants= : Comma-separated landlord tenant IDs (e.g. 1,2)}
                            {--retry-failed : Only tenants whose latest update log is a failure}
                            {--force : Run without confirmation in production}';

    protected $description = 'Run tenant DB migrations per tenant; continue on failure; log each result';

    public function handle(TenantMigrationRunner $runner): int
    {
        if (! $this->option('force') && $this->laravel->environment('production')) {
            $this->error('Use --force to run migrations in production.');

            return self::FAILURE;
        }

        $targetVersion = config('app.version');

        $query = Tenant::query()->orderBy('id');

        if ($this->option('retry-failed')) {
            $failedIds = $this->failedTenantIds();
            if ($failedIds === []) {
                $this->warn('No tenants with a latest failed update log.');

                return self::SUCCESS;
            }
            $query->whereIn('id', $failedIds);
        }

        $idsOption = $this->option('tenants');
        if (is_string($idsOption) && $idsOption !== '') {
            $ids = array_values(array_filter(array_map('intval', explode(',', $idsOption))));
            if ($ids === []) {
                $this->error('Invalid --tenants value.');

                return self::FAILURE;
            }
            $query->whereIn('id', $ids);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants matched.');

            return self::SUCCESS;
        }

        $this->info('Target app version: '.$targetVersion);
        $ok = 0;
        $fail = 0;

        foreach ($tenants as $tenant) {
            $this->line("Migrating tenant #{$tenant->id} ({$tenant->slug}) …");
            $result = $runner->run($tenant, $targetVersion);
            if ($result['success']) {
                $this->info('  success');
                $ok++;
            } else {
                $this->error('  failure: '.$result['message']);
                $fail++;
            }
        }

        $this->info("Done. Success: {$ok}, failure: {$fail}. See storage/logs/tenant-updates.log");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<int>
     */
    private function failedTenantIds(): array
    {
        $latestIds = TenantUpdateLog::query()
            ->selectRaw('MAX(id) as agg_id')
            ->groupBy('tenant_id')
            ->pluck('agg_id')
            ->filter()
            ->values()
            ->all();

        if ($latestIds === []) {
            return [];
        }

        return TenantUpdateLog::query()
            ->whereIn('id', $latestIds)
            ->where('status', 'failure')
            ->pluck('tenant_id')
            ->map(static fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
