<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Revenue Analytics') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Confirmed booking income by month and by day.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        <a href="{{ tenant_url('reports') }}" class="text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">← {{ __('Back to Reports') }}</a>

        @include('Tenant.reports.partials.analytics-body', ['revenueByMonth' => $revenueByMonth, 'revenueByDay' => $revenueByDay])
    </div>
</x-tenant::app-layout>
