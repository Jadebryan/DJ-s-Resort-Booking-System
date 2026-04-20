<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Room;
use App\Support\BookingPaymentValidation;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * When payment_type is "full", amount_paid must be at least the stay total
 * (see BookingPaymentValidation). Optional empty amount when paying full via GCash (PayMongo).
 */
final class FullPaymentAmountCoversStay implements ValidationRule, DataAwareRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function __construct(
        private readonly ?Room $room = null,
        private readonly ?float $payableOverride = null,
        private readonly bool $allowEmptyAmountWhenFull = false,
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (($this->data['payment_type'] ?? '') !== 'full') {
            return;
        }

        if ($this->allowEmptyAmountWhenFull && ($value === null || $value === '')) {
            return;
        }

        $paid = is_numeric($value) ? (float) $value : 0.0;

        $payable = $this->payableOverride;
        if ($payable === null) {
            $room = $this->room;
            if ($room === null) {
                $rid = (int) ($this->data['room_id'] ?? 0);
                $room = $rid > 0 ? Room::query()->find($rid) : null;
            }
            $payable = BookingPaymentValidation::payableForStay(
                $room,
                $this->data['check_in'] ?? null,
                $this->data['check_out'] ?? null
            );
        }

        $msg = BookingPaymentValidation::fullPaymentShortfallMessage('full', $paid, $payable);
        if ($msg !== null) {
            $fail($msg);
        }
    }
}
