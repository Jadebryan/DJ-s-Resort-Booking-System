<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Advanced Reports') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Detailed revenue by room and booking contribution.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        <a href="{{ tenant_url('reports') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
            {{ __('Back to reports') }}
        </a>

        @include('Tenant.reports.partials.advanced-body', [
            'revenueByRoom' => $revenueByRoom,
            'totalRevenue' => $totalRevenue,
            'totalConfirmedBookings' => $totalConfirmedBookings,
        ])
    </div>
</x-tenant::app-layout>
