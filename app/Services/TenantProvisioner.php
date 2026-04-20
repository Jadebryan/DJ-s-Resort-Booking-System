<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantRegistrationRequest;
use App\Models\TenantModel\Tenant as TenantUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TenantProvisioner
{
    /**
     * Create landlord tenant row, primary domain, tenant database, migrations, and first admin user.
     * Uses the pre-hashed admin password from the registration request (same hash for landlord + tenant DB).
     *
     * @throws RuntimeException
     */
    public function provision(TenantRegistrationRequest $request): Tenant
    {
        $request->loadMissing('plan');

        $domain = tenant_primary_domain_storage((string) $request->primary_domain);
        if (TenantDomain::where('domain', $domain)->exists()) {
            throw new RuntimeException('This hostname is already assigned to another resort.');
        }

        $internalSlug = Str::slug(explode('.', $domain)[0] ?: 'resort').'-'.Str::lower(Str::random(5));
        while (Tenant::where('slug', $internalSlug)->exists()) {
            $internalSlug = Str::slug(explode('.', $domain)[0] ?: 'resort').'-'.Str::lower(Str::random(5));
        }

        $tenantId = Str::lower(Str::random(12));
        $tenantDatabaseName = config('tenancy.database.prefix', 'tenant_').$tenantId.config('tenancy.database.suffix', '');

        $passwordHash = $request->admin_password;

        $days = $request->subscriptionLengthDays();
        $monthsForRecord = max(1, (int) ceil($days / TenantRegistrationRequest::BILLING_DAYS_PER_MONTH));
        $planId = $request->plan_id ?? PlatformSetting::instance()->default_plan_id;
        $tenant = Tenant::create([
            'tenant_name' => $request->tenant_name,
            'app_display_name' => $request->tenant_name,
            'slug' => $internalSlug,
            'database_name' => $tenantDatabaseName,
            'plan_id' => $planId,
            'subscription_ends_at' => now()->addDays($days),
            'subscription_months' => $monthsForRecord,
            'email' => $request->admin_email,
            'password' => $passwordHash,
        ]);

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'is_primary' => true,
        ]);

        try {
            DB::connection('mysql')->statement('CREATE DATABASE IF NOT EXISTS `'.$tenantDatabaseName.'`');
            config(['database.connections.tenant.database' => $tenantDatabaseName]);
            DB::purge('tenant');
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            $tenant->domains()->delete();
            $tenant->delete();
            throw new RuntimeException('Failed to create tenant database: '.$e->getMessage(), 0, $e);
        }

        try {
            $adminUser = DB::connection('tenant')->table('tenant_users')->insertGetId([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => $passwordHash,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userRecord = DB::connection('tenant')
                ->table('tenant_users')
                ->where('id', $adminUser)
                ->first();

            $user = new TenantUser();
            $user->setConnection('tenant');
            $user->forceFill((array) $userRecord);
            event(new Registered($user));
        } catch (\Throwable $e) {
            try {
                DB::connection('mysql')->statement('DROP DATABASE IF EXISTS `'.$tenantDatabaseName.'`');
            } catch (\Throwable) {
            }
            $tenant->domains()->delete();
            $tenant->delete();
            throw new RuntimeException('Failed to create admin user: '.$e->getMessage(), 0, $e);
        }

        return $tenant->fresh(['domains', 'plan']);
    }
}
