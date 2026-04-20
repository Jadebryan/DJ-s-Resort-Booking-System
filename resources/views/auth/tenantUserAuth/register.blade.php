@php
    $tenant = current_tenant();
    $primary = $tenant?->primary_color ?? '#0ea5e9';
    $secondary = $tenant?->secondary_color ?? '#0369a1';
@endphp
<x-tenant-user::guest-layout container-class="max-w-xl" :compact="true">
    <div class="mb-3 text-center">
        <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 sm:text-[11px]">
            {{ __('Guest portal') }}
        </p>
        <h1 class="text-lg font-semibold leading-snug text-slate-900 sm:text-xl">
            {{ __('Create an account') }}
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 sm:text-xs">
            {{ __('Register to book rooms and manage your stays.') }}
        </p>
    </div>

    <x-form-with-busy method="POST" action="{{ route('tenant.user.register') }}" class="space-y-3" :overlay="false" busy-message="{{ __('Creating your account…') }}">
        @csrf

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <x-tenant-user::input-label for="name" :value="__('Name')" class="!text-xs" />
                <x-tenant-user::text-input
                    id="name"
                    class="mt-0.5 block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    constraint="personName"
                />
                <x-tenant-user::input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-tenant-user::input-label for="email" :value="__('Email')" class="!text-xs" />
                <x-tenant-user::text-input
                    id="email"
                    class="mt-0.5 block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    constraint="email"
                />
                <x-tenant-user::input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <x-tenant-user::input-label for="password" :value="__('Password')" class="!text-xs" />
                <div class="relative mt-0.5">
                    <x-tenant-user::text-input
                        id="password"
                        class="block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-1.5 my-0.5 inline-flex items-center rounded-md px-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';"
                        aria-label="{{ __('Toggle password visibility') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                            <circle cx="12" cy="12" r="3.2" />
                        </svg>
                    </button>
                </div>
                <x-tenant-user::input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <x-tenant-user::input-label for="password_confirmation" :value="__('Confirm Password')" class="!text-xs" />
                <div class="relative mt-0.5">
                    <x-tenant-user::text-input
                        id="password_confirmation"
                        class="block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-1.5 my-0.5 inline-flex items-center rounded-md px-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        onclick="var i=document.getElementById('password_confirmation');i.type=i.type==='password'?'text':'password';"
                        aria-label="{{ __('Toggle password visibility') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                            <circle cx="12" cy="12" r="3.2" />
                        </svg>
                    </button>
                </div>
                <x-tenant-user::input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="pt-0.5">
            <x-busy-submit class="flex min-h-10 w-full justify-center rounded-lg border-0 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95" style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});" busy-text="{{ __('Creating your account…') }}">
                {{ __('Register') }}
            </x-busy-submit>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <span class="h-px flex-1 bg-slate-200"></span>
            <span class="text-[10px] uppercase tracking-[0.18em] text-slate-400">{{ __('or') }}</span>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <div class="flex flex-col items-center gap-0.5">
            <p class="text-[11px] text-slate-700 sm:text-xs">{{ __('Already registered?') }}</p>
            <a
                href="{{ route('tenant.user.login') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-300 px-3 py-1 text-[11px] font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:text-xs"
            >
                {{ __('Sign in') }}
            </a>
        </div>
    </x-form-with-busy>
</x-tenant-user::guest-layout>
