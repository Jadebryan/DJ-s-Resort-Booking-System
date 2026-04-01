<?php

namespace App\Services;

class PaymentService
{
    public function paymongoEnabled(): bool
    {
        return config('services.paymongo.enabled', false)
            && ! empty(config('services.paymongo.secret'));
    }
}
