<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\PlatformReleaseVersionService;
use App\Services\TenantDatabaseUsage;
use App\Support\InputRules;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function index(Request $request, PlatformReleaseVersionService $releases): View
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $tenant->loadMissing('plan', 'domains');
        $dbInfo = TenantDatabaseUsage::summarizeTenantConnection();

        $primaryDomain = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->first();

        $tenantSchemaVersion = Tenant::query()->whereKey($tenant->id)->value('version') ?? '1.0.0';
        $systemLatestVersion = $releases->latestSchemaVersion();
        $latestReleaseDetails = $releases->latestReleaseDetails();

        return view('Tenant.settings.index', [
            'tenant' => $tenant,
            'primaryDomain' => $primaryDomain,
            'dbInfo' => $dbInfo,
            'appTimezone' => config('app.timezone'),
            'appLocale' => config('app.locale'),
            'appUrl' => config('app.url'),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'nowDisplay' => Carbon::now(config('app.timezone'))->format('Y-m-d H:i'),
            'systemLatestVersion' => $systemLatestVersion,
            'tenantSchemaVersion' => $tenantSchemaVersion,
            'latestReleaseDetails' => $latestReleaseDetails,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        if (auth('tenant')->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'app_display_name' => InputRules::title(120, false),
        ]);

        $trimmed = trim((string) ($validated['app_display_name'] ?? ''));
        $tenant->app_display_name = $trimmed === '' ? null : $trimmed;
        $tenant->save();

        return redirect()->route('tenant.settings.index')
            ->with('success', __('App name updated.'));
    }
}
