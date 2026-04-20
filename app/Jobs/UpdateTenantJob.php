<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\PlatformReleaseVersionService;
use App\Services\TenantMigrationRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTenantJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $uniqueFor = 3600;

    public function __construct(public int $landlordTenantId) {}

    public function uniqueId(): string
    {
        return 'landlord-tenant-update-'.$this->landlordTenantId;
    }

    public function handle(TenantMigrationRunner $runner, PlatformReleaseVersionService $releases): void
    {
        $tenant = Tenant::query()->find($this->landlordTenantId);
        if (! $tenant) {
            return;
        }

        $target = $releases->latestSchemaVersion();
        $result = $runner->run($tenant, $target);

        if ($result['success']) {
            $tenant->update(['version' => $target]);
        }
    }
}
