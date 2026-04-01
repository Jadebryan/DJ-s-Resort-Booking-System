<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DebugController extends Controller
{
    public function checkTenants()
    {
        $tenantCount = DB::table('tenants')->count();
        $tenants = DB::table('tenants')->limit(5)->get();

        return response()->json([
            'total_tenants' => $tenantCount,
            'sample_tenants' => $tenants,
        ]);
    }

    public function checkTenant($slug)
    {
        $tenant = DB::table('tenants')->where('slug', $slug)->first();

        if (!$tenant) {
            return response()->json(['error' => "Tenant '$slug' not found in root database"], 404);
        }

        // Switch to tenant database
        config(['database.connections.tenant.database' => $tenant->database_name]);
        DB::purge('tenant');

        // Check if regex_users table exists
        $tableExists = DB::connection('tenant')->getSchemaBuilder()->hasTable('regular_users');

        $response = [
            'tenant' => $tenant,
            'tenant_database' => $tenant->database_name,
            'regular_users_table_exists' => $tableExists,
        ];

        if ($tableExists) {
            $userCount = DB::connection('tenant')->table('regular_users')->count();
            $searchUser = DB::connection('tenant')->table('regular_users')->where('email', 'tenant' . substr($slug, -1) . '@example.com')->first();

            $response['regular_users_count'] = $userCount;
            $response['searched_user'] = $searchUser;

            if ($searchUser) {
                $response['password_hash'] = substr($searchUser->password, 0, 20) . '... [truncated]';
                $response['password_check'] = Hash::check('password', $searchUser->password);
            }
        }

        return response()->json($response);
    }

    public function checkAllTenantUsers()
    {
        $tenants = DB::table('tenants')->get();
        $results = [];

        foreach ($tenants as $tenant) {
            config(['database.connections.tenant.database' => $tenant->database_name]);
            DB::purge('tenant');

            $tableExists = DB::connection('tenant')->getSchemaBuilder()->hasTable('regular_users');
            $userCount = 0;
            $users = [];

            if ($tableExists) {
                $userCount = DB::connection('tenant')->table('regular_users')->count();
                $users = DB::connection('tenant')->table('regular_users')->select('id', 'email', 'name')->get();
            }

            $results[] = [
                'slug' => $tenant->slug,
                'database' => $tenant->database_name,
                'regular_users_exists' => $tableExists,
                'user_count' => $userCount,
                'users' => $users,
            ];
        }

        return response()->json($results);
    }
}
