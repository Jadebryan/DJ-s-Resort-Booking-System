<?php

namespace App\Support;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingCalendarGrid
{
    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array<int, array<int, array{date: Carbon, isCurrentMonth: bool, bookings: Collection<int, Booking>}>>
     */
    public static function buildWeeks(Carbon $startOfMonth, Carbon $endOfMonth, Collection $bookings): array
    {
        $start = $startOfMonth->copy();
        $firstWeekday = (int) $start->format('w');

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
}
