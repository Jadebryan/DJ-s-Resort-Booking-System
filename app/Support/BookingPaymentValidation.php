<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Room;
use Carbon\CarbonInterface;

/**
 * Helpers for booking payment totals. Full payment must cover the stay total; that rule is applied
 * via {@see \App\Rules\FullPaymentAmountCoversStay} on all booking payment HTTP entry points.
 */
final class BookingPaymentValidation
{
    /**
     * Total amount for the stay (room rate × nights), matching Booking::amount_payable logic.
     */
    public static function payableForStay(?Room $room, mixed $checkIn, mixed $checkOut): float
    {
        if (! $room || $checkIn === null || $checkOut === null) {
            return 0.0;
        }

        $ci = $checkIn instanceof CarbonInterface ? $checkIn : \Carbon\Carbon::parse($checkIn);
        $co = $checkOut instanceof CarbonInterface ? $checkOut : \Carbon\Carbon::parse($checkOut);
        $nights = max(1, (int) $ci->diffInDays($co));

        return (float) ($room->price_per_night * $nights);
    }

    /**
     * Validation error when payment type is "full" but amount paid is below the stay total.
     */
    public static function fullPaymentShortfallMessage(string $paymentType, float $amountPaid, float $amountPayable): ?string
    {
        if ($paymentType !== 'full') {
            return null;
        }
        if ($amountPaid + 0.009 >= $amountPayable) {
            return null;
        }

        return __('When you choose full payment, the amount paid must be at least ₱:amount (the total for this stay).', ['amount' => number_format($amountPayable, 2)]);
    }
}
