<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;

class Tenant extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $table = 'tenants';

    protected $fillable = [
        'tenant_name',
        'app_display_name',
        'slug',
        'database_name',
        'plan_id',
        'is_active',
        'subscription_ends_at',
        'subscription_months',
        'email',
        'password',
        'metadata',
        'logo_path',
        'primary_color',
        'secondary_color',
        'version',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
        'subscription_months' => 'integer',
    ];

    /**
     * Get the subscription plan for this tenant.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the domains associated with this tenant.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function updateLogs(): HasMany
    {
        return $this->hasMany(TenantUpdateLog::class);
    }

    /**
     * Get the primary domain for this tenant.
     */
    public function primaryDomain()
    {
        return $this->domains()->where('is_primary', true)->first();
    }

    /** Name shown in browser title, staff UI, and emails for this resort (defaults to tenant_name). */
    public function appDisplayName(): string
    {
        $custom = trim((string) $this->app_display_name);

        return $custom !== '' ? $custom : (string) $this->tenant_name;
    }
}
