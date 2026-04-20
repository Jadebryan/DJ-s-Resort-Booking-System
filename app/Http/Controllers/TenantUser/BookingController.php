<?php

namespace App\Http\Controllers\TenantUser;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Room;
use App\Services\PaymentService;
use App\Rules\FullPaymentAmountCoversStay;
use App\Support\GuestBookingCalendar;
use App\Support\InputRules;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with('room')
            ->where('regular_user_id', auth('regular_user')->id())
            ->orderBy('check_in', 'desc')
            ->get();

        $rooms = Room::where('is_available', true)->orderBy('name')->get();

        $paymongoEnabled = app(PaymentService::class)->paymongoEnabled();

        $calendarPayload = GuestBookingCalendar::monthPayloadForUser($request, (int) auth('regular_user')->id());

        return view('TenantUser.bookings.index', compact('bookings', 'rooms', 'paymongoEnabled', 'calendarPayload'));
    }

    /**
     * Guest updates their booking details (dates, guest info, notes). Only when not cancelled.
     */
    public function update(Request $request): RedirectResponse
    {
        $bookingId = (string) $request->route('booking');
        try {
            $booking = Booking::findOrFail($bookingId);
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', 'Booking not found.');
        }

        if ($booking->regular_user_id !== auth('regular_user')->id()) {
            abort(403);
        }
        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', 'Cannot edit a cancelled booking.');
        }

        try {
            $request->validate([
                'check_in' => ['required', 'date', 'after_or_equal:today'],
                'check_out' => ['required', 'date', 'after:check_in'],
                'guest_name' => InputRules::personName(255, false),
                'guest_email' => ['nullable', 'email:rfc,dns', 'max:254'],
                'guest_phone' => InputRules::phone(25, false),
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('openDetailsModal', $booking->id);
        }

        $before = $booking->auditSnapshot();

        $booking->update([
            'check_in' => $request->input('check_in'),
            'check_out' => $request->input('check_out'),
            'guest_name' => $request->input('guest_name'),
            'guest_email' => $request->input('guest_email'),
            'guest_phone' => $request->input('guest_phone'),
            'notes' => $request->input('notes'),
        ]);
        $booking->refresh();

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            try {
                ActivityLog::log(
                    'booking.updated',
                    'Guest updated booking #' . $booking->id . ' details.',
                    [
                        'entity_type' => 'booking',
                        'entity_id' => $booking->id,
                        'actor_type' => 'guest',
                        'metadata' => [
                            'before' => $before,
                            'after' => $booking->auditSnapshot(),
                        ],
                    ]
                );
            } catch (\Throwable) {
            }
        }

        return redirect()
            ->route('tenant.user.bookings.index')
            ->with('success', 'Booking details updated.');
    }

    /**
     * Guest uploads payment proof. Booking stays pending until tenant admin verifies and confirms.
     */
    public function uploadProof(Request $request): RedirectResponse
    {
        $bookingId = (string) $request->route('booking');
        try {
            $booking = Booking::findOrFail($bookingId);
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', 'Booking not found.');
        }

        if ($booking->regular_user_id !== auth('regular_user')->id()) {
            abort(403);
        }
        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', 'Cannot upload proof for a cancelled booking.');
        }

        $booking->load('room');
        $payableForProof = (float) $booking->amount_payable;

        try {
            $request->validate([
                'payer_full_name' => InputRules::personName(255, true),
                'payer_gcash_no' => InputRules::paymentMethod(80, true),
                'payer_ref_no' => InputRules::reference(80, true),
                'payment_type' => ['required', 'string', 'in:full,partial'],
                'amount_paid' => array_merge(InputRules::money(true, 0.0), [new FullPaymentAmountCoversStay(null, $payableForProof)]),
                'payment_proof' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('openPaymentModal', $booking->id);
        }

        $payable = $payableForProof;
        $paid = (float) $request->input('amount_paid', 0);
        $paymentType = (string) $request->input('payment_type', 'full');

        $file = $request->file('payment_proof');
        if (!$file) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('error', 'Please select a proof photo.')
                ->with('openPaymentModal', $booking->id);
        }

        $before = $booking->auditSnapshot();

        if ($booking->payment_proof_path && Storage::disk('public')->exists($booking->payment_proof_path)) {
            Storage::disk('public')->delete($booking->payment_proof_path);
        }

        $path = $file->store('payment_proofs', 'public');
        $absolute = Storage::disk('public')->path($path);
        $fileHash = is_file($absolute) ? hash_file('sha256', $absolute) : null;
        $booking->update([
            'payment_proof_path' => $path,
            'payment_proof_file_hash' => $fileHash,
            'payer_full_name' => $request->input('payer_full_name'),
            'payer_gcash_no' => $request->input('payer_gcash_no'),
            'payer_ref_no' => $request->input('payer_ref_no'),
            'payment_type' => $paymentType,
            'is_fully_paid' => $paymentType === 'full' || ($paid + 0.009 >= $payable),
            'amount_paid' => $request->input('amount_paid'),
        ]);
        $booking->refresh();

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            try {
                ActivityLog::log(
                    'booking.payment_proof_uploaded',
                    'Guest uploaded payment proof for booking #' . $booking->id . '.',
                    [
                        'entity_type' => 'booking',
                        'entity_id' => $booking->id,
                        'actor_type' => 'guest',
                        'metadata' => [
                            'before' => $before,
                            'after' => $booking->auditSnapshot(),
                        ],
                    ]
                );
            } catch (\Throwable) {
            }
        }

        return redirect()
            ->route('tenant.user.bookings.index')
            ->with('success', 'Payment proof uploaded. The resort will verify and confirm your booking shortly.');
    }
}
