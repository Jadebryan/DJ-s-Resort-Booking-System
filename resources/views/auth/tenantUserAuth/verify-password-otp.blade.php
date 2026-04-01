@php
    $tenant = current_tenant();
    $primary = $tenant?->primary_color ?? '#0ea5e9';
    $secondary = $tenant?->secondary_color ?? '#0369a1';
@endphp
<x-tenant-user::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 mb-1 sm:text-[11px]">
            {{ __('Guest portal') }}
        </p>
        <h1 class="text-lg font-semibold leading-snug text-slate-900 sm:text-xl">
            {{ __('Enter the 6-digit code') }}
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 sm:text-xs">
            {{ __('We sent a code to :email. Enter it below to continue.', ['email' => $email]) }}
        </p>
    </div>

    @if (session('status'))
        <p class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-center text-[11px] font-medium text-emerald-800 sm:text-xs" role="status">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('tenant.user.password.otp.verify') }}" class="space-y-3">
        @csrf

        <div>
            <x-tenant-user::input-label for="otp" :value="__('Verification code')" class="!text-xs" />
            <x-tenant-user::text-input
                id="otp"
                class="mt-0.5 block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-center text-lg font-semibold tracking-[0.35em] text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="text"
                name="otp"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                pattern="[0-9]{6}"
                required
                autofocus
                placeholder="000000"
            />
            <x-tenant-user::input-error :messages="$errors->get('otp')" class="mt-1" />
        </div>

        <div class="space-y-2 pt-1">
            <x-tenant-user::primary-button class="flex w-full min-h-10 justify-center rounded-lg border-0 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95" style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                {{ __('Verify and continue') }}
            </x-tenant-user::primary-button>

            <div class="flex flex-col items-center gap-1 pt-0.5 text-center">
                <form method="POST" action="{{ route('tenant.user.password.otp.resend') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-[11px] font-medium underline underline-offset-2 hover:opacity-90 sm:text-xs" style="color: {{ $primary }};">
                        {{ __('Resend code') }}
                    </button>
                </form>
                <a
                    href="{{ route('tenant.user.password.request') }}"
                    class="text-[11px] font-medium text-slate-700 underline underline-offset-2 hover:text-slate-900 sm:text-xs"
                >
                    {{ __('Start over with a different email') }}
                </a>
                <a
                    href="{{ route('tenant.user.login') }}"
                    class="text-[11px] font-medium text-slate-600 hover:text-slate-900 sm:text-xs"
                >
                    {{ __('Back to sign in') }}
                </a>
            </div>
        </div>
    </form>
</x-tenant-user::guest-layout>
