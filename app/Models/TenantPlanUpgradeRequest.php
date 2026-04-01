<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPlanUpgradeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'current_plan_id',
        'requested_plan_id',
        'requested_months',
        'payment_method',
        'payment_reference',
        'payment_notes',
        'payment_proof_path',
        'proration_days_remaining',
        'proration_credit_amount',
        'proration_new_term_total',
        'proration_amount_due',
        'proration_base_days',
        'proration_rollover_days',
        'proration_total_days',
        'status',
        'review_notes',
        'reviewed_by_admin_id',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_months' => 'integer',
        'proration_days_remaining' => 'integer',
        'proration_credit_amount' => 'decimal:2',
        'proration_new_term_total' => 'decimal:2',
        'proration_amount_due' => 'decimal:2',
        'proration_base_days' => 'integer',
        'proration_rollover_days' => 'integer',
        'proration_total_days' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'current_plan_id');
    }

    public function requestedPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'requested_plan_id');
    }
}
