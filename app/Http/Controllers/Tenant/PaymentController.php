<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\ActivityLog;
use App\Models\TenantPlanUpgradeRequest;
use App\Services\SubscriptionUpgradeProration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function portal(Request $request): View|RedirectResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }
        $plan = $tenant->loadMissing('plan')->plan;
        $amount = $plan ? (float) $plan->price_monthly : 0;
        $otherPlans = Plan::query()
            ->where('is_active', true)
            ->when($plan, fn ($q) => $q->where('id', '!=', $plan->id))
            ->orderBy('sort_order')
            ->orderBy('price_monthly')
            ->get();
        $latestUpgradeRequest = TenantPlanUpgradeRequest::query()
            ->with(['requestedPlan'])
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();
        $pendingUpgradeRequest = TenantPlanUpgradeRequest::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
        $subscriptionEndsAt = $tenant->subscription_ends_at;
        $daysRemaining = $subscriptionEndsAt
            ? max(0, Carbon::now()->startOfDay()->diffInDays($subscriptionEndsAt->copy()->startOfDay(), false))
            : null;

        return view('Tenant.payment.portal', [
            'tenant' => $tenant,
            'plan' => $plan,
            'amount' => $amount,
            'otherPlans' => $otherPlans,
            'latestUpgradeRequest' => $latestUpgradeRequest,
            'pendingUpgradeRequest' => $pendingUpgradeRequest,
            'subscriptionEndsAt' => $subscriptionEndsAt,
            'daysRemaining' => $daysRemaining,
            'billingDaysPerMonth' => SubscriptionUpgradeProration::billingDaysPerMonth(),
        ]);
    }

    public function upgradeQuote(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $tenant->loadMissing('plan');

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'months' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $newPlan = Plan::query()
            ->where('id', (int) $validated['plan_id'])
            ->where('is_active', true)
            ->first();

        if (! $newPlan) {
            return response()->json(['message' => 'Selected plan is unavailable.'], 422);
        }

        $quote = SubscriptionUpgradeProration::compute(
            $tenant,
            $tenant->plan,
            $newPlan,
            (int) $validated['months'],
            Carbon::now()
        );

        return response()->json([
            'days_remaining' => $quote['days_remaining'],
            'old_monthly' => $quote['old_monthly'],
            'new_monthly' => $quote['new_monthly'],
            'credit_amount' => $quote['credit_amount'],
            'new_term_total' => $quote['new_term_total'],
            'amount_due' => $quote['amount_due'],
            'base_days' => $quote['base_days'],
            'rollover_days' => $quote['rollover_days'],
            'total_days' => $quote['total_days'],
            'new_subscription_end' => $quote['new_subscription_ends_at']->toIso8601String(),
            'billing_days_per_month' => SubscriptionUpgradeProration::billingDaysPerMonth(),
        ]);
    }

    public function submitUpgradeRequest(Request $request): RedirectResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $tenant->loadMissing('plan');

        $validated = $request->validate([
            'requested_plan_id' => ['required', 'integer', 'exists:plans,id'],
            'requested_months' => ['required', 'integer', 'min:1', 'max:12'],
            'payment_method' => ['required', 'string', 'max:80'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_notes' => ['nullable', 'string', 'max:1500'],
            'payment_proof' => ['nullable', 'image', 'max:1900'],
        ], [
            'payment_proof.uploaded' => 'Payment proof failed to upload. Please use an image smaller than 2MB.',
            'payment_proof.max' => 'Payment proof must be 1.9MB or smaller.',
        ]);

        $requestedPlan = Plan::query()
            ->where('id', (int) $validated['requested_plan_id'])
            ->where('is_active', true)
            ->first();

        if (! $requestedPlan) {
            return redirect()->route('tenant.payment.portal')->with('error', 'Selected plan is unavailable.');
        }

        $existingPending = TenantPlanUpgradeRequest::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('tenant.payment.portal')->with('error', 'You already have a pending upgrade request.');
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('upgrade-request-proofs', 'public');
        }

        $proration = SubscriptionUpgradeProration::compute(
            $tenant,
            $tenant->plan,
            $requestedPlan,
            (int) $validated['requested_months'],
            Carbon::now()
        );

        $upgradeRequest = TenantPlanUpgradeRequest::create([
            'tenant_id' => $tenant->id,
            'current_plan_id' => $tenant->plan_id,
            'requested_plan_id' => $requestedPlan->id,
            'requested_months' => (int) $validated['requested_months'],
            'payment_method' => trim((string) $validated['payment_method']),
            'payment_reference' => trim((string) ($validated['payment_reference'] ?? '')) ?: null,
            'payment_notes' => trim((string) ($validated['payment_notes'] ?? '')) ?: null,
            'payment_proof_path' => $proofPath,
            'proration_days_remaining' => $proration['days_remaining'],
            'proration_credit_amount' => $proration['credit_amount'],
            'proration_new_term_total' => $proration['new_term_total'],
            'proration_amount_due' => $proration['amount_due'],
            'proration_base_days' => $proration['base_days'],
            'proration_rollover_days' => $proration['rollover_days'],
            'proration_total_days' => $proration['total_days'],
            'status' => 'pending',
        ]);
        $isRenewal = $tenant->plan_id && (int) $tenant->plan_id === (int) $requestedPlan->id;

        // Add billing event in tenant activity logs when available.
        try {
            if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('activity_logs')) {
                ActivityLog::log(
                    $isRenewal ? 'billing.renewal.requested' : 'billing.upgrade.requested',
                    ($isRenewal ? 'Renewal' : 'Upgrade') . ' request submitted for ' . $requestedPlan->name . '.',
                    [
                        'entity_type' => 'billing_request',
                        'entity_id' => $upgradeRequest->id,
                        'metadata' => [
                            'tenant_id' => $tenant->id,
                            'current_plan_id' => $tenant->plan_id,
                            'requested_plan_id' => $requestedPlan->id,
                            'is_renewal' => $isRenewal,
                            'payment_reference' => $upgradeRequest->payment_reference,
                        ],
                    ]
                );
            }
        } catch (\Throwable) {
            // Ignore logging failures to avoid blocking upgrade requests.
        }

        return redirect()->route('tenant.payment.portal')->with(
            'success',
            $isRenewal
                ? 'Renewal request sent. Superadmin will review it soon.'
                : 'Upgrade request sent. Superadmin will review it soon.'
        );
    }
}
