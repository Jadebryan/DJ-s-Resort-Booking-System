<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Booking;
use Illuminate\Support\Facades\URL;

final class SignedBookingReceiptUrl
{
    /**
     * Signed URL for guests to open the printable receipt (no login).
     */
    public static function make(Booking $booking, string $schemeAndHttpHost): ?string
    {
        if (! $booking->is_fully_paid) {
            return null;
        }

        $root = rtrim($schemeAndHttpHost, '/');
        $host = parse_url($root, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        URL::forceRootUrl($root);
        try {
            return URL::temporarySignedRoute(
                'tenant.booking.receipt.guest',
                now()->addYear(),
                ['tenant_domain' => $host, 'booking' => $booking->id],
                absolute: true
            );
        } finally {
            URL::forceRootUrl(null);
        }
    }
}
