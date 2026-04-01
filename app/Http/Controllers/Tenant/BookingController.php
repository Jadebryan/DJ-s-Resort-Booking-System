<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Tenant;
use App\Notifications\BookingStatusNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            return false;
        }
        $plan = $tenant->loadMissing('plan')->plan;
        if (!$plan) {
            return false;
        }
        return is_array($plan->features) && in_array('booking_calendar', $plan->features);
    }

    public function index(Request $request): View
    {
        $bookings = Booking::with(['room', 'user'])
            ->orderBy('check_in', 'desc')
            ->get();

        return view('Tenant.bookings.index', compact('bookings'));
    }

    public function calendar(Request $request): View|RedirectResponse
    {
        if (!$this->tenantAllowsCalendar($request)) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('error', 'Booking calendar is available on Standard and Premium plans. Upgrade to access.');
        }

        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $date = Carbon::createFromDate($year, $month, 1);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $bookings = Booking::with('room')
            ->where('check_in', '<=', $endOfMonth)
            ->where('check_out', '>=', $startOfMonth)
            ->orderBy('check_in')
            ->get();

        $calendarWeeks = $this->buildCalendarWeeks($startOfMonth, $endOfMonth, $bookings);

        $prev = $date->copy()->subMonth();
        $next = $date->copy()->addMonth();

        return view('Tenant.bookings.calendar', [
            'date' => $date,
            'year' => $year,
            'month' => $month,
            'calendarWeeks' => $calendarWeeks,
            'prevYear' => $prev->year,
            'prevMonth' => $prev->month,
            'nextYear' => $next->year,
            'nextMonth' => $next->month,
        ]);
    }

    /**
     * Build calendar grid: array of weeks, each week = 7 days. Each day = ['date' => Carbon|null, 'bookings' => Collection].
     */
    protected function buildCalendarWeeks(Carbon $startOfMonth, Carbon $endOfMonth, Collection $bookings): array
    {
        $start = $startOfMonth->copy();
        $end = $endOfMonth->copy();
        $firstWeekday = (int) $start->format('w'); // 0 = Sunday
        $daysInMonth = $start->daysInMonth;

        $weeks = [];
        $day = $start->copy()->subDays($firstWeekday);

        for ($w = 0; $w < 6; $w++) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $cellDate = $day->copy();
                $isCurrentMonth = $cellDate->between($startOfMonth, $endOfMonth);
                $dayBookings = $bookings->filter(function (Booking $b) use ($cellDate) {
                    return $cellDate->between($b->check_in, $b->check_out);
                });
                $week[] = [
                    'date' => $cellDate->copy(),
                    'isCurrentMonth' => $isCurrentMonth,
                    'bookings' => $dayBookings->values(),
                ];
                $day->addDay();
            }
            $weeks[] = $week;
            if ($day->month !== $startOfMonth->month && $day->day > 7) {
                break;
            }
        }

        return $weeks;
    }

    public function confirm(Request $request): RedirectResponse
    {
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
            $notifiable->notify(new BookingStatusNotification($booking, $status));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
