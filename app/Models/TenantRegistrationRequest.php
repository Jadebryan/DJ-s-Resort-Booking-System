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

    protected $fillable = [
        'token',
        'plan_id',
        'subscription_months',
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

    public function subscriptionMonths(): int
    {
        $m = (int) ($this->subscription_months ?? 1);

        return max(1, $m);
    }

    /** Total due: monthly plan price × N subscription months (each month = 31-day term on the tenant). */
    public function amountDue(): float
    {
        $this->loadMissing('plan');
        $plan = $this->plan;
        if (! $plan) {
            return 0.0;
        }

        return (float) $plan->price_monthly * $this->subscriptionMonths();
    }

    public function requiresPayment(): bool
    {
        return $this->amountDue() > 0;
    }
}
