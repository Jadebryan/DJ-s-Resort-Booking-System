<x-tenant::app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3 min-w-0">
            <div class="leading-tight min-w-0">
                <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Booking Calendar') }}</h1>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Month view of reservations by day.') }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ tenant_url('bookings/calendar') }}?year={{ $prevYear }}&month={{ $prevMonth }}"
                   class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">← Previous</a>
                <span class="px-4 py-2 text-lg font-semibold text-gray-800">{{ $date->format('F Y') }}</span>
                <a href="{{ tenant_url('bookings/calendar') }}?year={{ $nextYear }}&month={{ $nextMonth }}"
                   class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">Next →</a>
            </div>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        <a href="{{ tenant_url('bookings') }}" class="inline-flex text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">← Back to list</a>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="min-w-0 overflow-x-auto p-4">
                <table class="min-w-[720px] w-full border-collapse calendar-grid">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Sun</th>
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Mon</th>
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Tue</th>
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Wed</th>
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Thu</th>
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Fri</th>
                            <th class="w-[14.28%] p-2 text-left text-xs font-medium uppercase text-gray-500">Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calendarWeeks as $week)
                            <tr class="border-b border-gray-100">
                                @foreach($week as $cell)
                                    <td class="min-h-[100px] align-top p-1 {{ $cell['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50/50' }}">
                                        <div class="mb-1 text-sm font-medium {{ $cell['isCurrentMonth'] ? 'text-gray-700' : 'text-gray-400' }}">{{ $cell['date']->format('j') }}</div>
                                        <div class="space-y-1">
                                            @foreach($cell['bookings'] as $booking)
                                                <div class="truncate rounded px-2 py-1 text-xs
                                                    @if($booking->status === 'confirmed') bg-teal-100 text-teal-800
                                                    @elseif($booking->status === 'pending') bg-amber-100 text-amber-800
                                                    @else bg-gray-100 text-gray-600
                                                    @endif"
                                                     title="{{ $booking->room?->name ?? 'Room' }} — {{ $booking->guest_name ?? $booking->user?->name ?? 'Guest' }} ({{ $booking->status }})">
                                                    {{ $booking->room?->name ?? '—' }} ({{ $booking->status }})
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap gap-4 border-t border-gray-200/80 px-4 py-3 text-sm">
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-teal-100"></span> Confirmed</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-amber-100"></span> Pending</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-gray-100"></span> Cancelled</span>
            </div>
        </div>
    </div>
</x-tenant::app-layout>
