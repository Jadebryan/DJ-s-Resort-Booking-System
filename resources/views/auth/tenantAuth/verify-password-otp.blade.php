<x-tenant::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 mb-1 sm:text-[11px]">
            {{ __('Check your email') }}
        </p>
        <h1 class="text-lg font-semibold leading-snug text-slate-900 sm:text-xl">
            {{ __('Enter the 6-digit code') }}
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 sm:text-xs">
            {{ __('We sent a code to :email. Enter it below to continue.', ['email' => $email]) }}
        </p>
    </div>

    <form method="POST" action="{{ route('tenant.password.otp.verify') }}" class="space-y-3">
        @csrf

        <div>
            <x-tenant::input-label for="otp" :value="__('Verification code')" class="!text-xs" />
            <x-tenant::text-input
                id="otp"
                class="mt-0.5 block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-center text-lg font-semibold tracking-[0.35em] text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="text"
                name="otp"
                required
                autofocus
                placeholder="000000"
                constraint="otp"
            />
            <x-tenant::input-error :messages="$errors->get('otp')" class="mt-1" />
        </div>

        <div class="space-y-2 pt-1">
            <x-tenant::primary-button class="w-full min-h-10 justify-center rounded-lg border-0 bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 py-2.5 text-sm font-semibold shadow-sm hover:brightness-110">
                {{ __('Verify and continue') }}
            </x-tenant::primary-button>

            <div class="flex flex-col items-center gap-1 pt-0.5 text-center">
                <form method="POST" action="{{ route('tenant.password.otp.resend') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-[11px] font-medium text-sky-700 underline underline-offset-2 hover:text-sky-900 sm:text-xs">
                        {{ __('Resend code') }}
                    </button>
                </form>
                <a
                    href="{{ route('tenant.password.request') }}"
                    class="text-[11px] font-medium text-slate-700 underline underline-offset-2 hover:text-slate-900 sm:text-xs"
                >
                    {{ __('Start over with a different email') }}
                </a>
                <a
                    href="{{ route('tenant.login') }}"
                    class="text-[11px] font-medium text-slate-600 hover:text-slate-900 sm:text-xs"
                >
                    {{ __('Back to sign in') }}
                </a>
            </div>
        </div>
    </form>
</x-tenant::guest-layout>
