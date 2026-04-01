<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/debug/tenants', function () {
    $tenants = DB::table('tenants')->limit(5)->get();
    return [
        'count' => DB::table('tenants')->count(),
        'tenants' => $tenants
    ];
});

Route::get('/debug/tenant/{slug}', function ($slug) {
    $tenant = DB::table('tenants')->where('slug', $slug)->first();
    if (!$tenant) {
        return ['error' => 'Tenant not found'];
    }

    // Switch to tenant database
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');

    $users = DB::connection('tenant')->table('regular_users')->limit(5)->get();
    return [
        'tenant' => $tenant,
        'tenant_db' => $tenant->database_name,
        'regular_users_count' => DB::connection('tenant')->table('regular_users')->count(),
        'users' => $users,
    ];
});
