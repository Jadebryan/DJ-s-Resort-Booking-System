<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TenantRbacRole;
use App\Models\TenantModel\Tenant as TenantUser;
use App\Services\TenantRbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    protected function staffRbacRoleRules(string $roleValue): array
    {
        $svc = app(TenantRbacService::class);
        if ($roleValue !== 'staff' || ! $svc->rbacTablesReady()) {
            return ['tenant_rbac_role_id' => ['nullable', 'integer']];
        }

        $hasRoles = TenantRbacRole::query()->where('kind', TenantRbacRole::KIND_STAFF)->exists();
        if (! $hasRoles) {
            return ['tenant_rbac_role_id' => ['nullable', 'integer']];
        }

        return [
            'tenant_rbac_role_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! TenantRbacRole::query()->where('id', $value)->where('kind', TenantRbacRole::KIND_STAFF)->exists()) {
                        $fail(__('Select a valid permission set for this staff member.'));
                    }
                },
            ],
        ];
    }

    protected function ensureStaffPermission(Request $request, string $action): bool
    {
        $user = $request->user('tenant');
        if (! $user instanceof TenantUser) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }

        return app(TenantRbacService::class)->staffCan($user, 'staff', $action);
    }

    protected function redirectForbidden(Request $request): RedirectResponse
    {
        return redirect()
            ->route('tenant.dashboard')
            ->with('error', __('You do not have permission to manage staff.'));
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (! $this->ensureStaffPermission($request, 'read')) {
            return $this->redirectForbidden($request);
        }
        $users = TenantUser::query()
            ->orderBy('role')
            ->orderBy('name')
            ->with('tenantRbacRole:id,name')
            ->get();
        $usersForJs = $users->map(fn (TenantUser $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'tenant_rbac_role_id' => $u->tenant_rbac_role_id,
            'update_url' => route('tenant.staff.update', ['member' => $u]),
        ])->values()->all();
        $staffRbacRoles = app(TenantRbacService::class)->rbacTablesReady()
            ? TenantRbacRole::query()->where('kind', TenantRbacRole::KIND_STAFF)->orderBy('name')->get()
            : collect();

        return view('Tenant.staff.index', [
            'users' => $users,
            'usersForJs' => $usersForJs,
            'staffRbacRoles' => $staffRbacRoles,
            'staffRbacRolesForJs' => $staffRbacRoles->map(fn (TenantRbacRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
            ])->values()->all(),
            'openModal' => session('openModal'),
            'editMemberId' => session('editMemberId'),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->ensureStaffPermission($request, 'create')) {
            return $this->redirectForbidden($request);
        }
        return view('Tenant.staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->ensureStaffPermission($request, 'create')) {
            return $this->redirectForbidden($request);
        }
        try {
            $validated = $request->validate(array_merge([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('tenant_users', 'email')->connection('tenant')],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['required', 'in:admin,staff'],
            ], $this->staffRbacRoleRules((string) $request->input('role'))));
        } catch (ValidationException $e) {
            return redirect()
                ->route('tenant.staff.index')
                ->with('openModal', 'create')
                ->withErrors($e->errors())
                ->withInput();
        }

        /** @var TenantUser $actor */
        $actor = $request->user('tenant');
        if ($actor->role !== 'admin' && ($validated['role'] ?? '') === 'admin') {
            return redirect()
                ->route('tenant.staff.index')
                ->with('openModal', 'create')
                ->withErrors(['role' => __('Only the resort owner can create owner accounts.')])
                ->withInput();
        }

        TenantUser::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'tenant_rbac_role_id' => $validated['role'] === 'staff'
                ? ($validated['tenant_rbac_role_id'] ?? null)
                : null,
        ]);
        if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('staff.added', 'Staff "' . $validated['name'] . '" (' . $validated['email'] . ') added with role ' . $validated['role'] . '.');
        }
        return redirect()
            ->route('tenant.staff.index')
            ->with('success', 'Staff member added.');
    }

    public function edit(Request $request, TenantUser $member): View|RedirectResponse
    {
        if (! $this->ensureStaffPermission($request, 'read')) {
            return $this->redirectForbidden($request);
        }
        return view('Tenant.staff.edit', compact('member'));
    }

    public function update(Request $request, TenantUser $member): RedirectResponse
    {
        if (! $this->ensureStaffPermission($request, 'update')) {
            return $this->redirectForbidden($request);
        }
        $rules = array_merge([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('tenant_users', 'email')->connection('tenant')->ignore($member->id)],
            'role' => ['required', 'in:admin,staff'],
        ], $this->staffRbacRoleRules((string) $request->input('role')));
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }
        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return redirect()
                ->route('tenant.staff.index')
                ->with('openModal', 'edit')
                ->with('editMemberId', $member->id)
                ->withErrors($e->errors())
                ->withInput();
        }

        /** @var TenantUser $actor */
        $actor = $request->user('tenant');
        if ($actor->role !== 'admin' && ($validated['role'] ?? '') === 'admin') {
            return redirect()
                ->route('tenant.staff.index')
                ->with('openModal', 'edit')
                ->with('editMemberId', $member->id)
                ->withErrors(['role' => __('Only the resort owner can assign owner accounts.')])
                ->withInput();
        }

        if ($actor->role !== 'admin' && $member->role === 'admin') {
            return redirect()
                ->route('tenant.staff.index')
                ->with('error', __('Only the resort owner can change owner accounts.'));
        }

        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->role = $validated['role'];
        $member->tenant_rbac_role_id = $validated['role'] === 'staff'
            ? ($validated['tenant_rbac_role_id'] ?? null)
            : null;
        if (!empty($validated['password'] ?? null)) {
            $member->password = Hash::make($validated['password']);
        }
        $member->save();
        if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('staff.updated', 'Staff "' . $member->name . '" updated.');
        }
        return redirect()
            ->route('tenant.staff.index')
            ->with('success', 'Staff member updated.');
    }

    public function destroy(Request $request, TenantUser $member): RedirectResponse
    {
        if (! $this->ensureStaffPermission($request, 'delete')) {
            return $this->redirectForbidden($request);
        }
        /** @var TenantUser $actor */
        $actor = $request->user('tenant');
        if ($actor->role !== 'admin' && $member->role === 'admin') {
            return redirect()
                ->route('tenant.staff.index')
                ->with('error', __('Only the resort owner can remove owner accounts.'));
        }
        if ($member->id === $request->user('tenant')->id) {
            return redirect()
                ->route('tenant.staff.index')
                ->with('error', 'You cannot remove yourself.');
        }
        $name = $member->name;
        $member->delete();
        if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('staff.removed', 'Staff "' . $name . '" removed.');
        }
        return redirect()
            ->route('tenant.staff.index')
            ->with('success', 'Staff member removed.');
    }
}
