<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TenantRbacRole;
use App\Models\TenantModel\Tenant as TenantStaffUser;
use App\Services\TenantRbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TenantRbacController extends Controller
{
    public function __construct(
        private readonly TenantRbacService $rbac
    ) {}

    protected function ensureResortOwner(Request $request): bool
    {
        $user = $request->user('tenant');

        return $user instanceof TenantStaffUser && $user->role === 'admin';
    }

    protected function redirectForbidden(Request $request): RedirectResponse
    {
        return redirect()
            ->route('tenant.dashboard')
            ->with('error', __('Only the resort owner can manage access control.'));
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (! $this->ensureResortOwner($request)) {
            return $this->redirectForbidden($request);
        }

        if (! $this->rbac->rbacTablesReady()) {
            return view('Tenant.rbac.pending-migration');
        }

        $staffRoles = TenantRbacRole::query()
            ->where('kind', TenantRbacRole::KIND_STAFF)
            ->with('updatedByStaff:id,name')
            ->orderBy('name')
            ->get();

        $customerRoles = TenantRbacRole::query()
            ->where('kind', TenantRbacRole::KIND_CUSTOMER)
            ->with('updatedByStaff:id,name')
            ->orderBy('name')
            ->get();

        $staffDefs = $this->rbac->staffResourceDefinitions();
        $customerDefs = $this->rbac->customerResourceDefinitions();

        $staffResourceCount = count($staffDefs);
        $staffActionCount = array_sum(array_map('count', $staffDefs));
        $customerResourceCount = count($customerDefs);
        $customerActionCount = array_sum(array_map('count', $customerDefs));

        $rolesForJs = $staffRoles->merge($customerRoles)->map(fn (TenantRbacRole $r) => [
            'id' => $r->id,
            'kind' => $r->kind,
            'name' => $r->name,
            'slug' => $r->slug,
            'description' => $r->description,
            'permissions' => $r->permissions ?? [],
            'is_system' => $r->is_system,
            'updated_at' => $r->updated_at?->toIso8601String(),
            'updated_by' => $r->updatedByStaff?->name,
            'update_url' => route('tenant.rbac.update', ['rbacRole' => $r->id]),
        ])->values()->all();

        return view('Tenant.rbac.index', [
            'staffRoles' => $staffRoles,
            'customerRoles' => $customerRoles,
            'staffDefs' => $staffDefs,
            'customerDefs' => $customerDefs,
            'staffResourceCount' => $staffResourceCount,
            'staffActionCount' => $staffActionCount,
            'customerResourceCount' => $customerResourceCount,
            'customerActionCount' => $customerActionCount,
            'rolesForJs' => $rolesForJs,
            'openEditRoleId' => session('openEditRoleId'),
        ]);
    }

    public function initialize(Request $request): RedirectResponse
    {
        if (! $this->ensureResortOwner($request)) {
            return $this->redirectForbidden($request);
        }
        if (! $this->rbac->rbacTablesReady()) {
            return redirect()->route('tenant.rbac.index')->with('error', __('Run tenant migrations first.'));
        }

        /** @var TenantStaffUser $actor */
        $actor = $request->user('tenant');
        $this->rbac->initializeDefaultRoles($actor->id);

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('rbac.initialized', 'Default staff and guest roles were initialized.');
        }

        return redirect()
            ->route('tenant.rbac.index')
            ->with('success', __('Default roles are ready. Assign staff roles under Staff & Accounts.'));
    }

    public function update(Request $request, TenantRbacRole $rbacRole): RedirectResponse
    {
        if (! $this->ensureResortOwner($request)) {
            return $this->redirectForbidden($request);
        }
        if (! $this->rbac->rbacTablesReady()) {
            return redirect()->route('tenant.rbac.index')->with('error', __('Run tenant migrations first.'));
        }

        $defs = $rbacRole->kind === TenantRbacRole::KIND_CUSTOMER
            ? $this->rbac->customerResourceDefinitions()
            : $this->rbac->staffResourceDefinitions();

        $incoming = $request->input('permissions', []);
        if (! is_array($incoming)) {
            $incoming = [];
        }
        $sanitized = [];
        foreach ($defs as $resource => $validActions) {
            $picked = $incoming[$resource] ?? [];
            if (! is_array($picked)) {
                continue;
            }
            $sanitized[$resource] = array_values(array_intersect($validActions, $picked));
        }

        /** @var TenantStaffUser $actor */
        $actor = $request->user('tenant');
        $rbacRole->permissions = $sanitized;
        $rbacRole->updated_by_tenant_user_id = $actor->id;
        $rbacRole->save();

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('rbac.role_updated', 'Role "'.$rbacRole->name.'" permissions were updated.');
        }

        return redirect()
            ->route('tenant.rbac.index')
            ->with('success', __('Role permissions updated.'));
    }
}
