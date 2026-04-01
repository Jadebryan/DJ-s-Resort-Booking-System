<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Access control') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Role-based permissions for staff and guests.') }}</p>
        </div>
    </x-slot>

    <div class="max-w-2xl rounded-xl border border-amber-200 bg-amber-50/80 p-6 text-amber-950 shadow-sm">
        <h2 class="text-sm font-semibold">{{ __('Database update required') }}</h2>
        <p class="mt-2 text-sm text-amber-900/90">
            {{ __('The access control tables are not present in this resort database yet. Run tenant migrations from the project root, for example:') }}
        </p>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-amber-100/80 p-3 text-xs text-amber-950">php artisan migrate --path=database/migrations/tenant --database=tenant</pre>
        <p class="mt-3 text-xs text-amber-900/80">
            {{ __('Use your tenant connection name if it differs. After migrating, reload this page.') }}
        </p>
    </div>
</x-tenant::app-layout>
