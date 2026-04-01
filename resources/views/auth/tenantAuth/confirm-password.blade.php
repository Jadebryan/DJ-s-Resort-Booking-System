<x-tenant::guest-layout container-class="max-w-[360px]" :compact="true">
    <div class="mb-3 text-[11px] leading-snug text-slate-600 sm:text-xs">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('tenant.password.confirm') }}" class="space-y-3">
        @csrf

        <div>
            <x-tenant::input-label for="password" :value="__('Password')" class="!text-xs" />

            <x-tenant::text-input
                id="password"
                class="block mt-0.5 w-full min-h-10 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />

            <x-tenant::input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex justify-end pt-1">
            <x-tenant::primary-button class="min-h-10 px-4 py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 hover:brightness-110 border-0 rounded-lg shadow-sm">
                {{ __('Confirm') }}
            </x-tenant::primary-button>
        </div>
    </form>
</x-tenant::guest-layout>
