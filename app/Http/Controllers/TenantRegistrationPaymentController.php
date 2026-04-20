<?php

namespace App\Http\Controllers;

use App\Models\TenantRegistrationRequest;
use App\Services\TenantRegistrationNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Support\InputRules;

class TenantRegistrationPaymentController extends Controller
{
    public function show(TenantRegistrationRequest $registration): View|RedirectResponse
    {
        if ($registration->status === TenantRegistrationRequest::STATUS_REJECTED) {
            abort(404);
        }

        if ($registration->status === TenantRegistrationRequest::STATUS_APPROVED) {
            return redirect()->route('tenant.select.login')
                ->with('status', __('Your resort is already approved. Sign in from your domain.'));
        }

        if ($registration->status === TenantRegistrationRequest::STATUS_PENDING_REVIEW) {
            return redirect()->route('tenant.register.submitted', ['registration' => $registration->token]);
        }

        if (! $registration->requiresPayment()) {
            return redirect()->route('tenant.register.submitted', ['registration' => $registration->token]);
        }

        $registration->loadMissing('plan');

        return view('auth.tenantAuth.register-payment', [
            'registration' => $registration,
        ]);
    }

    public function submitManual(Request $request, TenantRegistrationRequest $registration): RedirectResponse
    {
        $registration->refresh();

        if ($registration->status === TenantRegistrationRequest::STATUS_REJECTED) {
            abort(404);
        }

        if ($registration->status === TenantRegistrationRequest::STATUS_APPROVED) {
            return redirect()->route('tenant.select.login')
                ->with('status', __('This application is already approved. Sign in from your resort domain.'));
        }

        if ($registration->status === TenantRegistrationRequest::STATUS_PENDING_REVIEW) {
            return redirect()->route('tenant.register.submitted', ['registration' => $registration->token])
                ->with('status', __('Your payment details were already submitted. We are reviewing your application.'));
        }

        if ($registration->status !== TenantRegistrationRequest::STATUS_AWAITING_PAYMENT) {
            return redirect()->route('tenant.register.payment', ['registration' => $registration->token])
                ->with('error', __('We could not record payment for this step. Please refresh the payment page and try again.'));
        }

        if (! $registration->requiresPayment()) {
            return redirect()->route('tenant.register.submitted', ['registration' => $registration->token]);
        }

        $validated = $request->validate([
            'payment_provider' => ['required', 'string', 'in:gcash,maya,bank_transfer,other'],
            'payment_reference' => ['required', 'string', 'max:255', "regex:/^[A-Za-z0-9][A-Za-z0-9\\s._\\-]*$/"],
            'payment_notes' => ['nullable', 'string', 'max:2000'],
            'payment_proof' => ['required', 'file', 'image', 'max:1900'],
        ], [
            'payment_proof.required' => __('Please upload a screenshot or photo of your payment receipt.'),
            'payment_proof.image' => __('The payment proof must be an image file.'),
            'payment_proof.max' => __('Payment proof must be 1.9MB or smaller (server upload limit).'),
            'payment_proof.uploaded' => __('Payment proof failed to upload. Use a smaller image (under 1.9MB).'),
        ]);

        if ($registration->payment_proof_path && Storage::disk('public')->exists($registration->payment_proof_path)) {
            Storage::disk('public')->delete($registration->payment_proof_path);
        }

        $proofPath = $request->file('payment_proof')->store('tenant-registration-proofs', 'public');

        $labels = [
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'bank_transfer' => 'Bank transfer',
            'other' => 'Other',
        ];
        $provider = $labels[$validated['payment_provider']] ?? $validated['payment_provider'];

        $this->transitionToPendingReview(
            $registration,
            strtolower(str_replace(' ', '_', $provider)),
            $validated['payment_reference'],
            $validated['payment_notes'] ?? null,
            false,
            $proofPath
        );

        return redirect()->route('tenant.register.submitted', ['registration' => $registration->token])
            ->with('status', __('Payment details submitted. We will verify and email you when your resort is approved.'));
    }

    public function submitted(TenantRegistrationRequest $registration): View|RedirectResponse
    {
        if ($registration->status === TenantRegistrationRequest::STATUS_AWAITING_PAYMENT && $registration->requiresPayment()) {
            return redirect()->route('tenant.register.payment', ['registration' => $registration->token]);
        }

        $signInUrl = $registration->status === TenantRegistrationRequest::STATUS_APPROVED
            ? absolute_url_for_tenant_host($registration->primary_domain, '/login')
            : null;

        return view('auth.tenantAuth.register-thanks', [
            'registration' => $registration,
            'signInUrl' => $signInUrl,
        ]);
    }

    public function submittedStatus(TenantRegistrationRequest $registration): JsonResponse
    {
        $registration->refresh();

        if ($registration->status === TenantRegistrationRequest::STATUS_AWAITING_PAYMENT && $registration->requiresPayment()) {
            return response()->json([
                'status' => $registration->status,
            ]);
        }

        $payload = ['status' => $registration->status];

        if ($registration->status === TenantRegistrationRequest::STATUS_APPROVED) {
            $payload['sign_in_url'] = absolute_url_for_tenant_host($registration->primary_domain, '/login');
        }

        return response()->json($payload);
    }

    private function transitionToPendingReview(
        TenantRegistrationRequest $registration,
        string $paymentProvider,
        ?string $reference,
        ?string $notes,
        bool $isAutoPaid,
        ?string $paymentProofPath = null
    ): void {
        if ($registration->status !== TenantRegistrationRequest::STATUS_AWAITING_PAYMENT) {
            return;
        }

        $registration->update([
            'status' => TenantRegistrationRequest::STATUS_PENDING_REVIEW,
            'payment_provider' => $paymentProvider,
            'payment_reference' => $reference,
            'payment_notes' => $notes,
            'payment_proof_path' => $paymentProofPath,
            'paid_at' => $isAutoPaid ? now() : null,
            'submitted_for_review_at' => now(),
        ]);

        app(TenantRegistrationNotifier::class)->notifySubmittedForReview($registration->fresh(['plan']));
    }
}
