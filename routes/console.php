<?php

use App\Models\ActivityLog;
use App\Models\Tenant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run tenant migrations for every existing tenant database (do NOT use migrate --database=tenant).
Artisan::command('tenants:migrate {--force : Run without confirmation in production}', function () {
    if (! $this->option('force') && $this->laravel->environment('production')) {
        $this->error('Use --force to run migrations in production.');

        return 1;
    }
    $tenants = Tenant::all();
    if ($tenants->isEmpty()) {
        $this->warn('No tenants found.');
        return;
    }
    foreach ($tenants as $tenant) {
        $db = $tenant->database_name;
        $this->info("Migrating tenant: {$tenant->slug} ({$db})");
        config(['database.connections.tenant.database' => $db]);
        DB::purge('tenant');
        try {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->info("  OK");
        } catch (\Throwable $e) {
            $this->error("  Failed: " . $e->getMessage());
        }
    }
    $this->info('Done.');
})->purpose('Run tenant migrations for each existing tenant database');

Artisan::command('audit:verify-chain {slug : Tenant slug}', function (string $slug) {
    $tenant = Tenant::query()->where('slug', $slug)->first();
    if (! $tenant) {
        $this->error("Tenant not found: {$slug}");

        return 1;
    }
    config(['database.connections.tenant.database' => $tenant->database_name]);
    config(['database.connections.tenant.host' => env('DB_HOST', '127.0.0.1')]);
    config(['database.connections.tenant.port' => env('DB_PORT', '3306')]);
    config(['database.connections.tenant.username' => env('DB_USERNAME', 'root')]);
    config(['database.connections.tenant.password' => env('DB_PASSWORD', '')]);
    DB::purge('tenant');
    DB::reconnect('tenant');
    if (! \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('activity_logs')) {
        $this->warn('No activity_logs table for this tenant.');

        return 0;
    }
    $result = ActivityLog::verifyChain();
    foreach ($result['warnings'] as $w) {
        $this->warn($w);
    }
    foreach ($result['errors'] as $e) {
        $this->error($e);
    }
    if ($result['valid']) {
        $this->info('Chain OK for tenant: ' . $slug);
    } else {
        $this->error('Chain verification failed.');

        return 1;
    }

    return 0;
})->purpose('Verify HMAC hash chain on tenant activity_logs (tamper check)');
