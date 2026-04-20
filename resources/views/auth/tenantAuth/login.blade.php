@php $onTenantDomain = $onTenantDomain ?? false; @endphp
<x-tenant::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 mb-1 sm:text-[11px]">
            Tenant portal
        </p>
        <h1 class="text-lg font-semibold leading-snug text-slate-900 dark:text-white sm:text-xl">
            @if($onTenantDomain)
                {{ __('Sign in to your resort') }}
            @else
                {{ __('Go to your resort sign-in') }}
            @endif
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 dark:text-slate-400 sm:text-xs">
            @if($onTenantDomain)
                {{ __('Manage bookings, guests, and availability from your dashboard.') }}
            @else
                {{ __('Enter the website address you use for your resort (no https:// needed).') }}
            @endif
        </p>
    </div>

    @if($onTenantDomain)
        <x-form-with-busy method="POST" action="{{ tenant_url('login') }}" class="space-y-3" :overlay="false" busy-message="{{ __('Signing in…') }}">
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
                    constraint="email"
                />
                <x-tenant::input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <x-tenant::input-label for="tenant_login_password" :value="__('Password')" class="!text-xs" />
                <div class="relative mt-0.5">
                    <x-tenant::text-input
                        id="tenant_login_password"
                        class="block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-1.5 my-0.5 inline-flex items-center rounded-md px-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        onclick="
                            const input = document.getElementById('tenant_login_password');
                            if (!input) return;
                            input.type = input.type === 'password' ? 'text' : 'password';
                        "
                        aria-label="Toggle password visibility"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                            <circle cx="12" cy="12" r="3.2" />
                        </svg>
                    </button>
                </div>
                <x-tenant::input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="flex items-center justify-between gap-2 pt-0.5">
                <label for="remember_me" class="inline-flex items-center">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500"
                        name="remember"
                    >
                    <span class="ms-1.5 text-[11px] text-slate-700 sm:text-xs">{{ __('Keep me signed in') }}</span>
                </label>

                @if (Route::has('tenant.password.request'))
                    <a
                        class="shrink-0 text-[11px] font-semibold text-slate-700 underline underline-offset-2 hover:text-slate-900 sm:text-xs"
                        href="{{ route('tenant.password.request') }}"
                    >
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="pt-0.5">
                <x-busy-submit class="w-full min-h-10 rounded-lg border-0 bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-110" busy-text="{{ __('Signing in…') }}">
                    {{ __('Sign in') }}
                </x-busy-submit>
            </div>
        </x-form-with-busy>
    @else
        <x-form-with-busy method="POST" action="{{ url('/tenant/login') }}" class="space-y-3" :overlay="false" busy-message="{{ __('Continuing…') }}">
            @csrf

            <div>
                <x-tenant::input-label for="tenant_domain" :value="__('Resort website / domain')" class="!text-xs" />
                <x-tenant::text-input
                    id="tenant_domain"
                    class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    type="text"
                    name="tenant_domain"
                    :value="old('tenant_domain')"
                    required
                    autofocus
                    placeholder="e.g. sunrise-resort.example.com"
                    constraint="primaryDomain"
                />
                <x-tenant::input-error :messages="$errors->get('tenant_domain')" class="mt-1" />
                <p class="mt-1 text-[10px] leading-snug text-slate-500 sm:text-[11px]">{{ __('Use the same hostname guests see in the browser (no https:// needed).') }}</p>
            </div>

            <div class="pt-0.5">
                <x-busy-submit class="w-full min-h-10 rounded-lg border-0 bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-110" busy-text="{{ __('Continuing…') }}">
                    {{ __('Continue') }}
                </x-busy-submit>
            </div>
        </x-form-with-busy>
    @endif

    <div class="flex items-center gap-2 pt-3">
        <span class="h-px flex-1 bg-slate-200"></span>
        <span class="text-[10px] uppercase tracking-[0.18em] text-slate-400">or</span>
        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <div class="flex flex-col items-center gap-0.5 pt-1.5">
        <p class="text-[11px] text-slate-700 sm:text-xs">{{ __('New resort?') }}</p>
        <a
            href="{{ central_route('tenant.select.register') }}"
            class="inline-flex items-center justify-center rounded-full border border-slate-300 px-3 py-1 text-[11px] font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition sm:text-xs"
        >
            {{ __('Create a resort tenant') }}
        </a>
    </div>
</x-tenant::guest-layout>
