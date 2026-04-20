<?php

declare(strict_types=1);

namespace App\Services\PayMongo;

use App\Models\Booking;

final class BookingPayMongoSync
{
    /**
     * Apply a succeeded PayMongo PaymentIntent response to the booking.
     *
     * @param  array<string, mixed>  $paymentIntentResponse  Decoded JSON from GET /payment_intents/:id
     */
    public function applySucceededIntent(Booking $booking, array $paymentIntentResponse): bool
    {
        $attrs = $paymentIntentResponse['data']['attributes'] ?? null;
        if (! is_array($attrs)) {
            return false;
        }

        if (($attrs['status'] ?? '') !== 'succeeded') {
            return false;
        }

        if ($booking->payer_gcash_no === 'GCash (PayMongo)' && filled($booking->payer_ref_no)) {
            $booking->forceFill(['paymongo_payment_intent_id' => null])->save();

            return true;
        }

        $amountCentavos = (int) ($attrs['amount'] ?? 0);
        $sessionPhp = round($amountCentavos / 100, 2);

        $payRef = $this->extractPaymentId($attrs);
        if ($payRef === '') {
            $payRef = (string) ($paymentIntentResponse['data']['id'] ?? '');
        }

        $payable = (float) $booking->amount_payable;
        $prevPaid = (float) ($booking->amount_paid ?? 0);
        $chosenType = (string) ($booking->payment_type ?? 'full');

        $newPaid = $chosenType === 'full'
            ? $sessionPhp
            : $prevPaid + $sessionPhp;

        $isFull = $newPaid + 0.005 >= $payable;

        $booking->forceFill([
            'payer_gcash_no' => 'GCash (PayMongo)',
            'payer_ref_no' => $payRef,
            'amount_paid' => $newPaid,
            'payment_type' => $isFull ? 'full' : 'partial',
            'is_fully_paid' => $isFull,
            'paymongo_payment_intent_id' => null,
        ])->save();

        return true;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function extractPaymentId(array $attributes): string
    {
        $payments = $attributes['payments'] ?? [];
        if (! is_array($payments) || $payments === []) {
            return '';
        }
        $first = $payments[0];
        if (is_string($first)) {
            return $first;
        }
        if (is_array($first) && isset($first['id'])) {
            return (string) $first['id'];
        }
        if (is_array($first) && isset($first['attributes']['id'])) {
            return (string) $first['attributes']['id'];
        }

        return '';
    }
}
