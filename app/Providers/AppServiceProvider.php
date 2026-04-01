<?php

namespace App\Providers;

use App\Models\Room;
use App\Models\TenantRbacRole;
use App\Models\TenantModel\Tenant as TenantUser;
use App\Models\TenantUserModel\RegularUser;
use DateTimeZone;
use Illuminate\Contracts\Foundation\ExceptionRenderer;
use App\Exceptions\SimpleExceptionRenderer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use a simple exception renderer so the debug page doesn't depend on
        // laravel-exceptions-renderer::topbar (missing in the framework package).
        $this->app->bind(ExceptionRenderer::class, SimpleExceptionRenderer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register view namespaces for role-specific components and layouts
        view()->addNamespace('admin', resource_path('views/admin'));
        view()->addNamespace('tenant', resource_path('views/Tenant'));
        view()->addNamespace('tenant-user', resource_path('views/TenantUser'));
        // Register anonymous component namespaces so tags like
        // <x-admin::guest-layout> map to resources/views/admin/components/guest-layout.blade.php
        Blade::anonymousComponentNamespace(resource_path('views/admin/components'), 'admin');
        Blade::anonymousComponentNamespace(resource_path('views/Tenant/components'), 'tenant');
        Blade::anonymousComponentNamespace(resource_path('views/TenantUser/components'), 'tenant-user');

        // Resolve {member} in tenant staff routes to TenantUser (tenant DB)
        Route::bind('member', function (string $value) {
            return TenantUser::findOrFail($value);
        });

        // Resolve {room} in tenant book routes to Room (tenant DB)
        Route::bind('room', function (string $value) {
            return Room::findOrFail($value);
        });

        Route::bind('rbacRole', function (string $value) {
            return TenantRbacRole::findOrFail($value);
        });

        Route::bind('guest', function (string $value) {
            return RegularUser::findOrFail($value);
        });

        $this->applyPlatformTimezone();
    }

    private function applyPlatformTimezone(): void
    {
        try {
            if (! Schema::hasTable('platform_settings')) {
                return;
            }
            $tz = \App\Models\PlatformSetting::query()->value('timezone');
            if (! is_string($tz) || $tz === '') {
                return;
            }
            if (! in_array($tz, DateTimeZone::listIdentifiers(), true)) {
                return;
            }
            config(['app.timezone' => $tz]);
            date_default_timezone_set($tz);
        } catch (\Throwable) {
            // Migrations not run or DB unavailable during early boot
        }
    }


}
