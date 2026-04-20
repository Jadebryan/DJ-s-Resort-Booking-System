<?php

namespace App\Models;

use App\Models\TenantUserModel\RegularUser;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $connection = 'tenant';

    protected $table = 'bookings';

    protected $fillable = [
        'room_id',
        'regular_user_id',
        'check_in',
        'check_out',
        'status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'notes',
        'payment_proof_path',
        'payment_proof_file_hash',
        'payer_full_name',
        'payer_gcash_no',
        'payer_ref_no',
        'paymongo_payment_intent_id',
        'payment_type',
        'is_fully_paid',
        'amount_paid',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'is_fully_paid' => 'boolean',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Total amount payable for this booking (room price × nights).
     */
    public function getAmountPayableAttribute(): float
    {
        if (!$this->room || !$this->check_in || !$this->check_out) {
            return 0.0;
        }
        $nights = (int) $this->check_in->diffInDays($this->check_out);
        $nights = max(1, $nights);
        return (float) ($this->room->price_per_night * $nights);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(RegularUser::class, 'regular_user_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'room_id' => $this->room_id,
            'regular_user_id' => $this->regular_user_id,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'payment_proof_path' => $this->payment_proof_path,
            'payment_proof_file_hash' => $this->payment_proof_file_hash,
            'payer_full_name' => $this->payer_full_name,
            'payer_gcash_no' => $this->payer_gcash_no,
            'payer_ref_no' => $this->payer_ref_no,
            'payment_type' => $this->payment_type,
            'amount_paid' => $this->amount_paid,
            'is_fully_paid' => $this->is_fully_paid,
        ];
    }
}
