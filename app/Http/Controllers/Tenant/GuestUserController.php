<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TenantRbacRole;
use App\Models\TenantUserModel\RegularUser;
use App\Services\TenantRbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function index(Request $request): View|RedirectResponse
    {
        if (! $this->canManageGuests($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', __('You do not have permission to view user accounts.'));
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

    public function updateRole(Request $request, RegularUser $guest): RedirectResponse
    {
        if (! $this->canAssignGuestRoles($request)) {
            return redirect()
                ->route('tenant.users.index')
                ->with('error', __('You do not have permission to assign portal roles.'));
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
