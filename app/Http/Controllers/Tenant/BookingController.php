<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Tenant;
use App\Notifications\BookingStatusNotification;
use App\Support\BookingCalendarGrid;
use App\Rules\FullPaymentAmountCoversStay;
use App\Support\InputRules;
use App\Support\TenantPlanFeatures;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Ensure the tenant DB connection is pointed at the current tenant.
     * This mirrors the logic in SetTenantDatabase but is scoped to this controller
     * so route model lookups always hit the correct tenant database.
     */
    protected function ensureTenantConnection(Request $request): void
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            return;
        }

        config(['database.connections.tenant.database' => $tenant->database_name]);
        config(['database.connections.tenant.host' => env('DB_HOST', '127.0.0.1')]);
        config(['database.connections.tenant.port' => env('DB_PORT', '3306')]);
        config(['database.connections.tenant.username' => env('DB_USERNAME', 'root')]);
        config(['database.connections.tenant.password' => env('DB_PASSWORD', '')]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    protected function tenantAllowsCalendar(Request $request): bool
    {
        return TenantPlanFeatures::hasRequestFeature($request, 'booking_calendar');
    }

    protected function tenantAllowsBasicBooking(Request $request): bool
    {
        return TenantPlanFeatures::hasRequestFeature($request, 'basic_booking');
    }

    protected function tenantAllowsBookingArchive(Request $request): bool
    {
        return TenantPlanFeatures::hasRequestFeature($request, 'booking_archive');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $this->ensureTenantConnection($request);

        if (! $this->tenantAllowsBasicBooking($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Booking management is not enabled in your current subscription.');
        }

        $bookingsQuery = Booking::with(['room', 'user'])->orderBy('check_in', 'desc');
        if (! $this->tenantAllowsBookingArchive($request)) {
            $bookingsQuery->where('check_in', '>=', now()->subDays(90));
        }

        $bookings = $bookingsQuery->get();

        $canUseCalendar = $this->tenantAllowsCalendar($request);
        $calendarPayload = $canUseCalendar
            ? $this->buildBookingCalendarPayload($request)
            : null;

        return view('Tenant.bookings.index', compact('bookings', 'canUseCalendar', 'calendarPayload'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildBookingCalendarPayload(Request $request): array
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $date = Carbon::createFromDate($year, $month, 1);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $monthBookings = Booking::with(['room', 'user'])
            ->where('check_in', '<=', $endOfMonth)
            ->where('check_out', '>=', $startOfMonth)
            ->orderBy('check_in')
            ->get();

        $calendarWeeks = BookingCalendarGrid::buildWeeks($startOfMonth, $endOfMonth, $monthBookings);

        $prev = $date->copy()->subMonth();
        $next = $date->copy()->addMonth();

        return [
            'date' => $date,
            'year' => $year,
            'month' => $month,
            'calendarWeeks' => $calendarWeeks,
            'monthBookings' => $monthBookings,
            'prevYear' => $prev->year,
            'prevMonth' => $prev->month,
            'nextYear' => $next->year,
            'nextMonth' => $next->month,
        ];
    }

    public function calendar(Request $request): View|RedirectResponse
    {
        $this->ensureTenantConnection($request);

        if (!$this->tenantAllowsCalendar($request)) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', 'Booking calendar is available on Standard and Premium plans. Upgrade to access.');
        }

        return view('Tenant.bookings.calendar', $this->buildBookingCalendarPayload($request));
    }

    /**
     * Printable payment receipt for fully paid bookings (thermal / POS printers).
     */
    public function receipt(Request $request): View
    {
        $this->ensureTenantConnection($request);

        if (! $this->tenantAllowsBasicBooking($request)) {
            abort(403);
        }

        if (! tenant_staff_can('bookings', 'read')) {
            abort(403);
        }

        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $id = (string) $request->route('booking');
        $booking = Booking::with(['room', 'user'])->find($id);
        if (! $booking) {
            abort(404);
        }

        if (! $booking->is_fully_paid) {
            abort(403, __('Receipts are only available for fully paid bookings.'));
        }

        $nights = ($booking->check_in && $booking->check_out)
            ? max(1, (int) $booking->check_in->diffInDays($booking->check_out))
            : 1;

        $printedAt = now()->timezone(config('app.timezone'));

        return view('Tenant.bookings.receipt', [
            'tenant' => $tenant,
            'booking' => $booking,
            'nights' => $nights,
            'printedAt' => $printedAt,
            'isGuestReceipt' => false,
        ]);
    }

    /**
     * Guest-facing printable receipt (signed URL from confirmation email; no login).
     */
    public function guestReceipt(Request $request): View
    {
        $this->ensureTenantConnection($request);

        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $id = (string) $request->route('booking');
        $booking = Booking::with(['room', 'user'])->find($id);
        if (! $booking) {
            abort(404);
        }

        if (! $booking->is_fully_paid) {
            abort(404);
        }

        $nights = ($booking->check_in && $booking->check_out)
            ? max(1, (int) $booking->check_in->diffInDays($booking->check_out))
            : 1;

        $printedAt = now()->timezone(config('app.timezone'));

        return view('Tenant.bookings.receipt', [
            'tenant' => $tenant,
            'booking' => $booking,
            'nights' => $nights,
            'printedAt' => $printedAt,
            'isGuestReceipt' => true,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $this->ensureTenantConnection($request);

        if (! $this->tenantAllowsBasicBooking($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Booking management is not enabled in your current subscription.');
        }

        $this->ensureTenantConnection($request);

        // Always read the ID from the route to avoid any parameter binding quirks.
        $booking = (string) $request->route('booking');

        $bookingModel = Booking::with(['room', 'user'])->find($booking);
        if (! $bookingModel) {
            $existingIds = Booking::orderBy('id')->pluck('id')->implode(', ');

            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', "Booking not found (id={$booking}). Existing booking IDs: [{$existingIds}].");
        }

        $before = $bookingModel->auditSnapshot();
        $updated = Booking::whereKey($booking)->update(['status' => 'confirmed']);

        if ($updated) {
            $bookingModel->refresh();
            $bookingModel->load(['room', 'user']);
            if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
                try {
                    ActivityLog::log(
                        'booking.confirmed',
                        'Booking #' . $bookingModel->id . ' (' . ($bookingModel->room?->name ?? 'room') . ') confirmed.',
                        [
                            'entity_type' => 'booking',
                            'entity_id' => $bookingModel->id,
                            'metadata' => [
                                'before' => $before,
                                'after' => $bookingModel->auditSnapshot(),
                            ],
                        ]
                    );
                } catch (\Throwable) {
                }
            }
            $this->notifyGuest($bookingModel, 'confirmed');

            return redirect()
                ->route('tenant.bookings.index')
                ->with('success', 'Booking confirmed.');
        }

        return redirect()
            ->route('tenant.bookings.index')
            ->with('error', 'Booking could not be confirmed.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->ensureTenantConnection($request);

        if (! $this->tenantAllowsBasicBooking($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Booking management is not enabled in your current subscription.');
        }

        $this->ensureTenantConnection($request);

        $booking = (string) $request->route('booking');

        $bookingModel = Booking::with(['room', 'user'])->find($booking);
        if (! $bookingModel) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', 'Booking is already cancelled or no longer exists.');
        }

        $before = $bookingModel->auditSnapshot();
        $updated = Booking::whereKey($booking)
            ->where('status', '!=', 'cancelled')
            ->update(['status' => 'cancelled']);

        if ($updated) {
            $bookingModel->refresh();
            $bookingModel->load(['room', 'user']);
            if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
                try {
                    ActivityLog::log(
                        'booking.cancelled',
                        'Booking #' . $bookingModel->id . ' (' . ($bookingModel->room?->name ?? 'room') . ') cancelled.',
                        [
                            'entity_type' => 'booking',
                            'entity_id' => $bookingModel->id,
                            'metadata' => [
                                'before' => $before,
                                'after' => $bookingModel->auditSnapshot(),
                            ],
                        ]
                    );
                } catch (\Throwable) {
                }
            }
            $this->notifyGuest($bookingModel, 'cancelled');

            return redirect()
                ->route('tenant.bookings.index')
                ->with('success', 'Booking cancelled.');
        }

        return redirect()
            ->route('tenant.bookings.index')
            ->with('error', 'Booking is already cancelled or no longer exists.');
    }

    /**
     * Update booking details and payment for not-fully-paid bookings (partial payment or balance due).
     * Applies to bookings from signed-in guests and walk-in guests (regular_user_id may be null).
     */
    public function update(Request $request): RedirectResponse
    {
        $this->ensureTenantConnection($request);

        if (! $this->tenantAllowsBasicBooking($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Booking management is not enabled in your current subscription.');
        }

        if (! tenant_staff_can('bookings', 'update')) {
            abort(403);
        }

        $bookingId = (string) $request->route('booking');
        $bookingModel = Booking::with('room')->find($bookingId);
        if (! $bookingModel) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', 'Booking not found.');
        }

        if ($bookingModel->status === 'cancelled') {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', 'Cannot edit a cancelled booking.');
        }

        if ($bookingModel->is_fully_paid) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', 'Fully paid bookings cannot be edited here.');
        }

        try {
            $validated = $request->validate([
                'check_in' => ['required', 'date'],
                'check_out' => ['required', 'date', 'after:check_in'],
                'guest_name' => InputRules::personName(255, true),
                'guest_email' => ['nullable', 'email:rfc,dns', 'max:254'],
                'guest_phone' => InputRules::phone(25, false),
                'notes' => ['nullable', 'string', 'max:1000'],
                'payment_type' => ['required', 'string', 'in:full,partial'],
                'payer_full_name' => InputRules::personName(255, true),
                'payer_gcash_no' => InputRules::paymentMethod(80, true),
                'payer_ref_no' => InputRules::reference(80, true),
                'amount_paid' => array_merge(InputRules::money(true, 0.0), [new FullPaymentAmountCoversStay($bookingModel->room)]),
                'payment_proof' => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('tenant.bookings.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('openTenantBookingAdminEditId', $bookingId);
        }

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);

        $overlap = Booking::where('room_id', $bookingModel->room_id)
            ->where('id', '!=', $bookingModel->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                        $q2->where('check_in', '<=', $checkIn)->where('check_out', '>=', $checkOut);
                    });
            })->exists();

        if ($overlap) {
            return redirect()
                ->route('tenant.bookings.index')
                ->withErrors(['check_in' => 'This room is already booked for the selected dates.'])
                ->withInput()
                ->with('openTenantBookingAdminEditId', $bookingId);
        }

        $before = $bookingModel->auditSnapshot();

        $proofPath = $bookingModel->payment_proof_path;
        $proofHash = $bookingModel->payment_proof_file_hash;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            if ($bookingModel->payment_proof_path && Storage::disk('public')->exists($bookingModel->payment_proof_path)) {
                Storage::disk('public')->delete($bookingModel->payment_proof_path);
            }
            $proofPath = $file->store('payment_proofs', 'public');
            $absolute = Storage::disk('public')->path($proofPath);
            $proofHash = is_file($absolute) ? hash_file('sha256', $absolute) : null;
        }

        $bookingModel->fill([
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'payment_type' => $validated['payment_type'],
            'payer_full_name' => $validated['payer_full_name'],
            'payer_gcash_no' => $validated['payer_gcash_no'],
            'payer_ref_no' => $validated['payer_ref_no'],
            'amount_paid' => $validated['amount_paid'],
            'payment_proof_path' => $proofPath,
            'payment_proof_file_hash' => $proofHash,
        ]);

        $bookingModel->load('room');
        $payable = $bookingModel->amount_payable;
        $paid = (float) $validated['amount_paid'];
        $bookingModel->is_fully_paid = ($validated['payment_type'] === 'full') || ($paid + 0.009 >= $payable);

        $bookingModel->save();

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            try {
                ActivityLog::log(
                    'booking.updated',
                    'Staff updated booking #' . $bookingModel->id . ' (' . ($bookingModel->room?->name ?? 'room') . ').',
                    [
                        'entity_type' => 'booking',
                        'entity_id' => $bookingModel->id,
                        'metadata' => [
                            'before' => $before,
                            'after' => $bookingModel->auditSnapshot(),
                        ],
                    ]
                );
            } catch (\Throwable) {
            }
        }

        return redirect()
            ->route('tenant.bookings.index')
            ->with('success', 'Booking updated.')
            ->with('openTenantBookingAdminEditId', $bookingModel->id);
    }

    protected function notifyGuest(Booking $booking, string $status): void
    {
        $email = $booking->guest_email ?? $booking->user?->email;
        if (empty($email)) {
            return;
        }
        $notifiable = (new AnonymousNotifiable())->route('mail', $email);
        $phone = $booking->guest_phone ?? $booking->user?->phone ?? null;
        if (!empty($phone)) {
            $notifiable->route('sms', $phone);
        }
        try {
            $booking->load('room');
            $tenant = request()->attributes->get('tenant');
            $tenantModel = $tenant instanceof Tenant ? $tenant : null;
            $portalBase = request()->getSchemeAndHttpHost();
            $notifiable->notify(new BookingStatusNotification($booking, $status, $tenantModel, $portalBase));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
