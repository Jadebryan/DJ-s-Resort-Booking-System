<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Room;
use App\Notifications\BookingStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicBookingController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->to(tenant_url('/'));
    }

    public function show(Request $request, string $room): View|RedirectResponse
    {
        try {
            $room = Room::findOrFail($room);
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('tenant.book.index')
                ->with('error', 'Room not found. Please choose from the list below.');
        }
        if (! $room->is_available) {
            return redirect()
                ->route('tenant.book.index')
                ->with('error', 'That room is not available for booking.');
        }
        $room->load('images');

        return view('Tenant.book.show', compact('room'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isGuest = !auth('regular_user')->check();
        $rules = [
            'room_id' => ['required', 'integer', 'min:1'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
        if ($isGuest) {
            $rules['guest_name'] = ['required', 'string', 'max:255'];
            $rules['guest_email'] = ['required', 'email'];
        } else {
            $rules['payment_type'] = ['required', 'string', 'in:full,partial'];
            $rules['payer_full_name'] = ['required', 'string', 'max:255'];
            $rules['payer_gcash_no'] = ['required', 'string', 'max:50'];
            $rules['payer_ref_no'] = ['required', 'string', 'max:80'];
            $rules['amount_paid'] = ['required', 'numeric', 'min:0'];
            $rules['payment_proof'] = ['required', 'file', 'mimes:jpeg,jpg,png', 'max:5120'];
        }
        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $isGuest) {
                return redirect()
                    ->route('tenant.user.bookings.index')
                    ->withErrors($e->errors())
                    ->withInput()
                    ->with('openBookModalRoomId', (int) $request->input('room_id'));
            }

            throw $e;
        }

        // Resolve room on tenant connection (Room model uses tenant connection)
        $room = Room::find($validated['room_id']);
        if (!$room) {
            if (! $isGuest) {
                return redirect()
                    ->route('tenant.user.bookings.index')
                    ->withInput()
                    ->withErrors(['room_id' => 'Selected room is invalid or not available.'])
                    ->with('openBookModalRoomId', (int) $request->input('room_id'));
            }
            return back()->withInput()->withErrors(['room_id' => 'Selected room is invalid or not available.']);
        }
        if (!$room->is_available) {
            if (! $isGuest) {
                return redirect()
                    ->route('tenant.user.bookings.index')
                    ->withInput()
                    ->withErrors(['room_id' => 'This room is not available for booking.'])
                    ->with('openBookModalRoomId', (int) $room->id);
            }
            return back()->withInput()->withErrors(['room_id' => 'This room is not available for booking.']);
        }

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);

        $overlap = Booking::where('room_id', $room->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                        $q2->where('check_in', '<=', $checkIn)->where('check_out', '>=', $checkOut);
                    });
            })->exists();

        if ($overlap) {
            if (! $isGuest) {
                return redirect()
                    ->route('tenant.user.bookings.index')
                    ->withInput()
                    ->withErrors(['check_in' => 'This room is already booked for the selected dates.'])
                    ->with('openBookModalRoomId', (int) $room->id);
            }
            return back()->withInput()->withErrors(['check_in' => 'This room is already booked for the selected dates.']);
        }

        $user = auth('regular_user')->user();
        $userId = $user?->id;
        $proofPath = null;
        if (! $isGuest) {
            $proofFile = $request->file('payment_proof');
            if (! $proofFile) {
                return redirect()
                    ->route('tenant.user.bookings.index')
                    ->withInput()
                    ->withErrors(['payment_proof' => 'Please upload a payment proof image.'])
                    ->with('openBookModalRoomId', (int) $room->id);
            }
            $proofPath = $proofFile->store('payment_proofs', 'public');
        }

        $proofFileHash = null;
        if ($proofPath) {
            $absolute = Storage::disk('public')->path($proofPath);
            $proofFileHash = is_file($absolute) ? hash_file('sha256', $absolute) : null;
        }

        $booking = Booking::create([
            'room_id' => $room->id,
            'regular_user_id' => $userId,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'status' => 'pending',
            'guest_name' => $userId ? $user->name : ($validated['guest_name'] ?? null),
            'guest_email' => $userId ? $user->email : ($validated['guest_email'] ?? null),
            'guest_phone' => $validated['guest_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'payment_proof_path' => $proofPath,
            'payment_proof_file_hash' => $proofFileHash,
            'payer_full_name' => $validated['payer_full_name'] ?? null,
            'payer_gcash_no' => $validated['payer_gcash_no'] ?? null,
            'payer_ref_no' => $validated['payer_ref_no'] ?? null,
            'payment_type' => $validated['payment_type'] ?? null,
            'is_fully_paid' => ($validated['payment_type'] ?? null) === 'full',
            'amount_paid' => $validated['amount_paid'] ?? null,
        ]);
        $booking->load('room');
        $this->notifyGuest($booking, 'pending');

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            try {
                $guestCtx = [
                    'entity_type' => 'booking',
                    'entity_id' => $booking->id,
                    'actor_type' => 'guest',
                    'metadata' => [
                        'after' => $booking->auditSnapshot(),
                    ],
                ];
                if ($userId) {
                    $guestCtx['regular_user_id'] = $userId;
                }
                ActivityLog::log(
                    'booking.created',
                    'Booking #' . $booking->id . ' (' . ($booking->room?->name ?? 'room') . ') submitted' . ($proofPath ? ' with payment proof.' : '.'),
                    $guestCtx
                );
            } catch (\Throwable) {
            }
        }

        $message = ! $isGuest
            ? 'Booking request submitted with payment details. The resort will verify and confirm shortly.'
            : 'Booking request submitted. The resort will confirm shortly.';
        if ($userId) {
            return redirect()
                ->route('tenant.user.bookings.index')
                ->with('success', $message);
        }
        return redirect()
            ->route('tenant.book.index')
            ->with('success', $message);
    }

    protected function notifyGuest(Booking $booking, string $status): void
    {
        $email = $booking->guest_email ?? $booking->user?->email ?? null;
        if (empty($email)) {
            return;
        }
        $notifiable = (new AnonymousNotifiable())->route('mail', $email);
        $phone = $booking->guest_phone ?? $booking->user?->phone ?? null;
        if (!empty($phone)) {
            $notifiable->route('sms', $phone);
        }
        try {
            $notifiable->notify(new BookingStatusNotification($booking, $status));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
