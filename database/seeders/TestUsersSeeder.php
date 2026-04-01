<?php

namespace Database\Seeders;

use App\Models\AdminModel\Admin;
use App\Models\TenantModel\Tenant;
use App\Models\TenantUserModel\RegularUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test admin
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@resort.local',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Create a test tenant
        Tenant::create([
            'name' => 'Tenant User',
            'email' => 'tenant@resort.local',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Create a test regular user
        RegularUser::create([
            'name' => 'Regular User',
            'email' => 'user@resort.local',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);
    }
}
