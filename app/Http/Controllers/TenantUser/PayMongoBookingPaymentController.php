<?php

declare(strict_types=1);

namespace App\Http\Controllers\TenantUser;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Tenant;
use App\Services\PaymentService;
use App\Services\PayMongo\BookingPayMongoSync;
use App\Services\PayMongo\PayMongoClient;
use App\Rules\FullPaymentAmountCoversStay;
use App\Support\InputRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PayMongoBookingPaymentController extends Controller
{
    public function __construct(
        private readonly PayMongoClient $payMongo,
        private readonly BookingPayMongoSync $sync
    ) {}

    /**
     * Start GCash redirect flow (PayMongo) for an existing booking.
     */
    public function start(Request $request): RedirectResponse
    {
        if (! $this->payMongo->isConfigured()) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Online GCash checkout is not enabled. Ask the resort to configure PayMongo, or pay manually with proof.'));
        }

        $bookingId = (string) $request->route('booking');
        $booking = Booking::with('room')->find($bookingId);
        if (! $booking) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Booking not found.'));
        }

        if ($booking->regular_user_id !== auth('regular_user')->id()) {
            abort(403);
        }
        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Cannot pay for a cancelled booking.'));
        }
        if ($booking->is_fully_paid) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('info', __('This booking is already marked as paid.'));
        }

        $payable = (float) $booking->amount_payable;

        $validated = $request->validate([
            'payment_type' => ['required', 'string', 'in:full,partial'],
            'amount_paid' => [
                Rule::requiredIf(static fn () => $request->input('payment_type') === 'partial'),
                'nullable',
                'numeric',
                'min:0',
                new FullPaymentAmountCoversStay(null, $payable, true),
            ],
            'payer_full_name' => InputRules::personName(255, true),
        ]);

        $paymentType = (string) $validated['payment_type'];

        $amountPhp = $paymentType === 'full'
            ? $payable
            : (float) ($validated['amount_paid'] ?? 0);

        if ($paymentType === 'partial' && $amountPhp <= 0) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->withErrors(['amount_paid' => __('Enter a valid partial amount.')])
                ->withInput()
                ->with('openPaymentModal', (int) $booking->id);
        }
        if ($amountPhp > $payable + 0.01) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->withErrors(['amount_paid' => __('Amount cannot exceed the total for this booking.')])
                ->withInput()
                ->with('openPaymentModal', (int) $booking->id);
        }

        $minPhp = PaymentService::paymongoMinimumPhp();
        if ($amountPhp + 0.0001 < $minPhp) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('PayMongo requires a minimum payment of ₱:min.', ['min' => number_format($minPhp, 0)]))
                ->with('openPaymentModal', (int) $booking->id);
        }

        $centavos = (int) round($amountPhp * 100);
        if ($centavos < 2000) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('PayMongo requires a minimum payment of ₱20.'))
                ->with('openPaymentModal', (int) $booking->id);
        }

        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Could not resolve resort context.'));
        }

        $user = auth('regular_user')->user();
        $email = (string) ($user->email ?? '');
        $name = trim((string) $validated['payer_full_name']);
        $phone = $this->billingPhone($booking);

        try {
            $pi = $this->payMongo->createPaymentIntent($centavos, [
                'central_tenant_id' => (string) $tenant->id,
                'booking_id' => (string) $booking->id,
            ]);
            $intentId = (string) ($pi['data']['id'] ?? '');
            if ($intentId === '') {
                throw new RuntimeException('Missing payment intent id.');
            }

            $pm = $this->payMongo->createGcashPaymentMethod($name, $email, $phone);
            $pmId = (string) ($pm['data']['id'] ?? '');
            if ($pmId === '') {
                throw new RuntimeException('Missing payment method id.');
            }

            $sig = $this->returnSignature((int) $booking->id, $intentId);
            $returnUrl = tenant_url('user/bookings/gcash-return')
                . '?booking=' . $booking->id
                . '&pi=' . rawurlencode($intentId)
                . '&s=' . rawurlencode($sig);

            $attach = $this->payMongo->attachPaymentIntent($intentId, $pmId, $returnUrl);
            $redirectUrl = data_get($attach, 'data.attributes.next_action.redirect.url');

            if (! is_string($redirectUrl) || $redirectUrl === '') {
                throw new RuntimeException('PayMongo did not return a GCash redirect URL.');
            }
        } catch (RuntimeException $e) {
            report($e);

            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Could not start GCash: :m', ['m' => $e->getMessage()]))
                ->with('openPaymentModal', (int) $booking->id);
        }

        $booking->forceFill([
            'paymongo_payment_intent_id' => $intentId,
            'payer_full_name' => $name,
            'payment_type' => $paymentType,
        ])->save();

        return redirect()->away($redirectUrl);
    }

    public function returnPage(Request $request): View|RedirectResponse
    {
        $bookingId = (int) $request->query('booking', 0);
        $intentId = (string) $request->query('pi', '');
        $sig = (string) $request->query('s', '');

        if ($bookingId < 1 || $intentId === '' || $sig === '') {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Invalid payment return link.'));
        }

        if (! hash_equals($this->returnSignature($bookingId, $intentId), $sig)) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('Invalid payment return signature.'));
        }

        $booking = Booking::with('room')->find($bookingId);
        if (! $booking || $booking->regular_user_id !== auth('regular_user')->id()) {
            abort(403);
        }

        if (! $this->payMongo->isConfigured()) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', __('PayMongo is not configured.'));
        }

        try {
            $pi = $this->payMongo->retrievePaymentIntent($intentId);
        } catch (RuntimeException $e) {
            report($e);

            return view('TenantUser.bookings.gcash-return', [
                'booking' => $booking,
                'success' => false,
                'message' => __('Could not verify payment status. If you completed GCash, wait a moment and check your bookings, or contact the resort.'),
            ]);
        }

        $status = (string) data_get($pi, 'data.attributes.status', '');
        if ($status === 'succeeded') {
            $this->sync->applySucceededIntent($booking->fresh(), $pi);
            $booking->refresh();

            if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
                try {
                    ActivityLog::log(
                        'booking.gcash_paid',
                        'Guest completed PayMongo GCash payment for booking #' . $booking->id . '.',
                        [
                            'entity_type' => 'booking',
                            'entity_id' => $booking->id,
                            'actor_type' => 'guest',
                            'metadata' => [
                                'after' => $booking->auditSnapshot(),
                            ],
                        ]
                    );
                } catch (\Throwable) {
                }
            }

            return view('TenantUser.bookings.gcash-return', [
                'booking' => $booking,
                'success' => true,
                'message' => __('Payment received. Your booking stays pending until the resort confirms it.'),
            ]);
        }

        if ($status === 'processing') {
            return view('TenantUser.bookings.gcash-return', [
                'booking' => $booking,
                'success' => false,
                'message' => __('Payment is still processing. Refresh this page in a few seconds or check My Bookings later.'),
            ]);
        }

        return view('TenantUser.bookings.gcash-return', [
            'booking' => $booking,
            'success' => false,
            'message' => __('Payment was not completed. You can try again from My Bookings.'),
        ]);
    }

    private function returnSignature(int $bookingId, string $intentId): string
    {
        return hash_hmac('sha256', $bookingId . '|' . $intentId, (string) config('app.key'));
    }

    private function billingPhone(Booking $booking): string
    {
        $raw = trim((string) ($booking->guest_phone ?? ''));
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (strlen($digits) >= 10) {
            if (str_starts_with($digits, '63')) {
                return '+' . $digits;
            }
            if (str_starts_with($digits, '0')) {
                return '+63' . substr($digits, 1);
            }

            return '+63' . $digits;
        }

        return '+639000000000';
    }
}
