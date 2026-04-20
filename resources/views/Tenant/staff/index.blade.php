<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Staff & Accounts') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Who can sign in to manage this resort.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6" x-data="{
        users: @js($usersForJs ?? []),
        staffRoles: @js($staffRbacRolesForJs ?? []),
        createRole: @js(old('role', 'staff')),
        openCreate: @js(session('openModal') === 'create'),
        openEdit: @js(session('openModal') === 'edit'),
        editMemberId: @js(session('editMemberId')),
        currentUser: null,
        editForm: { name: '', email: '', role: 'staff', tenant_rbac_role_id: '', password: '', password_confirmation: '' },
        listGridMode: 'grid',
        listGridStorageKey: 'mtrbs.tenant.staff.index.view',
        staffFilter: '',
        roleFilter: 'all',
        init() {
            try {
                const v = localStorage.getItem(this.listGridStorageKey);
                if (v === 'grid' || v === 'list') {
                    this.listGridMode = v;
                }
            } catch (e) {}
            if (this.openEdit && this.editMemberId && this.users.length) {
                const u = this.users.find(user => user.id == this.editMemberId);
                if (u) {
                    this.currentUser = u;
                    this.editForm = {
                        name: @js(old('name')),
                        email: @js(old('email')),
                        role: @js(old('role', 'staff')),
                        tenant_rbac_role_id: @js(old('tenant_rbac_role_id')),
                        password: '',
                        password_confirmation: ''
                    };
                    if (this.editForm.name === null) {
                        this.editForm = {
                            name: u.name,
                            email: u.email,
                            role: u.role,
                            tenant_rbac_role_id: u.tenant_rbac_role_id != null ? String(u.tenant_rbac_role_id) : '',
                            password: '',
                            password_confirmation: ''
                        };
                    }
                }
            }
        },
        setListGridMode(m) {
            this.listGridMode = m;
            try {
                localStorage.setItem(this.listGridStorageKey, m);
            } catch (e) {}
        },
        staffRowVisible(el) {
            const q = (this.staffFilter || '').toLowerCase().trim();
            const blob = (el.dataset.staffSearch || '').toLowerCase();
            if (q && !blob.includes(q)) return false;
            if (this.roleFilter !== 'all' && (el.dataset.staffRole || '') !== this.roleFilter) return false;
            return true;
        },
        openCreateModal() { this.openCreate = true; this.createRole = 'staff'; },
        closeCreateModal() { this.openCreate = false; },
        openEditModal(id) {
            const user = this.users.find(u => u.id == id);
            if (!user) return;
            this.currentUser = user;
            this.editForm = {
                name: user.name,
                email: user.email,
                role: user.role,
                tenant_rbac_role_id: user.tenant_rbac_role_id != null ? String(user.tenant_rbac_role_id) : '',
                password: '',
                password_confirmation: ''
            };
            this.openEdit = true;
        },
        closeEditModal() { this.openEdit = false; this.currentUser = null; }
    }">
        @if($users->isEmpty())
            <div class="rounded-xl border border-gray-200/80 bg-white p-8 text-center shadow-sm">
                <p class="text-gray-600">No staff accounts yet. Add team members who can log in to manage bookings and rooms.</p>
                @if(tenant_staff_can('staff', 'create'))
                <button type="button" @click="openCreateModal()" class="mt-3 font-medium text-teal-600 hover:text-teal-700 hover:underline">
                    Add your first staff member
                </button>
                @endif
            </div>
        @else
            <div class="w-full min-w-0">
                <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between lg:gap-x-4 lg:gap-y-3">
                    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-3">
                        <div class="w-full min-w-0 sm:min-w-[min(100%,18rem)] sm:flex-1 lg:max-w-md">
                            <label for="staff-index-search" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Search') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                                </span>
                                <input id="staff-index-search" type="search" x-model="staffFilter" autocomplete="off"
                                       placeholder="{{ __('Name, email, role…') }}"
                                       class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="w-full min-w-[11rem] sm:w-44">
                            <label for="staff-filter-role" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Role') }}</label>
                            <div class="relative">
                                <select id="staff-filter-role" x-model="roleFilter"
                                        class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-9 text-sm text-gray-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                    <option value="all">{{ __('All roles') }}</option>
                                    <option value="admin">{{ __('Owner / Admin') }}</option>
                                    <option value="staff">{{ __('Staff') }}</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col gap-1 sm:ml-auto">
                        <span class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Actions') }}</span>
                        <div class="flex h-10 flex-wrap items-center gap-2 sm:flex-nowrap">
                            <x-list-grid-toggle-buttons accent="teal" />
                            @if(tenant_staff_can('staff', 'create'))
                                <button type="button" @click="openCreateModal()"
                                        class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Add staff') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="w-full min-w-0">
                    <div x-show="listGridMode === 'list'" x-cloak class="w-full min-w-0">
                    <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
                        <table class="min-w-[640px] w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Email') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Role') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($users as $user)
                                    @php
                                        $roleLabel = $user->role === 'admin'
                                            ? __('Owner / Admin')
                                            : ($user->tenantRbacRole?->name ?? __('Staff'));
                                        $staffSearchBlob = strtolower(implode(' ', array_filter([
                                            $user->name,
                                            $user->email,
                                            $user->role,
                                            $roleLabel,
                                        ], fn ($v) => $v !== null && $v !== '')));
                                    @endphp
                                    <tr class="hover:bg-gray-50/50"
                                        data-staff-search="{{ e($staffSearchBlob) }}"
                                        data-staff-role="{{ e($user->role) }}"
                                        x-show="staffRowVisible($el)">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-3 text-gray-600 break-all">{{ $user->email }}</td>
                                        <td class="px-4 py-3 text-gray-700">
                                            @if($user->role === 'admin')
                                                {{ __('Owner / Admin') }}
                                            @else
                                                {{ $user->tenantRbacRole?->name ?? __('Staff') }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if(tenant_staff_can('staff', 'update'))
                                                <button type="button" @click="openEditModal({{ $user->id }})" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</button>
                                            @endif
                                            @if($user->id !== auth('tenant')->id() && tenant_staff_can('staff', 'delete'))
                                                <x-confirm-form-button class="inline-block ml-1" :action="tenant_url('staff/' . $user->id)" method="DELETE" :title="__('Remove staff member')" :message="__('Remove this staff member? They will no longer be able to log in.')" :confirm-label="__('Remove')">
                                                    <button type="button" @click="open = true" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50">{{ __('Remove') }}</button>
                                                </x-confirm-form-button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    </div>

                    <div x-show="listGridMode === 'grid'" x-cloak class="w-full min-w-0">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($users as $user)
                    @php
                        $roleLabelGrid = $user->role === 'admin'
                            ? __('Owner / Admin')
                            : ($user->tenantRbacRole?->name ?? __('Staff'));
                        $staffSearchBlobGrid = strtolower(implode(' ', array_filter([
                            $user->name,
                            $user->email,
                            $user->role,
                            $roleLabelGrid,
                        ], fn ($v) => $v !== null && $v !== '')));
                    @endphp
                    <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition hover:border-teal-200 hover:shadow-md"
                         data-staff-search="{{ e($staffSearchBlobGrid) }}"
                         data-staff-role="{{ e($user->role) }}"
                         x-show="staffRowVisible($el)">
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->role === 'admin' ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-600' }}">
                                    @if($user->role === 'admin')
                                        {{ __('Owner / Admin') }}
                                    @else
                                        {{ $user->tenantRbacRole?->name ?? __('Staff') }}
                                    @endif
                                </span>
                            </div>
                            <h3 class="mt-3 truncate font-semibold text-gray-900" title="{{ $user->name }}">{{ $user->name }}</h3>
                            <p class="truncate text-sm text-gray-500" title="{{ $user->email }}">{{ $user->email }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if(tenant_staff_can('staff', 'update'))
                                <button type="button" @click="openEditModal({{ $user->id }})"
                                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:border-teal-200 hover:text-teal-700">
                                    Edit
                                </button>
                                @endif
                                @if($user->id !== auth('tenant')->id())
                                    @if(tenant_staff_can('staff', 'delete'))
                                    <x-confirm-form-button
                                        class="inline-block"
                                        :action="tenant_url('staff/' . $user->id)"
                                        method="DELETE"
                                        :title="__('Remove staff member')"
                                        :message="__('Remove this staff member? They will no longer be able to log in.')"
                                        :confirm-label="__('Remove')">
                                        <button type="button" @click="open = true" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50">
                                            {{ __('Remove') }}
                                        </button>
                                    </x-confirm-form-button>
                                    @endif
                                @else
                                    <span class="px-3 py-1.5 text-sm text-gray-400">(you)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Create modal --}}
    @if(tenant_staff_can('staff', 'create'))
    <div x-show="openCreate" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto px-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm" @click="closeCreateModal()"></div>
        <div class="relative mx-auto max-w-lg rounded-xl bg-white p-6 shadow-xl sm:my-8">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Add staff member</h2>
                <button type="button" @click="closeCreateModal()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ tenant_url('staff') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="create_name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="create_name" name="name" type="text" value="{{ old('name') }}" required
                           {{ \App\Support\InputHtmlAttributes::personName() }}
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="create_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="create_email" name="email" type="email" value="{{ old('email') }}" required
                           {{ \App\Support\InputHtmlAttributes::email() }}
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="create_password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="create_password" name="password" type="password" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="create_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                    <input id="create_password_confirmation" name="password_confirmation" type="password" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div>
                    <label for="create_role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="create_role" name="role" x-model="createRole" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staff — Manage rooms and bookings</option>
                        @if(auth('tenant')->user()->role === 'admin')
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Owner / Admin — Full access</option>
                        @endif
                    </select>
                    @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div x-show="createRole === 'staff' && staffRoles.length" x-cloak class="space-y-1">
                    <label for="create_tenant_rbac_role_id" class="block text-sm font-medium text-gray-700">{{ __('Permission set') }}</label>
                    <select id="create_tenant_rbac_role_id" name="tenant_rbac_role_id"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        <option value="">{{ __('Select…') }}</option>
                        @foreach($staffRbacRoles ?? [] as $sr)
                            <option value="{{ $sr->id }}" @selected((string) old('tenant_rbac_role_id') === (string) $sr->id)>{{ $sr->name }}</option>
                        @endforeach
                    </select>
                    @error('tenant_rbac_role_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeCreateModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Add staff member</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit modal --}}
    @if(tenant_staff_can('staff', 'update'))
    <div x-show="openEdit" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto px-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm" @click="closeEditModal()"></div>
        <div class="relative mx-auto max-w-lg rounded-xl bg-white p-6 shadow-xl sm:my-8" @click.self="closeEditModal()">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Edit staff member</h2>
                <button type="button" @click="closeEditModal()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="currentUser">
                <form :action="currentUser.update_url" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="edit_name" name="name" type="text" x-model="editForm.name" required
                               {{ \App\Support\InputHtmlAttributes::personName() }}
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="edit_email" name="email" type="email" x-model="editForm.email" required
                               {{ \App\Support\InputHtmlAttributes::email() }}
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700">New password (leave blank to keep)</label>
                        <input id="edit_password" name="password" type="password" x-model="editForm.password"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="edit_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm new password</label>
                        <input id="edit_password_confirmation" name="password_confirmation" type="password" x-model="editForm.password_confirmation"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="edit_role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="edit_role" name="role" x-model="editForm.role" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                            <option value="staff">Staff</option>
                            @if(auth('tenant')->user()->role === 'admin')
                                <option value="admin">Owner / Admin</option>
                            @endif
                        </select>
                        @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="editForm.role === 'staff' && staffRoles.length" x-cloak class="space-y-1">
                        <label for="edit_tenant_rbac_role_id" class="block text-sm font-medium text-gray-700">{{ __('Permission set') }}</label>
                        <select id="edit_tenant_rbac_role_id" name="tenant_rbac_role_id" x-model="editForm.tenant_rbac_role_id"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('Select…') }}</option>
                            <template x-for="sr in staffRoles" :key="sr.id">
                                <option :value="sr.id" x-text="sr.name"></option>
                            </template>
                        </select>
                        @error('tenant_rbac_role_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeEditModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Update</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
    @endif
    </div>
</x-tenant::app-layout>
