<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Profile') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Staff account, password, and sign-out options.') }}</p>
        </div>
    </x-slot>

    @php
        $u = auth('tenant')->user();
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-5">
        <section class="rounded-xl border border-teal-100 bg-teal-50/50 px-4 py-4 shadow-sm sm:px-5 sm:py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between min-w-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-teal-600 text-lg font-semibold text-white">
                        {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ $u->name }}</p>
                        <p class="truncate text-xs text-gray-600">{{ $u->email }}</p>
                        <p class="mt-1 text-[11px] font-medium uppercase tracking-wide text-teal-800">{{ $u->role === 'admin' ? __('Resort admin') : __('Staff') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ tenant_url('dashboard') }}" class="inline-flex items-center rounded-lg border border-white/80 bg-white/80 px-3 py-2 text-xs font-medium text-teal-900 hover:bg-white">{{ __('Dashboard') }}</a>
                    <a href="{{ tenant_url('settings') }}" class="inline-flex items-center rounded-lg border border-white/80 bg-white/80 px-3 py-2 text-xs font-medium text-teal-900 hover:bg-white">{{ __('Settings') }}</a>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 min-w-0">
            <div class="rounded-xl border border-gray-200/80 bg-white p-4 sm:p-6 shadow-sm min-w-0">
                <div class="max-w-none">
                    @include('Tenant.profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-4 sm:p-6 shadow-sm min-w-0">
                <div class="max-w-none">
                    @include('Tenant.profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white p-4 sm:p-6 shadow-sm min-w-0">
            <div class="max-w-2xl">
                @include('Tenant.profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-tenant::app-layout>
