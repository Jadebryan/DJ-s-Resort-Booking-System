<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Booking Reports & Financial Summary') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Export data, revenue by room, and recent reservations.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6"
         x-data="{ pdfModalOpen: false, analyticsModalOpen: false, advancedModalOpen: false, activityModalOpen: false, pdfUrl: @js(tenant_url('reports/export/pdf')) }"
         @keydown.escape.window="pdfModalOpen = false; analyticsModalOpen = false; advancedModalOpen = false; activityModalOpen = false">
        <div class="flex flex-wrap items-center gap-2">
            @if($canExport ?? false)
                <a href="{{ tenant_url('reports/export/csv') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    {{ __('Download CSV') }}
                </a>
                <button type="button" @click="pdfModalOpen = true"
                   class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700">
                    {{ __('Print / Save as PDF') }}
                </button>
            @endif
            @if($canUseAnalytics ?? false)
                <button type="button" @click="analyticsModalOpen = true"
                   class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700">
                    {{ __('Revenue analytics') }}
                </button>
            @endif
            @if($canUseAdvancedReports ?? false)
                <button type="button" @click="advancedModalOpen = true"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    {{ __('Advanced reports') }}
                </button>
            @endif
            @if($canUseActivityLog ?? false)
                <button type="button" @click="activityModalOpen = true"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    {{ __('Activity log') }}
                </button>
            @endif
        </div>

        {{-- Summary cards --}}
        <x-stat-kpi-toggle storage-key="mtrbs.tenant.reports.kpi.hidden" grid-class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5 min-w-0" accent="teal">
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Total Bookings</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalBookings }}</p>
            </div>
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Pending</p>
                <p class="mt-1 text-2xl font-semibold text-amber-600">{{ $pending }}</p>
            </div>
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Confirmed</p>
                <p class="mt-1 text-2xl font-semibold text-teal-600">{{ $confirmed }}</p>
            </div>
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Cancelled</p>
                <p class="mt-1 text-2xl font-semibold text-gray-600">{{ $cancelled }}</p>
            </div>
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Revenue (confirmed)</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">₱{{ number_format($revenue, 0) }}</p>
            </div>
        </x-stat-kpi-toggle>

        @if($canUseAdvancedReports ?? false)
            {{-- Revenue by room --}}
            <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200/80 p-5 min-w-0">
                    <h2 class="text-lg font-semibold text-gray-800">Revenue by Room</h2>
                    <p class="mt-1 text-sm text-gray-500">Earnings from confirmed bookings per room.</p>
                </div>
                @if($revenueByRoom->isEmpty())
                    <div class="p-6 text-gray-600">No confirmed bookings yet. Revenue will appear here once bookings are confirmed.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Room</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Bookings</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($revenueByRoom as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['room']?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $row['count'] }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">₱{{ number_format($row['revenue'], 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50/80">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">Total</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $revenueByRoom->sum('count') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($revenue, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Recent bookings --}}
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200/80 p-5 min-w-0">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Recent Bookings</h2>
                    <p class="mt-1 text-sm text-gray-500">Latest reservations. View and manage all in Bookings.</p>
                </div>
                @if(tenant_staff_can('bookings', 'read'))
                <a href="{{ tenant_url('bookings') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700">
                    View all bookings
                </a>
                @endif
            </div>
            @if($bookings->isEmpty())
                <div class="p-6 text-gray-600">No bookings yet. Bookings will appear here when customers make reservations.</div>
            @else
                <div class="min-w-0 overflow-x-auto">
                    <table class="min-w-[720px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Room</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Guest</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Check-in</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Check-out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($bookings->take(10) as $booking)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $booking->room?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->guest_name ?? $booking->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->check_in?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->check_out?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($booking->status === 'confirmed')
                                            <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-700">Confirmed</span>
                                        @elseif($booking->status === 'cancelled')
                                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">Cancelled</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($bookings->count() > 10)
                    <div class="border-t border-gray-200/80 p-4 text-center text-sm text-gray-500">Showing latest 10 of {{ $bookings->count() }} bookings.</div>
                @endif
            @endif
        </div>

        @if($canUseAnalytics ?? false)
        <template x-teleport="body">
            <div x-show="analyticsModalOpen" x-cloak class="fixed inset-0 z-[140] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm" @click="analyticsModalOpen = false"></div>
                <div @click.stop class="relative z-10 flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 sm:px-5">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-gray-900">{{ __('Revenue analytics') }}</h2>
                            <p class="mt-0.5 text-xs text-gray-500">{{ __('Confirmed booking income by month and by day.') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ tenant_url('reports/analytics') }}" target="_blank" rel="noopener"
                               class="hidden rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 sm:inline-flex">
                                {{ __('Open in new tab') }}
                            </a>
                            <button type="button" @click="analyticsModalOpen = false"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
                        @include('Tenant.reports.partials.analytics-body', [
                            'revenueByMonth' => $analyticsRevenueByMonth ?? [],
                            'revenueByDay' => $analyticsRevenueByDay ?? [],
                        ])
                    </div>
                </div>
            </div>
        </template>
        @endif

        @if($canUseAdvancedReports ?? false)
        <template x-teleport="body">
            <div x-show="advancedModalOpen" x-cloak class="fixed inset-0 z-[140] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm" @click="advancedModalOpen = false"></div>
                <div @click.stop class="relative z-10 flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 sm:px-5">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-gray-900">{{ __('Advanced reports') }}</h2>
                            <p class="mt-0.5 text-xs text-gray-500">{{ __('Detailed revenue by room and booking contribution.') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ tenant_url('reports/advanced') }}" target="_blank" rel="noopener"
                               class="hidden rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 sm:inline-flex">
                                {{ __('Open in new tab') }}
                            </a>
                            <button type="button" @click="advancedModalOpen = false"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
                        @include('Tenant.reports.partials.advanced-body', [
                            'revenueByRoom' => $revenueByRoom,
                            'totalRevenue' => (float) $revenueByRoom->sum('revenue'),
                            'totalConfirmedBookings' => (int) $revenueByRoom->sum('count'),
                        ])
                    </div>
                </div>
            </div>
        </template>
        @endif

        @if($canUseActivityLog ?? false)
        <template x-teleport="body">
            <div x-show="activityModalOpen" x-cloak class="fixed inset-0 z-[140] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm" @click="activityModalOpen = false"></div>
                <div @click.stop class="relative z-10 flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 sm:px-5">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-gray-900">{{ __('Activity log') }}</h2>
                            <p class="mt-0.5 text-xs text-gray-500">{{ __('Recent actions by staff and owners on this resort.') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ tenant_url('activity') }}" target="_blank" rel="noopener"
                               class="hidden rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 sm:inline-flex">
                                {{ __('Open full log') }}
                            </a>
                            <button type="button" @click="activityModalOpen = false"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
                        @include('Tenant.activity.partials.log-panel', [
                            'logs' => $activityLogsPreview ?? collect(),
                            'showPagination' => false,
                            'previewFooter' => __('Showing the 50 most recent entries. Use “Open full log” for pagination and full history.'),
                        ])
                    </div>
                </div>
            </div>
        </template>
        @endif

        <template x-teleport="body">
            <div x-show="pdfModalOpen" x-cloak class="fixed inset-0 z-[140] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm" @click="pdfModalOpen = false"></div>
                <div @click.stop class="relative z-10 flex h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 sm:px-5">
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('Print / Save as PDF') }}</h2>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="$refs.reportPdfFrame?.contentWindow?.print?.()"
                                    class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                                {{ __('Print / Save') }}
                            </button>
                            <button type="button" @click="pdfModalOpen = false"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                    <iframe x-ref="reportPdfFrame" :src="pdfUrl" class="h-full w-full bg-white" title="Reports PDF preview"></iframe>
                </div>
            </div>
        </template>
    </div>
</x-tenant::app-layout>
