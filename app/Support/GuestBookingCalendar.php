<?php

namespace App\Support;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuestBookingCalendar
{
    /**
     * Current month only (matches tenant staff dashboard calendar scope).
     *
     * @return array<string, mixed>
     */
    public static function currentMonthForUser(int $regularUserId): array
    {
        $request = Request::create('/', 'GET', [
            'year' => now()->year,
            'month' => now()->month,
        ]);

        return self::monthPayloadForUser($request, $regularUserId);
    }

    /**
     * Month grid payload for a registered guest’s bookings only.
     *
     * @return array<string, mixed>
     */
    public static function monthPayloadForUser(Request $request, int $regularUserId): array
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $date = Carbon::createFromDate($year, $month, 1);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $monthBookings = Booking::with(['room', 'user'])
            ->where('regular_user_id', $regularUserId)
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
}
