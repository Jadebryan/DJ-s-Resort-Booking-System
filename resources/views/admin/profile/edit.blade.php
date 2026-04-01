<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Admin profile') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Manage your account details, password, and access.') }}</p>
        </div>
    </x-slot>

    @php
        $admin = auth('admin')->user();
    @endphp
    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1 text-left">
        <section class="rounded-xl border border-indigo-100 bg-indigo-50/50 px-4 py-4 shadow-sm sm:px-5 sm:py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between min-w-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-lg font-semibold text-white">
                        {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ $admin->name }}</p>
                        <p class="truncate text-xs text-gray-600">{{ $admin->email }}</p>
                        <p class="mt-1 text-[11px] text-gray-500">{{ __('Superadmin account') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-lg border border-white/80 bg-white/80 px-3 py-2 text-xs font-medium text-indigo-900 hover:bg-white">{{ __('Dashboard') }}</a>
                    <a href="{{ route('admin.settings') }}" class="inline-flex items-center rounded-lg border border-white/80 bg-white/80 px-3 py-2 text-xs font-medium text-indigo-900 hover:bg-white">{{ __('Settings') }}</a>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 min-w-0">
            <div class="p-4 sm:p-6 bg-white shadow-sm rounded-xl border border-gray-200/80 min-w-0">
                <div class="max-w-none">
                    @include('admin.profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-6 bg-white shadow-sm rounded-xl border border-gray-200/80 min-w-0">
                <div class="max-w-none">
                    @include('admin.profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 bg-white shadow-sm rounded-xl border border-gray-200/80 min-w-0">
            <div class="max-w-2xl">
                @include('admin.profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-admin::app-layout>
