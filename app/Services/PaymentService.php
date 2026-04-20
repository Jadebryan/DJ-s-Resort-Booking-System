<?php

namespace App\Services;

use App\Services\PayMongo\PayMongoClient;

class PaymentService
{
    public function paymongoEnabled(): bool
    {
        return app(PayMongoClient::class)->isConfigured();
    }

    /** PayMongo minimum charge is ₱20.00 (2000 centavos). */
    public static function paymongoMinimumPhp(): float
    {
        return 20.0;
    }
}
