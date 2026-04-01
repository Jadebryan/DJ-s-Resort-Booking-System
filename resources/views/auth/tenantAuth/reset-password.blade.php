<x-tenant::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-center">
        <h1 class="text-lg font-semibold text-slate-900 sm:text-xl">{{ __('New password') }}</h1>
        <p class="mt-1 text-[11px] text-slate-600 sm:text-xs">{{ __('Choose a password for :email', ['email' => $email]) }}</p>
    </div>

    @if (session('status'))
        <p class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-center text-[11px] font-medium text-emerald-800 sm:text-xs" role="status">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('tenant.password.store') }}" class="space-y-3">
        @csrf

        <div>
            <x-tenant::input-label for="password" :value="__('Password')" class="!text-xs" />
            <x-tenant::text-input
                id="password"
                class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
            />
            <x-tenant::input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-tenant::input-label for="password_confirmation" :value="__('Confirm Password')" class="!text-xs" />
            <x-tenant::text-input
                id="password_confirmation"
                class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-tenant::input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-1">
            <x-tenant::primary-button class="w-full min-h-10 justify-center py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 hover:brightness-110 border-0 rounded-lg shadow-sm">
                {{ __('Save new password') }}
            </x-tenant::primary-button>
        </div>
    </form>
</x-tenant::guest-layout>
