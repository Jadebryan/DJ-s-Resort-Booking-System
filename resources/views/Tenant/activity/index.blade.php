<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Activity Log') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Recent actions by staff and owners on this resort.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        @include('Tenant.activity.partials.log-panel', [
            'logs' => $logs,
            'showPagination' => true,
        ])
    </div>
</x-tenant::app-layout>
