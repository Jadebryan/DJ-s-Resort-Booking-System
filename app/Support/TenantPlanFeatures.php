<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantPlanFeatures
{
    /**
     * Feature bundles by plan slug. Higher plans include lower plan features.
     */
    private const PLAN_FEATURES = [
        'basic' => [
            'guest_management',
            'basic_booking',
            'simple_dashboard',
        ],
        'standard' => [
            'unlimited_rooms',
            'booking_calendar',
            'reports_pdf_csv',
            'availability_tracking',
        ],
        'premium' => [
            'revenue_analytics',
            'advanced_reports',
            'booking_archive',
            'activity_logs',
        ],
    ];

    public static function forPlan(?Plan $plan): array
    {
        if (! $plan) {
            return [];
        }

        $features = [];

        $slug = strtolower((string) ($plan->slug ?? ''));
        if ($slug === 'standard' || $slug === 'premium') {
            $features = array_merge($features, self::PLAN_FEATURES['basic']);
        }
        if ($slug === 'premium') {
            $features = array_merge($features, self::PLAN_FEATURES['standard']);
        }
        if (isset(self::PLAN_FEATURES[$slug])) {
            $features = array_merge($features, self::PLAN_FEATURES[$slug]);
        }

        // Keep support for manually customized feature arrays from admin UI.
        $custom = is_array($plan->features) ? $plan->features : [];
        $features = array_merge($features, $custom);

        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => trim((string) $value),
            $features
        ))));
    }

    public static function hasPlanFeature(?Plan $plan, string $feature): bool
    {
        return in_array($feature, self::forPlan($plan), true);
    }

    public static function hasTenantFeature(?Tenant $tenant, string $feature): bool
    {
        if (! $tenant) {
            return false;
        }

        $plan = $tenant->loadMissing('plan')->plan;

        return self::hasPlanFeature($plan, $feature);
    }

    public static function hasRequestFeature(Request $request, string $feature): bool
    {
        $tenant = $request->attributes->get('tenant');

        return $tenant instanceof Tenant
            ? self::hasTenantFeature($tenant, $feature)
            : false;
    }
}

