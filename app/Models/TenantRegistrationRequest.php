<?php

namespace App\Models;

use App\Models\AdminModel\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRegistrationRequest extends Model
{
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /** Calendar-agnostic “month” length when computing subscription end dates (signup & renewals). */
    public const BILLING_DAYS_PER_MONTH = 31;

    /** Maximum custom subscription length (days) at signup. */
    public const MAX_SUBSCRIPTION_DAYS = 1095;

    protected $fillable = [
        'token',
        'plan_id',
        'subscription_months',
        'subscription_days',
        'tenant_name',
        'primary_domain',
        'admin_name',
        'admin_email',
        'admin_password',
        'status',
        'payment_provider',
        'payment_reference',
        'payment_notes',
        'payment_proof_path',
        'paid_at',
        'submitted_for_review_at',
        'approved_tenant_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $hidden = [
        'admin_password',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'submitted_for_review_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'subscription_months' => 'integer',
            'subscription_days' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function approvedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'approved_tenant_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function usesCustomSubscriptionDays(): bool
    {
        $d = $this->subscription_days;

        return $d !== null && (int) $d > 0;
    }

    /** Full subscription length in days (custom days, or months × 31). */
    public function subscriptionLengthDays(): int
    {
        if ($this->usesCustomSubscriptionDays()) {
            return max(1, min(self::MAX_SUBSCRIPTION_DAYS, (int) $this->subscription_days));
        }

        return $this->subscriptionMonths() * self::BILLING_DAYS_PER_MONTH;
    }

    /** Billing “months” for legacy UI; with custom days this is an approximate ceil(days/31). */
    public function subscriptionMonths(): int
    {
        if ($this->usesCustomSubscriptionDays()) {
            return max(1, (int) ceil($this->subscriptionLengthDays() / self::BILLING_DAYS_PER_MONTH));
        }

        $m = (int) ($this->subscription_months ?? 1);

        return max(1, $m);
    }

    /** Short label for admin / payment summaries (e.g. "3 mo" or "45 days"). */
    public function subscriptionTermLabel(): ?string
    {
        if ($this->usesCustomSubscriptionDays()) {
            return (string) $this->subscriptionLengthDays().' '.__('days');
        }
        if ($this->subscription_months !== null && (int) $this->subscription_months > 0) {
            return (string) (int) $this->subscription_months.' '.__('mo');
        }

        return null;
    }

    /**
     * Total due: monthly price × months, or pro‑rated by days (price_monthly × days / 31).
     */
    public function amountDue(): float
    {
        $this->loadMissing('plan');
        $plan = $this->plan;
        if (! $plan) {
            return 0.0;
        }

        $price = (float) $plan->price_monthly;
        if ($this->usesCustomSubscriptionDays()) {
            $days = $this->subscriptionLengthDays();

            return round($price * ($days / self::BILLING_DAYS_PER_MONTH), 2);
        }

        return $price * $this->subscriptionMonths();
    }

    public function requiresPayment(): bool
    {
        return $this->amountDue() > 0;
    }
}
