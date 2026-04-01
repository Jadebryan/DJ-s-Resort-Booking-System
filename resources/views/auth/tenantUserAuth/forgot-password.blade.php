@php
    $tenant = current_tenant();
    $primary = $tenant?->primary_color ?? '#0ea5e9';
    $secondary = $tenant?->secondary_color ?? '#0369a1';
@endphp
<x-tenant-user::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 mb-1 sm:text-[11px]">
            Guest portal
        </p>
        <h1 class="font-display text-lg font-semibold leading-snug text-slate-900 sm:text-xl">
            Forgot your password?
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 sm:text-xs">
            {{ __('Enter your email. We’ll send a 6-digit code, then you can choose a new password.') }}
        </p>
    </div>

    <x-form-with-busy method="POST" action="{{ route('user.password.email') }}" class="space-y-3" :overlay="false" busy-message="{{ __('Sending code…') }}">
        @csrf

        <div>
            <x-tenant-user::input-label for="email" :value="__('Email')" class="!text-xs" />
            <x-tenant-user::text-input
                id="email"
                class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
            />
            <x-tenant-user::input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="space-y-2 pt-1">
            <x-tenant-user::primary-button class="w-full min-h-10 py-2.5 flex justify-center border-0 rounded-lg shadow-sm text-sm font-semibold text-white hover:opacity-95" style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                {{ __('Send verification code') }}
            </x-tenant-user::primary-button>

            <div class="flex justify-center pt-0.5">
                <a
                    href="{{ route('tenant.user.login') }}"
                    class="text-[11px] font-medium text-slate-700 hover:text-slate-900 underline underline-offset-2 sm:text-xs"
                >
                    {{ __('Back to sign in') }}
                </a>
            </div>
        </div>
    </x-form-with-busy>
</x-tenant-user::guest-layout>
