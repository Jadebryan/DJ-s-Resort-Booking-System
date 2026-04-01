<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TenantApplicationApproved;
use App\Mail\TenantApplicationRejected;
use App\Models\TenantRegistrationRequest;
use App\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $q = Str::trim((string) $request->query('q', ''));
        $like = $q !== '' ? '%'.$q.'%' : null;

        $requests = TenantRegistrationRequest::query()
            ->with('plan')
            ->whereIn('status', [
                TenantRegistrationRequest::STATUS_AWAITING_PAYMENT,
                TenantRegistrationRequest::STATUS_PENDING_REVIEW,
            ])
            ->when($like !== null, function ($query) use ($like): void {
                $query->where(function ($sub) use ($like): void {
                    $sub->where('tenant_name', 'like', $like)
                        ->orWhere('primary_domain', 'like', $like)
                        ->orWhere('admin_email', 'like', $like)
                        ->orWhere('admin_name', 'like', $like)
                        ->orWhere('payment_reference', 'like', $like)
                        ->orWhere('payment_provider', 'like', $like)
                        ->orWhereHas('plan', fn ($plan) => $plan->where('name', 'like', $like));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $historyStatus = (string) $request->query('history_status', 'all');
        if (! in_array($historyStatus, ['all', 'approved', 'rejected'], true)) {
            $historyStatus = 'all';
        }

        $historyQ = Str::trim((string) $request->query('hq', ''));
        $historyLike = $historyQ !== '' ? '%'.$historyQ.'%' : null;

        $historyQuery = TenantRegistrationRequest::query()
            ->with('plan', 'approvedTenant', 'reviewer')
            ->when($historyStatus === 'approved', fn ($query) => $query->where('status', TenantRegistrationRequest::STATUS_APPROVED))
            ->when($historyStatus === 'rejected', fn ($query) => $query->where('status', TenantRegistrationRequest::STATUS_REJECTED))
            ->when($historyStatus === 'all', fn ($query) => $query->whereIn('status', [
                TenantRegistrationRequest::STATUS_APPROVED,
                TenantRegistrationRequest::STATUS_REJECTED,
            ]))
            ->when($historyLike !== null, function ($query) use ($historyLike): void {
                $query->where(function ($sub) use ($historyLike): void {
                    $sub->where('tenant_name', 'like', $historyLike)
                        ->orWhere('primary_domain', 'like', $historyLike)
                        ->orWhere('admin_email', 'like', $historyLike)
                        ->orWhere('admin_name', 'like', $historyLike)
                        ->orWhere('payment_reference', 'like', $historyLike)
                        ->orWhere('payment_provider', 'like', $historyLike)
                        ->orWhereHas('plan', fn ($plan) => $plan->where('name', 'like', $historyLike));
                });
            })
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id');

        $history = $historyQuery->paginate(12, ['*'], 'history_page')->withQueryString();

        $stats = [
            'awaiting_payment' => TenantRegistrationRequest::query()
                ->where('status', TenantRegistrationRequest::STATUS_AWAITING_PAYMENT)
                ->count(),
            'pending_review' => TenantRegistrationRequest::query()
                ->where('status', TenantRegistrationRequest::STATUS_PENDING_REVIEW)
                ->count(),
            'approved_total' => TenantRegistrationRequest::query()
                ->where('status', TenantRegistrationRequest::STATUS_APPROVED)
                ->count(),
            'rejected_total' => TenantRegistrationRequest::query()
                ->where('status', TenantRegistrationRequest::STATUS_REJECTED)
                ->count(),
        ];

        return view('admin.tenant-registrations.index', compact('requests', 'history', 'historyStatus', 'stats'));
    }

    public function approve(TenantRegistrationRequest $registration): RedirectResponse
    {
        if ($registration->status !== TenantRegistrationRequest::STATUS_PENDING_REVIEW) {
            return redirect()->route('admin.tenant-registrations.index')
                ->with('error', __('Only applications waiting for review can be approved.'));
        }

        try {
            // Do not wrap in DB::transaction(): CREATE DATABASE (DDL) implicitly commits in MySQL and
            // leaves PDO with no active transaction, so Laravel's closing commit() throws.
            $tenant = app(TenantProvisioner::class)->provision($registration);
            $registration->update([
                'status' => TenantRegistrationRequest::STATUS_APPROVED,
                'approved_tenant_id' => $tenant->id,
                'reviewed_by' => auth('admin')->id(),
                'reviewed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.tenant-registrations.index')
                ->with('error', $e->getMessage());
        }

        $registration->refresh();
        $tenant->load('domains');
        $loginUrl = absolute_url_for_tenant_host($registration->primary_domain, '/login');

        try {
            Mail::to($registration->admin_email)->send(
                new TenantApplicationApproved($registration, $tenant, $loginUrl)
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.tenant-registrations.index')
                ->with('success', __('Resort approved and provisioned. Email could not be sent; check logs and contact the applicant manually.'));
        }

        return redirect()->route('admin.tenant-registrations.index')
            ->with('success', __('Resort approved and provisioned. The applicant was emailed sign-in details.'));
    }

    public function reject(Request $request, TenantRegistrationRequest $registration): RedirectResponse
    {
        if ($registration->status !== TenantRegistrationRequest::STATUS_PENDING_REVIEW) {
            return redirect()->route('admin.tenant-registrations.index')
                ->with('error', __('Only pending applications can be rejected.'));
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration->update([
            'status' => TenantRegistrationRequest::STATUS_REJECTED,
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        Mail::to($registration->admin_email)->send(new TenantApplicationRejected($registration->fresh()));

        return redirect()->route('admin.tenant-registrations.index')
            ->with('success', __('Application rejected and the applicant was notified.'));
    }
}
