<x-tenant::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 mb-1 sm:text-[11px]">
            {{ __('Reset tenant password') }}
        </p>
        <h1 class="text-lg font-semibold leading-snug text-slate-900 sm:text-xl">
            {{ __('We’ll help you back into your resort') }}
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 sm:text-xs">
            {{ __('Enter your work email. We’ll send a 6-digit code to verify it’s you, then you can set a new password.') }}
        </p>
    </div>

    <x-form-with-busy method="POST" action="{{ route('tenant.password.email') }}" class="space-y-3" :overlay="false" busy-message="{{ __('Sending code…') }}">
        @csrf

        <div>
            <x-tenant::input-label for="email" :value="__('Work email')" class="!text-xs" />
            <x-tenant::text-input
                id="email"
                class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-tenant::input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="space-y-2 pt-1">
            <x-tenant::primary-button class="w-full min-h-10 justify-center py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 hover:brightness-110 border-0 rounded-lg shadow-sm">
                {{ __('Send verification code') }}
            </x-tenant::primary-button>

            <div class="flex justify-center pt-0.5">
                <a
                    href="{{ route('tenant.login') }}"
                    class="text-[11px] font-medium text-slate-700 hover:text-slate-900 underline underline-offset-2 sm:text-xs"
                >
                    {{ __('Back to sign in') }}
                </a>
            </div>
        </div>
    </x-form-with-busy>
</x-tenant::guest-layout>
