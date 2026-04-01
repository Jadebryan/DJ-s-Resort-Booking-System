@php
    $tenant = current_tenant();
    $primary = $tenant?->primary_color ?? '#0ea5e9';
    $secondary = $tenant?->secondary_color ?? '#0369a1';
@endphp
<x-tenant-user::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-[11px] leading-snug text-slate-600 sm:text-xs">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('user.password.confirm') }}" class="space-y-3">
        @csrf

        <div>
            <x-tenant-user::input-label for="password" :value="__('Password')" class="!text-xs" />

            <x-tenant-user::text-input
                id="password"
                class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />

            <x-tenant-user::input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex justify-end pt-1">
            <x-tenant-user::primary-button class="min-h-10 px-4 py-2.5 text-sm font-semibold border-0 rounded-lg shadow-sm text-white hover:opacity-95" style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                {{ __('Confirm') }}
            </x-tenant-user::primary-button>
        </div>
    </form>
</x-tenant-user::guest-layout>
