<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantRbacRole;
use App\Models\TenantModel\Tenant as TenantStaffUser;
use App\Models\TenantUserModel\RegularUser;
use Illuminate\Support\Facades\Schema;

class TenantRbacService
{
    /**
     * @return array<string, list<string>>
     */
    public function staffResourceDefinitions(): array
    {
        return config('tenant_rbac.staff_resources', []);
    }

    /**
     * @return array<string, list<string>>
     */
    public function customerResourceDefinitions(): array
    {
        return config('tenant_rbac.customer_resources', []);
    }

    public function staffCan(TenantStaffUser $user, string $resource, string $action): bool
    {
        if ($user->role === 'admin') {
            return $this->isValidStaffAction($resource, $action);
        }

        if ($user->role !== 'staff') {
            return false;
        }

        $defs = $this->staffResourceDefinitions();
        if (! $this->isValidStaffAction($resource, $action)) {
            return false;
        }

        if ($user->tenant_rbac_role_id === null) {
            return $this->legacyStaffCan($resource, $action);
        }

        $role = $user->relationLoaded('tenantRbacRole')
            ? $user->getRelation('tenantRbacRole')
            : $user->tenantRbacRole()->first();

        if (! $role instanceof TenantRbacRole || $role->kind !== TenantRbacRole::KIND_STAFF) {
            return $this->legacyStaffCan($resource, $action);
        }

        return $role->allows($resource, $action, $defs);
    }

    public function customerCan(RegularUser $user, string $resource, string $action): bool
    {
        $defs = $this->customerResourceDefinitions();
        if (! isset($defs[$resource]) || ! in_array($action, $defs[$resource], true)) {
            return false;
        }

        if ($user->tenant_rbac_role_id === null) {
            return true;
        }

        $role = $user->relationLoaded('tenantRbacRole')
            ? $user->getRelation('tenantRbacRole')
            : $user->tenantRbacRole()->first();

        if (! $role instanceof TenantRbacRole || $role->kind !== TenantRbacRole::KIND_CUSTOMER) {
            return true;
        }

        return $role->allowsPortal($resource, $action, $defs);
    }

    public function legacyStaffCan(string $resource, string $action): bool
    {
        $legacy = config('tenant_rbac.legacy_staff_permissions', []);
        $allowed = $legacy[$resource] ?? [];

        return is_array($allowed) && in_array($action, $allowed, true);
    }

    public function isValidStaffAction(string $resource, string $action): bool
    {
        $defs = $this->staffResourceDefinitions();

        return isset($defs[$resource]) && in_array($action, $defs[$resource], true);
    }

    /**
     * @return array<string, list<string>>
     */
    public function allStaffPermissionsMatrix(): array
    {
        $matrix = [];
        foreach ($this->staffResourceDefinitions() as $res => $actions) {
            $matrix[$res] = $actions;
        }

        return $matrix;
    }

    /**
     * @return array<string, list<string>>
     */
    public function allCustomerPermissionsMatrix(): array
    {
        $matrix = [];
        foreach ($this->customerResourceDefinitions() as $res => $actions) {
            $matrix[$res] = $actions;
        }

        return $matrix;
    }

    public function rbacTablesReady(): bool
    {
        try {
            return Schema::connection('tenant')->hasTable('tenant_rbac_roles');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Seed default staff and customer roles. Idempotent by slug + kind.
     */
    public function initializeDefaultRoles(?int $updatedByTenantUserId = null): void
    {
        $customerFull = $this->allCustomerPermissionsMatrix();

        $staffManager = config('tenant_rbac.legacy_staff_permissions', []);
        if ($staffManager === []) {
            $staffManager = $this->allStaffPermissionsMatrix();
        }

        $staffReception = [
            'dashboard' => ['read'],
            'rooms' => ['read'],
            'bookings' => ['read', 'confirm', 'cancel'],
            'reports' => ['read'],
            'branding' => [],
            'staff' => [],
            'domains' => [],
            'settings' => ['read'],
            'activity' => ['read'],
            'payment' => [],
            'rbac' => [],
            'guests' => [],
        ];

        $customerLimited = [
            'portal' => ['read'],
            'portal_profile' => ['read', 'update'],
        ];

        $sets = [
            [TenantRbacRole::KIND_STAFF, 'manager', 'Manager', 'Runs day-to-day operations without RBAC or guest-role management.', $staffManager, true],
            [TenantRbacRole::KIND_STAFF, 'reception', 'Reception', 'Handles bookings and guest contact; read-only rooms.', $staffReception, true],
            [TenantRbacRole::KIND_CUSTOMER, 'standard', 'Standard guest', 'Full guest portal: bookings and profile.', $customerFull, true],
            [TenantRbacRole::KIND_CUSTOMER, 'limited', 'Limited guest', 'View bookings only; profile updates allowed.', $customerLimited, true],
        ];

        foreach ($sets as [$kind, $slug, $name, $description, $permissions, $isSystem]) {
            TenantRbacRole::query()->updateOrCreate(
                ['kind' => $kind, 'slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'permissions' => $permissions,
                    'is_system' => $isSystem,
                    'updated_by_tenant_user_id' => $updatedByTenantUserId,
                ]
            );
        }
    }
}
