<?php

namespace App\Models\TenantUserModel;

use App\Models\TenantRbacRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\TenantUserPasswordReset;

class RegularUser extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\RegularUserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

    protected $connection = 'tenant';
    protected $table = 'regular_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_rbac_role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'regular_user_id');
    }

    public function tenantRbacRole(): BelongsTo
    {
        return $this->belongsTo(TenantRbacRole::class, 'tenant_rbac_role_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $central = \App\Models\Tenant::where('database_name', \DB::connection('tenant')->getDatabaseName())->first();
        $host = $central?->primaryDomain()?->domain
            ?? $central?->domains()->value('domain');

        if (! $host) {
            return;
        }

        $this->notify(new TenantUserPasswordReset($token, $host));
    }
}

