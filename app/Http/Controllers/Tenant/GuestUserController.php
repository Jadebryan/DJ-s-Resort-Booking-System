<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Support\TenantStaffPermissionDeniedResponse;
use App\Models\ActivityLog;
use App\Models\TenantRbacRole;
use App\Models\TenantUserModel\RegularUser;
use App\Services\TenantRbacService;
use App\Support\TenantPlanFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuestUserController extends Controller
{
    public function __construct(
        private readonly TenantRbacService $rbac
    ) {}

    protected function canManageGuests(Request $request): bool
    {
        $user = $request->user('tenant');
        if (! $user instanceof \App\Models\TenantModel\Tenant) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }

        return $this->rbac->staffCan($user, 'guests', 'read');
    }

    protected function canAssignGuestRoles(Request $request): bool
    {
        $user = $request->user('tenant');
        if (! $user instanceof \App\Models\TenantModel\Tenant) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }

        return $this->rbac->staffCan($user, 'guests', 'update');
    }

    public function index(Request $request): View|RedirectResponse|Response
    {
        if (! TenantPlanFeatures::hasRequestFeature($request, 'guest_management')) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', __('Guest management is not enabled in your current subscription.'));
        }

        if (! $this->canManageGuests($request)) {
            return TenantStaffPermissionDeniedResponse::make(
                $request,
                __('Guest users'),
                __('You don’t have permission to view registered guest accounts. Ask the resort owner to grant “Guest / portal users” access for your staff role if you need this.')
            );
        }

        $users = RegularUser::query()
            ->orderByDesc('created_at')
            ->with('tenantRbacRole:id,name,slug')
            ->withCount('bookings')
            ->get();

        $roles = $this->rbac->rbacTablesReady()
            ? TenantRbacRole::query()->where('kind', TenantRbacRole::KIND_CUSTOMER)->orderBy('name')->get()
            : collect();

        return view('Tenant.users.index', [
            'users' => $users,
            'customerRoles' => $roles,
            'canAssignRoles' => $this->canAssignGuestRoles($request),
        ]);
    }

    public function updateRole(Request $request, RegularUser $guest): RedirectResponse|Response
    {
        if (! TenantPlanFeatures::hasRequestFeature($request, 'guest_management')) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', __('Guest management is not enabled in your current subscription.'));
        }

        if (! $this->canAssignGuestRoles($request)) {
            return TenantStaffPermissionDeniedResponse::make(
                $request,
                __('Guest roles'),
                __('You don’t have permission to change guest portal roles. Ask the resort owner to grant update access for “Guest / portal users” if you need this.')
            );
        }

        if (! $this->rbac->rbacTablesReady()) {
            return redirect()->route('tenant.users.index')->with('error', __('Initialize access control first.'));
        }

        try {
            $validated = $request->validate([
                'tenant_rbac_role_id' => ['nullable', 'integer', Rule::exists('tenant_rbac_roles', 'id')->where('kind', TenantRbacRole::KIND_CUSTOMER)],
            ]);
        } catch (ValidationException $e) {
            return redirect()
                ->route('tenant.users.index')
                ->withErrors($e->errors());
        }

        $guest->tenant_rbac_role_id = $validated['tenant_rbac_role_id'] ?? null;
        $guest->save();

        if (class_exists(ActivityLog::class) && Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('guest.role_updated', 'User "'.$guest->name.'" portal role was updated.');
        }

        return redirect()
            ->route('tenant.users.index')
            ->with('success', __('Portal role updated.'));
    }
}
