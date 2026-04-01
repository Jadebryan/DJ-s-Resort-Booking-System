<?php

namespace App\Models\TenantModel;

use App\Models\TenantRbacRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\TenantPasswordReset;

class Tenant extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    // This model represents the **tenant staff/admin user inside tenant DB**
    protected $connection = 'tenant';
    protected $table = 'tenant_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_rbac_role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
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

        $this->notify(new TenantPasswordReset($token, $host));
    }
}
