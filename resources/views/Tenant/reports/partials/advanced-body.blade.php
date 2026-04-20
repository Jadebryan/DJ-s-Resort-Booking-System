{{-- Expects: $revenueByRoom (collection of rows), $totalRevenue (float), $totalConfirmedBookings (int) --}}
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-gray-600">
        <span>{{ __('Total confirmed bookings: :count', ['count' => number_format($totalConfirmedBookings)]) }}</span>
    </div>

    <x-stat-kpi-toggle storage-key="mtrbs.tenant.reports-advanced.kpi.hidden" grid-class="grid grid-cols-1 gap-4 sm:grid-cols-2" accent="teal">
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">{{ __('Rooms with revenue') }}</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($revenueByRoom->count()) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">{{ __('Total revenue') }}</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">₱{{ number_format($totalRevenue, 0) }}</p>
        </div>
    </x-stat-kpi-toggle>

    <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-200/80 p-5 min-w-0">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('Revenue by Room (Advanced)') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Premium-only advanced breakdown by room.') }}</p>
        </div>
        @if($revenueByRoom->isEmpty())
            <div class="p-6 text-gray-600">{{ __('No confirmed bookings available for advanced analysis yet.') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('Room') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ __('Bookings') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ __('Revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($revenueByRoom as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['room']?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row['count']) }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">₱{{ number_format($row['revenue'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
