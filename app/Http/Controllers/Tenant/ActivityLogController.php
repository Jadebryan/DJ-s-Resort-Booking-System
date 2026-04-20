<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\TenantPlanFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    protected function tenantHasActivityLogs(Request $request): bool
    {
        return TenantPlanFeatures::hasRequestFeature($request, 'activity_logs');
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$this->tenantHasActivityLogs($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Activity log is not enabled in your current subscription.');
        }

        $logs = ActivityLog::with(['user', 'regularUser'])->orderByDesc('created_at')->paginate(50);

        return view('Tenant.activity.index', compact('logs'));
    }
}
