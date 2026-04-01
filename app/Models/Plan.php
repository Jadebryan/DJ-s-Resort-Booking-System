<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'max_rooms',
        'price_monthly',
        'price_yearly',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    /**
     * Tenants subscribed to this plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Whether this plan allows unlimited rooms.
     */
    public function hasUnlimitedRooms(): bool
    {
        return $this->max_rooms === null;
    }

    /**
     * Check if a room count is within plan limit.
     */
    public function allowsRoomCount(int $count): bool
    {
        if ($this->hasUnlimitedRooms()) {
            return true;
        }
        return $count <= $this->max_rooms;
    }
}
