<?php

namespace Database\Seeders;

use App\Models\AdminModel\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed a default superadmin for development.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'djsresortbookingsystem@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
            ]
        );
    }
}
