<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Users') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('People who registered on your resort site and can sign in to manage their bookings.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-6xl space-y-4" x-data="{
        listGridMode: 'list',
        listGridStorageKey: 'mtrbs.tenant.users.index.view',
        userFilter: '',
        portalRoleFilter: 'all',
        init() {
            try {
                const v = localStorage.getItem(this.listGridStorageKey);
                if (v === 'grid' || v === 'list') {
                    this.listGridMode = v;
                }
            } catch (e) {}
        },
        setListGridMode(m) {
            this.listGridMode = m;
            try {
                localStorage.setItem(this.listGridStorageKey, m);
            } catch (e) {}
        },
        userRowVisible(el) {
            const q = (this.userFilter || '').toLowerCase().trim();
            const blob = (el.dataset.userSearch || '').toLowerCase();
            if (q && !blob.includes(q)) return false;
            if (this.portalRoleFilter === 'all') return true;
            const rid = el.dataset.userRoleId ?? '';
            if (this.portalRoleFilter === 'default') return rid === '';
            return String(rid) === String(this.portalRoleFilter);
        },
    }">
        @if ($customerRoles->isEmpty() && tenant_rbac_ready())
            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
                {{ __('Under Access control, initialize default roles if you want to assign Standard or Limited portal access to specific users.') }}
            </div>
        @endif

        @if ($users->isEmpty())
            <div class="rounded-xl border border-gray-200/80 bg-white p-10 text-center text-sm text-gray-600 shadow-sm">
                {{ __('No registered users yet. They appear here after they create an account from your site’s guest login or registration page.') }}
            </div>
        @else
            <div class="w-full min-w-0">
                <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between lg:gap-x-4 lg:gap-y-3">
                    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-3">
                        <div class="w-full min-w-0 sm:min-w-[min(100%,18rem)] sm:flex-1 lg:max-w-md">
                            <label for="users-index-search" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Search') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                                </span>
                                <input id="users-index-search" type="search" x-model="userFilter" autocomplete="off"
                                       placeholder="{{ __('Name, email…') }}"
                                       class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="w-full min-w-[12rem] sm:w-52">
                            <label for="users-filter-portal-role" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Portal role') }}</label>
                            <div class="relative">
                                <select id="users-filter-portal-role" x-model="portalRoleFilter"
                                        class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-9 text-sm text-gray-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                    <option value="all">{{ __('All roles') }}</option>
                                    <option value="default">{{ __('Default (full portal)') }}</option>
                                    @foreach ($customerRoles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col gap-1 sm:ml-auto">
                        <span class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Actions') }}</span>
                        <div class="flex h-10 items-center">
                            <x-list-grid-toggle-buttons accent="teal" />
                        </div>
                    </div>
                </div>

                <div class="w-full min-w-0">
                    <div x-show="listGridMode === 'list'" x-cloak class="w-full min-w-0">
                    <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Name') }}</th>
                                    <th class="px-4 py-3">{{ __('Email') }}</th>
                                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('Joined') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Bookings') }}</th>
                                    <th class="px-4 py-3 min-w-[9.5rem]">{{ __('Portal role') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($users as $user)
                                    @php
                                        $userSearchBlob = strtolower(implode(' ', array_filter([
                                            $user->name,
                                            $user->email,
                                            $user->tenantRbacRole?->name,
                                            __('Default (full portal)'),
                                            (string) $user->bookings_count,
                                            $user->created_at?->timezone(config('app.timezone'))->format('M j, Y'),
                                        ], fn ($v) => $v !== null && $v !== '')));
                                    @endphp
                                    <tr class="hover:bg-gray-50/80"
                                        data-user-search="{{ e($userSearchBlob) }}"
                                        data-user-role-id="{{ $user->tenant_rbac_role_id ?? '' }}"
                                        x-show="userRowVisible($el)">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">
                                            <span class="break-all">{{ $user->email }}</span>
                                        </td>
                                        <td class="hidden px-4 py-3 text-gray-600 sm:table-cell whitespace-nowrap">
                                            {{ $user->created_at?->timezone(config('app.timezone'))->format('M j, Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums text-gray-800">
                                            {{ number_format($user->bookings_count) }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 min-w-[9.5rem]">
                                            @if ($canAssignRoles && $customerRoles->isNotEmpty())
                                                <form method="POST" action="{{ route('tenant.users.update-role', ['guest' => $user->id]) }}" class="block w-full">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="tenant_rbac_role_id" class="w-full min-w-[8.75rem] rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-teal-500 focus:ring-teal-500" onchange="this.form.requestSubmit()">
                                                        <option value="">{{ __('Default (full portal)') }}</option>
                                                        @foreach ($customerRoles as $role)
                                                            <option value="{{ $role->id }}" @selected($user->tenant_rbac_role_id === $role->id)>{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @else
                                                {{ $user->tenantRbacRole?->name ?? __('Default (full portal)') }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    </div>

                    <div x-show="listGridMode === 'grid'" x-cloak class="w-full min-w-0">
                    <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
                        @foreach ($users as $user)
                            @php
                                $userSearchBlobGrid = strtolower(implode(' ', array_filter([
                                    $user->name,
                                    $user->email,
                                    $user->tenantRbacRole?->name,
                                    __('Default (full portal)'),
                                    (string) $user->bookings_count,
                                    $user->created_at?->timezone(config('app.timezone'))->format('M j, Y'),
                                ], fn ($v) => $v !== null && $v !== '')));
                            @endphp
                            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm"
                                 data-user-search="{{ e($userSearchBlobGrid) }}"
                                 data-user-role-id="{{ $user->tenant_rbac_role_id ?? '' }}"
                                 x-show="userRowVisible($el)">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-gray-900 truncate" title="{{ $user->name }}">{{ $user->name }}</h3>
                                        <p class="mt-1 text-xs text-gray-500 break-all">{{ $user->email }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-teal-100 px-2 py-0.5 text-xs font-medium text-teal-800 tabular-nums">{{ number_format($user->bookings_count) }} {{ __('bookings') }}</span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">{{ __('Joined') }} {{ $user->created_at?->timezone(config('app.timezone'))->format('M j, Y') ?? '—' }}</p>
                                <div class="mt-4 border-t border-gray-100 pt-3">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500 mb-1">{{ __('Portal role') }}</p>
                                    @if ($canAssignRoles && $customerRoles->isNotEmpty())
                                        <form method="POST" action="{{ route('tenant.users.update-role', ['guest' => $user->id]) }}" class="block w-full">
                                            @csrf
                                            @method('PATCH')
                                            <select name="tenant_rbac_role_id" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-teal-500 focus:ring-teal-500" onchange="this.form.requestSubmit()">
                                                <option value="">{{ __('Default (full portal)') }}</option>
                                                @foreach ($customerRoles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->tenant_rbac_role_id === $role->id)>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <p class="text-sm text-gray-700">{{ $user->tenantRbacRole?->name ?? __('Default (full portal)') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-tenant::app-layout>
