<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Users') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('People who registered on your resort site and can sign in to manage their bookings.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-6xl space-y-4">
        @if (session('success'))
            <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        @if ($customerRoles->isEmpty() && tenant_rbac_ready())
            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
                {{ __('Under Access control, initialize default roles if you want to assign Standard or Limited portal access to specific users.') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Email') }}</th>
                        <th class="hidden px-4 py-3 sm:table-cell">{{ __('Joined') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Bookings') }}</th>
                        <th class="px-4 py-3 min-w-[11rem]">{{ __('Portal role') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50/80">
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
                            <td class="px-4 py-3 text-gray-700">
                                @if ($canAssignRoles && $customerRoles->isNotEmpty())
                                    <form method="POST" action="{{ route('tenant.users.update-role', ['guest' => $user->id]) }}" class="inline-flex max-w-full">
                                        @csrf
                                        @method('PATCH')
                                        <select name="tenant_rbac_role_id" class="max-w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-teal-500 focus:ring-teal-500" onchange="this.form.requestSubmit()">
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
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-600">
                                {{ __('No registered users yet. They appear here after they create an account from your site’s guest login or registration page.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-tenant::app-layout>
