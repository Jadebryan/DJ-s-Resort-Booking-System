<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Models\TenantPlanUpgradeRequest;
use App\Services\SubscriptionUpgradeProration;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function payments(Request $request): View
    {
        $paymentStatus = (string) $request->query('payment_status', 'all');
        if (! in_array($paymentStatus, ['all', 'pending', 'approved', 'rejected'], true)) {
            $paymentStatus = 'all';
        }

        $pq = Str::trim((string) $request->query('pq', ''));
        $pqLike = $pq !== '' ? '%'.$pq.'%' : null;

        $upgradeRequests = TenantPlanUpgradeRequest::query()
            ->with(['tenant', 'currentPlan', 'requestedPlan'])
            ->when($paymentStatus !== 'all', fn ($q) => $q->where('status', $paymentStatus))
            ->when($pqLike !== null, function ($query) use ($pqLike): void {
                $query->where(function ($sub) use ($pqLike): void {
                    $sub->whereHas('tenant', function ($t) use ($pqLike): void {
                        $t->where('tenant_name', 'like', $pqLike)
                            ->orWhere('email', 'like', $pqLike);
                    })
                        ->orWhere('payment_reference', 'like', $pqLike)
                        ->orWhere('payment_method', 'like', $pqLike)
                        ->orWhere('payment_notes', 'like', $pqLike)
                        ->orWhereHas('currentPlan', fn ($p) => $p->where('name', 'like', $pqLike))
                        ->orWhereHas('requestedPlan', fn ($p) => $p->where('name', 'like', $pqLike));
                });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = TenantPlanUpgradeRequest::query()->where('status', 'pending')->count();
        $approvedCount = TenantPlanUpgradeRequest::query()->where('status', 'approved')->count();
        $rejectedCount = TenantPlanUpgradeRequest::query()->where('status', 'rejected')->count();
        $activeSubscriptions = Tenant::query()
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '>=', Carbon::now())
            ->count();

        return view('admin.payments.index', compact(
            'upgradeRequests',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'activeSubscriptions',
            'paymentStatus'
        ));
    }

    public function approveUpgradeRequest(Request $request, TenantPlanUpgradeRequest $upgradeRequest): RedirectResponse
    {
        if ($upgradeRequest->status !== 'pending') {
            return redirect()->route('admin.payments')->with('error', 'This request was already reviewed.');
        }

        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = Tenant::query()->find($upgradeRequest->tenant_id);
        if (! $tenant) {
            return redirect()->route('admin.payments')->with('error', 'Tenant not found for this request.');
        }

        $tenant->load('plan');
        $requestedPlan = Plan::query()->find($upgradeRequest->requested_plan_id);
        if (! $requestedPlan) {
            return redirect()->route('admin.payments')->with('error', 'Requested plan not found.');
        }

        $months = max(1, (int) $upgradeRequest->requested_months);
        $proration = SubscriptionUpgradeProration::compute(
            $tenant,
            $tenant->plan,
            $requestedPlan,
            $months,
            Carbon::now()
        );

        $tenant->plan_id = $requestedPlan->id;
        $tenant->subscription_months = $months;
        $tenant->subscription_ends_at = $proration['new_subscription_ends_at'];
        $tenant->save();

        $upgradeRequest->proration_days_remaining = $proration['days_remaining'];
        $upgradeRequest->proration_credit_amount = $proration['credit_amount'];
        $upgradeRequest->proration_new_term_total = $proration['new_term_total'];
        $upgradeRequest->proration_amount_due = $proration['amount_due'];
        $upgradeRequest->proration_base_days = $proration['base_days'];
        $upgradeRequest->proration_rollover_days = $proration['rollover_days'];
        $upgradeRequest->proration_total_days = $proration['total_days'];

        $upgradeRequest->status = 'approved';
        $upgradeRequest->review_notes = trim((string) ($validated['review_notes'] ?? '')) ?: null;
        $upgradeRequest->reviewed_by_admin_id = auth('admin')->id();
        $upgradeRequest->reviewed_at = now();
        $upgradeRequest->save();

        return redirect()->route('admin.payments')->with('success', 'Upgrade request approved and tenant plan updated.');
    }

    public function rejectUpgradeRequest(Request $request, TenantPlanUpgradeRequest $upgradeRequest): RedirectResponse
    {
        if ($upgradeRequest->status !== 'pending') {
            return redirect()->route('admin.payments')->with('error', 'This request was already reviewed.');
        }

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:1000'],
        ]);

        $upgradeRequest->status = 'rejected';
        $upgradeRequest->review_notes = trim((string) $validated['review_notes']);
        $upgradeRequest->reviewed_by_admin_id = auth('admin')->id();
        $upgradeRequest->reviewed_at = now();
        $upgradeRequest->save();

        return redirect()->route('admin.payments')->with('success', 'Upgrade request rejected.');
    }

    public function maintenance(): View
    {
        $tickets = MaintenanceTicket::query()->orderByDesc('updated_at')->get();
        $ticketCounts = [
            MaintenanceTicket::STATUS_OPEN => $tickets->where('status', MaintenanceTicket::STATUS_OPEN)->count(),
            MaintenanceTicket::STATUS_IN_PROGRESS => $tickets->where('status', MaintenanceTicket::STATUS_IN_PROGRESS)->count(),
            MaintenanceTicket::STATUS_RESOLVED => $tickets->where('status', MaintenanceTicket::STATUS_RESOLVED)->count(),
        ];

        return view('admin.maintenance.index', compact('tickets', 'ticketCounts'));
    }

    public function storeMaintenanceTicket(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::in(array_keys(MaintenanceTicket::priorityLabels()))],
            'status' => ['required', Rule::in([
                MaintenanceTicket::STATUS_OPEN,
                MaintenanceTicket::STATUS_IN_PROGRESS,
                MaintenanceTicket::STATUS_RESOLVED,
            ])],
            'related_tenant' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        MaintenanceTicket::query()->create($validated);

        return redirect()
            ->route('admin.maintenance')
            ->with('success', __('Maintenance ticket created.'));
    }

    public function updateMaintenanceTicket(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                MaintenanceTicket::STATUS_OPEN,
                MaintenanceTicket::STATUS_IN_PROGRESS,
                MaintenanceTicket::STATUS_RESOLVED,
            ])],
        ]);

        $ticket->update($validated);

        return redirect()
            ->route('admin.maintenance')
            ->with('success', __('Ticket status updated.'));
    }

    public function reports(Request $request): View
    {
        $tenantCount = Tenant::count();
        $activeTenantCount = Tenant::where('is_active', true)->count();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $tenantsByPlan = Tenant::selectRaw('plan_id, count(*) as count')
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');
        $tenantsWithoutPlan = Tenant::whereNull('plan_id')->count();

        return view('admin.reports.index', [
            'tenantCount' => $tenantCount,
            'activeTenantCount' => $activeTenantCount,
            'plans' => $plans,
            'tenantsByPlan' => $tenantsByPlan,
            'tenantsWithoutPlan' => $tenantsWithoutPlan,
        ]);
    }

    public function settings(): View
    {
        $platformSettings = PlatformSetting::instance()->loadMissing('defaultPlan');
        $plans = Plan::query()->orderByDesc('is_active')->orderBy('sort_order')->orderBy('name')->get();
        $timezoneOptions = collect(DateTimeZone::listIdentifiers())
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('admin.settings.index', compact('platformSettings', 'plans', 'timezoneOptions'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'timezone' => ['required', Rule::in(DateTimeZone::listIdentifiers())],
        ]);

        $platformSettings = PlatformSetting::instance();
        $platformSettings->default_plan_id = $validated['default_plan_id'] ?? null;
        $platformSettings->timezone = $validated['timezone'];
        $platformSettings->send_system_emails = $request->boolean('send_system_emails');
        $platformSettings->send_sms_alerts = $request->boolean('send_sms_alerts');
        $platformSettings->feature_booking_calendar_beta = $request->boolean('feature_booking_calendar_beta');
        $platformSettings->feature_multi_currency = $request->boolean('feature_multi_currency');
        $platformSettings->save();

        return redirect()
            ->route('admin.settings')
            ->with('success', __('Platform settings saved.'));
    }

    public function subscriptions(): View
    {
        $featureCatalog = $this->subscriptionFeatureCatalog();
        $plans = Plan::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $planStats = [
            'total' => Plan::count(),
            'active' => Plan::where('is_active', true)->count(),
            'inactive' => Plan::where('is_active', false)->count(),
        ];

        return view('admin.subscriptions.index', compact('plans', 'featureCatalog', 'planStats'));
    }

    public function updateSubscriptions(Request $request): RedirectResponse
    {
        $featureKeys = array_keys($this->subscriptionFeatureCatalog());
        $validated = $request->validate([
            'plans' => ['required', 'array', 'min:1'],
            'plans.*.id' => ['required', 'integer', 'exists:plans,id'],
            'plans.*.name' => ['required', 'string', 'max:255'],
            'plans.*.description' => ['nullable', 'string', 'max:5000'],
            'plans.*.price_monthly' => ['required', 'numeric', 'min:0'],
            'plans.*.price_yearly' => ['nullable', 'numeric', 'min:0'],
            'plans.*.max_rooms' => ['nullable', 'integer', 'min:1'],
            'plans.*.sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'plans.*.is_active' => ['nullable', 'boolean'],
            'plans.*.features' => ['nullable', 'array'],
            'plans.*.features.*' => ['string', 'in:'.implode(',', $featureKeys)],
        ]);

        foreach ($validated['plans'] as $planInput) {
            $plan = Plan::findOrFail((int) $planInput['id']);
            $features = collect($planInput['features'] ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter(fn ($value) => $value !== '')
                ->unique()
                ->values()
                ->all();

            $plan->update([
                'name' => $planInput['name'],
                'description' => $planInput['description'] ?? null,
                'price_monthly' => $planInput['price_monthly'],
                'price_yearly' => $planInput['price_yearly'] === null || $planInput['price_yearly'] === ''
                    ? null
                    : $planInput['price_yearly'],
                'max_rooms' => $planInput['max_rooms'] === null || $planInput['max_rooms'] === ''
                    ? null
                    : (int) $planInput['max_rooms'],
                'sort_order' => (int) $planInput['sort_order'],
                'is_active' => (bool) ($planInput['is_active'] ?? false),
                'features' => $features,
            ]);
        }

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', __('Subscription plans updated successfully.'));
    }

    private function subscriptionFeatureCatalog(): array
    {
        return [
            'guest_management' => 'Guest management',
            'basic_booking' => 'Basic booking',
            'simple_dashboard' => 'Simple dashboard',
            'unlimited_rooms' => 'Unlimited rooms',
            'booking_calendar' => 'Booking calendar',
            'reports_pdf_csv' => 'Reports export (PDF/CSV)',
            'availability_tracking' => 'Room availability tracking',
            'revenue_analytics' => 'Revenue analytics',
            'advanced_reports' => 'Advanced reports',
            'booking_archive' => 'Booking archive',
            'activity_logs' => 'Activity logs',
        ];
    }
}
