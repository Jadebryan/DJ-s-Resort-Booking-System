<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Support\TenantStaffPermissionDeniedResponse;
use App\Models\ActivityLog;
use App\Models\TenantRbacRole;
use App\Models\TenantModel\Tenant as TenantUser;
use App\Services\TenantRbacService;
use App\Support\InputRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    /**
     * Route model binding is unreliable for `{member}` on tenant domain routes; resolve explicitly.
     */
    protected function memberFromRoute(Request $request): TenantUser
    {
        $value = $request->route('member');

        if ($value instanceof TenantUser) {
            return $value;
        }

        return TenantUser::on('tenant')->findOrFail((int) $value);
    }

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

    protected function redirectForbidden(Request $request): Response
    {
        return TenantStaffPermissionDeniedResponse::make(
            $request,
            __('Staff management'),
            __('You don’t have permission to manage staff accounts. Only the resort owner, or team members with the right staff permissions, can use this area.')
        );
    }

    public function index(Request $request): View|RedirectResponse|Response
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
            'update_url' => tenant_url('staff/'.$u->id),
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

    public function create(Request $request): View|RedirectResponse|Response
    {
        if (! $this->ensureStaffPermission($request, 'create')) {
            return $this->redirectForbidden($request);
        }
        return view('Tenant.staff.create');
    }

    public function store(Request $request): RedirectResponse|Response
    {
        if (! $this->ensureStaffPermission($request, 'create')) {
            return $this->redirectForbidden($request);
        }
        try {
            $validated = $request->validate(array_merge([
                'name' => InputRules::personName(255, true),
                'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:254', Rule::unique(TenantUser::class, 'email')],
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

    public function edit(Request $request): View|RedirectResponse|Response
    {
        if (! $this->ensureStaffPermission($request, 'read')) {
            return $this->redirectForbidden($request);
        }
        $member = $this->memberFromRoute($request);

        return view('Tenant.staff.edit', compact('member'));
    }

    public function update(Request $request): RedirectResponse|Response
    {
        if (! $this->ensureStaffPermission($request, 'update')) {
            return $this->redirectForbidden($request);
        }
        $member = $this->memberFromRoute($request);
        $rules = array_merge([
            'name' => InputRules::personName(255, true),
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:254', Rule::unique(TenantUser::class, 'email')->ignore($member->id)],
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

    public function destroy(Request $request): RedirectResponse|Response
    {
        if (! $this->ensureStaffPermission($request, 'delete')) {
            return $this->redirectForbidden($request);
        }
        $member = $this->memberFromRoute($request);
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
