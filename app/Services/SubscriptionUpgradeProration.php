<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantRegistrationRequest;
use Carbon\Carbon;
use DateTimeInterface;

class SubscriptionUpgradeProration
{
    public static function billingDaysPerMonth(): int
    {
        return TenantRegistrationRequest::BILLING_DAYS_PER_MONTH;
    }

    /**
     * Prorate an upgrade using 31-day "months" and daily rates derived from monthly prices.
     *
     * Credit: unused value of the current plan from remaining subscription days.
     * New term retail: requested months × new plan monthly price.
     * Amount due: max(0, new term retail − credit).
     * Rollover: credit converted into extra days at the new plan's daily rate (so unused value is preserved).
     * New end: as-of date + base days (months×31) + rollover days.
     *
     * @return array{
     *     days_remaining:int,
     *     old_monthly:float,
     *     new_monthly:float,
     *     credit_amount:float,
     *     new_term_total:float,
     *     amount_due:float,
     *     base_days:int,
     *     rollover_days:int,
     *     total_days:int,
     *     new_subscription_ends_at:Carbon
     * }
     */
    public static function compute(
        Tenant $tenant,
        ?Plan $currentPlan,
        Plan $newPlan,
        int $requestedMonths,
        DateTimeInterface $asOf
    ): array {
        $months = max(1, min(12, $requestedMonths));
        $daysPerMonth = self::billingDaysPerMonth();

        $asOfCarbon = Carbon::parse($asOf)->startOfDay();

        $endsAt = $tenant->subscription_ends_at;
        $daysRemaining = 0;
        if ($endsAt instanceof Carbon && $endsAt->copy()->startOfDay()->gt($asOfCarbon)) {
            $daysRemaining = (int) $asOfCarbon->diffInDays($endsAt->copy()->startOfDay());
        }

        $isRenewal = $currentPlan && (int) $currentPlan->id === (int) $newPlan->id;
        $oldMonthly = $currentPlan ? (float) $currentPlan->price_monthly : 0.0;
        $newMonthly = (float) $newPlan->price_monthly;

        $oldDaily = $daysPerMonth > 0 ? $oldMonthly / $daysPerMonth : 0.0;
        $newDaily = $daysPerMonth > 0 ? $newMonthly / $daysPerMonth : 0.0;

        // Renewal (same plan) works as advance payment: no deduction/credit, we append months after current term.
        $credit = $isRenewal ? 0.0 : round($daysRemaining * $oldDaily, 2);
        $newTermTotal = round($newMonthly * $months, 2);
        $amountDue = round(max(0.0, $newTermTotal - $credit), 2);

        $baseDays = $months * $daysPerMonth;

        $rolloverDays = 0;
        if ($isRenewal) {
            $rolloverDays = $daysRemaining;
        } elseif ($newDaily > 0.00001 && $credit > 0) {
            $rolloverDays = (int) round($credit / $newDaily);
        }

        $totalDays = $baseDays + $rolloverDays;

        return [
            'days_remaining' => $daysRemaining,
            'old_monthly' => $oldMonthly,
            'new_monthly' => $newMonthly,
            'credit_amount' => $credit,
            'new_term_total' => $newTermTotal,
            'amount_due' => $amountDue,
            'base_days' => $baseDays,
            'rollover_days' => $rolloverDays,
            'total_days' => $totalDays,
            'new_subscription_ends_at' => $asOfCarbon->copy()->addDays($totalDays),
        ];
    }
}
