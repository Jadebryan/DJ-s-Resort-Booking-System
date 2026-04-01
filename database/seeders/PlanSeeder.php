<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Essential features: limited rooms, guest info, booking management, simple dashboard.',
                'max_rooms' => 10,
                'price_monthly' => 29.00,
                'price_yearly' => null,
                'features' => ['guest_management', 'basic_booking', 'simple_dashboard'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Unlimited rooms, booking calendar, downloadable reports (PDF/CSV), room availability.',
                'max_rooms' => null,
                'price_monthly' => 59.00,
                'price_yearly' => 590.00,
                'features' => ['unlimited_rooms', 'booking_calendar', 'reports_pdf_csv', 'availability_tracking'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Everything in Standard plus revenue analytics, advanced reports, booking archive, admin activity logs.',
                'max_rooms' => null,
                'price_monthly' => 99.00,
                'price_yearly' => 990.00,
                'features' => ['revenue_analytics', 'advanced_reports', 'booking_archive', 'activity_logs'],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
