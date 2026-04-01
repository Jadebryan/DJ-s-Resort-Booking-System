<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Revenue Analytics') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Confirmed booking income by month and by day.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        <a href="{{ tenant_url('reports') }}" class="text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">← Back to Reports</a>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/80 p-5 min-w-0">
                <h2 class="text-lg font-semibold text-gray-800">Revenue by Month</h2>
                <p class="mt-1 text-sm text-gray-500">Income from confirmed bookings, grouped by month.</p>
            </div>
            @if(empty($revenueByMonth))
                <div class="p-6 text-gray-600">No confirmed bookings yet.</div>
            @else
                <div class="min-w-0 overflow-x-auto">
                    <table class="min-w-[480px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Revenue (₱)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($revenueByMonth as $month => $amount)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">₱{{ number_format($amount, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50/80">
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">Total</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format(array_sum($revenueByMonth), 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/80 p-5 min-w-0">
                <h2 class="text-lg font-semibold text-gray-800">Revenue by Day</h2>
                <p class="mt-1 text-sm text-gray-500">Daily income (last 90 days or all time).</p>
            </div>
            @if(empty($revenueByDay))
                <div class="p-6 text-gray-600">No confirmed bookings yet.</div>
            @else
                @php $recentDays = array_slice($revenueByDay, -90, 90, true); @endphp
                <div class="min-w-0 overflow-x-auto">
                    <table class="min-w-[480px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Revenue (₱)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($recentDays as $day => $amount)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($day)->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">₱{{ number_format($amount, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($revenueByDay) > 90)
                    <div class="border-t border-gray-200/80 p-4 text-sm text-gray-500">Showing last 90 days. Total days with revenue: {{ count($revenueByDay) }}.</div>
                @endif
            @endif
        </div>
    </div>
</x-tenant::app-layout>
