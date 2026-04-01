<?php

namespace App\Http\Controllers\TenantUser;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Room;
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

        return view('TenantUser.bookings.index', compact('bookings', 'rooms'));
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
                'guest_name' => ['nullable', 'string', 'max:255'],
                'guest_email' => ['nullable', 'email', 'max:255'],
                'guest_phone' => ['nullable', 'string', 'max:50'],
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

        try {
            $request->validate([
                'payer_full_name' => ['required', 'string', 'max:255'],
                'payer_gcash_no' => ['required', 'string', 'max:50'],
                'payer_ref_no' => ['required', 'string', 'max:80'],
                'payment_type' => ['required', 'string', 'in:full,partial'],
                'amount_paid' => ['required', 'numeric', 'min:0'],
                'payment_proof' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('openPaymentModal', $booking->id);
        }

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
        $paymentType = $request->input('payment_type', 'full');
        $booking->update([
            'payment_proof_path' => $path,
            'payment_proof_file_hash' => $fileHash,
            'payer_full_name' => $request->input('payer_full_name'),
            'payer_gcash_no' => $request->input('payer_gcash_no'),
            'payer_ref_no' => $request->input('payer_ref_no'),
            'payment_type' => $paymentType,
            'is_fully_paid' => $paymentType === 'full',
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
