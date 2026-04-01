<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\TenantModel\Tenant as TenantStaffUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantRbacRole extends Model
{
    public const KIND_STAFF = 'staff';

    public const KIND_CUSTOMER = 'customer';

    protected $connection = 'tenant';

    protected $table = 'tenant_rbac_roles';

    protected $fillable = [
        'kind',
        'slug',
        'name',
        'description',
        'permissions',
        'is_system',
        'updated_by_tenant_user_id',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function updatedByStaff(): BelongsTo
    {
        return $this->belongsTo(TenantStaffUser::class, 'updated_by_tenant_user_id');
    }

    public function tenantStaffUsers(): HasMany
    {
        return $this->hasMany(TenantStaffUser::class, 'tenant_rbac_role_id');
    }

    public function regularUsers(): HasMany
    {
        return $this->hasMany(\App\Models\TenantUserModel\RegularUser::class, 'tenant_rbac_role_id');
    }

    /**
     * @param  array<string, list<string>>  $matrix
     */
    public function allows(string $resource, string $action, array $staffResources): bool
    {
        if (! isset($staffResources[$resource])) {
            return false;
        }
        if (! in_array($action, $staffResources[$resource], true)) {
            return false;
        }
        $perms = $this->permissions ?? [];
        if (! is_array($perms)) {
            return false;
        }
        $allowed = $perms[$resource] ?? [];

        return is_array($allowed) && in_array($action, $allowed, true);
    }

    /**
     * @param  array<string, list<string>>  $portalResources
     */
    public function allowsPortal(string $resource, string $action, array $portalResources): bool
    {
        if (! isset($portalResources[$resource])) {
            return false;
        }
        if (! in_array($action, $portalResources[$resource], true)) {
            return false;
        }
        $perms = $this->permissions ?? [];
        if (! is_array($perms)) {
            return false;
        }
        $allowed = $perms[$resource] ?? [];

        return is_array($allowed) && in_array($action, $allowed, true);
    }
}
