<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\TenantDomain;
use App\Models\TenantRegistrationRequest;
use App\Services\TenantRegistrationNotifier;
use App\Support\InputRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TenantRegisterController extends Controller
{
    public function create(): View
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('auth.tenantAuth.register', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $domain = tenant_primary_domain_storage($request->string('primary_domain')->toString());
        $request->merge(['primary_domain' => $domain]);

        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'subscription_term_type' => ['required', Rule::in(['months', 'days'])],
            'subscription_months' => ['required_if:subscription_term_type,months', 'nullable', 'integer', 'min:1', 'max:12'],
            'subscription_days' => [
                'required_if:subscription_term_type,days',
                'nullable',
                'integer',
                'min:1',
                'max:'.TenantRegistrationRequest::MAX_SUBSCRIPTION_DAYS,
            ],
            'tenant_name' => InputRules::title(255, true),
            'primary_domain' => [
                'required',
                'string',
                'max:255',
                'regex:'.TenantDomain::STORED_DOMAIN_REGEX,
                Rule::unique('tenant_domains', 'domain'),
            ],
            'name' => InputRules::personName(255, true),
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:254'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plan = Plan::where('id', $request->plan_id)->where('is_active', true)->first();
        if (! $plan) {
            return back()->withInput()->withErrors(['plan_id' => __('Please choose a valid subscription.')]);
        }

        if (TenantRegistrationRequest::query()
            ->where('primary_domain', $domain)
            ->whereIn('status', [
                TenantRegistrationRequest::STATUS_AWAITING_PAYMENT,
                TenantRegistrationRequest::STATUS_PENDING_REVIEW,
            ])
            ->exists()) {
            return back()->withInput()->withErrors(['primary_domain' => __('This hostname already has a pending application.')]);
        }

        $centralHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: ''));
        if ($centralHost !== '' && tenant_browser_hostname($domain) === $centralHost) {
            return back()->withErrors(['primary_domain' => __('Use your resort’s own domain, not the central app host.')])->withInput();
        }

        $termType = (string) $request->input('subscription_term_type');
        $subscriptionMonths = null;
        $subscriptionDays = null;
        if ($termType === 'days') {
            $subscriptionDays = max(1, min(TenantRegistrationRequest::MAX_SUBSCRIPTION_DAYS, (int) $request->input('subscription_days')));
            $subscriptionMonths = max(1, (int) ceil($subscriptionDays / TenantRegistrationRequest::BILLING_DAYS_PER_MONTH));
        } else {
            $subscriptionMonths = max(1, min(12, (int) $request->input('subscription_months', 1)));
        }

        $registration = TenantRegistrationRequest::create([
            'token' => (string) Str::uuid(),
            'plan_id' => $plan->id,
            'subscription_months' => $subscriptionMonths,
            'subscription_days' => $subscriptionDays,
            'tenant_name' => $request->tenant_name,
            'primary_domain' => $domain,
            'admin_name' => $request->name,
            'admin_email' => $request->email,
            'admin_password' => Hash::make($request->password),
            'status' => TenantRegistrationRequest::STATUS_AWAITING_PAYMENT,
        ]);

        $amount = $registration->amountDue();
        if ($amount <= 0) {
            $registration->update([
                'status' => TenantRegistrationRequest::STATUS_PENDING_REVIEW,
                'payment_provider' => 'free_plan',
                'submitted_for_review_at' => now(),
            ]);
            app(TenantRegistrationNotifier::class)->notifySubmittedForReview($registration->fresh(['plan']));

            return redirect()->route('tenant.register.submitted', ['registration' => $registration->token])
                ->with('status', __('Application received. We will email you when it is approved.'));
        }

        return redirect()->route('tenant.register.payment', ['registration' => $registration->token]);
    }
}
