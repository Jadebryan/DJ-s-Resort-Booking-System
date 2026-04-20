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
        <h1 class="text-lg font-semibold leading-snug text-slate-900 sm:text-xl">
            {{ __('New password') }}
        </h1>
        <p class="mt-1 text-[11px] leading-snug text-slate-600 sm:text-xs">
            {{ __('Choose a password for :email', ['email' => $email]) }}
        </p>
    </div>

    <form method="POST" action="{{ route('user.password.store') }}" class="space-y-3">
        @csrf

        <div>
            <x-tenant-user::input-label for="password" :value="__('Password')" class="!text-xs" />
            <div class="relative mt-0.5">
                <x-tenant-user::text-input
                    id="password"
                    class="block w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    type="password"
                    name="password"
                    required
                    autofocus
                    autocomplete="new-password"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-1.5 my-0.5 inline-flex items-center rounded-md px-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                    onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';"
                    aria-label="Toggle password visibility"
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
                    aria-label="Toggle password visibility"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                        <circle cx="12" cy="12" r="3.2" />
                    </svg>
                </button>
            </div>
            <x-tenant-user::input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="space-y-2 pt-1">
            <x-tenant-user::primary-button class="flex w-full min-h-10 justify-center rounded-lg border-0 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95" style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                {{ __('Save new password') }}
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
    </form>
</x-tenant-user::guest-layout>
