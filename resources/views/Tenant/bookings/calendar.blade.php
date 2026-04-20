<x-tenant::app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3 min-w-0">
            <div class="leading-tight min-w-0">
                <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Booking calendar') }}</h1>
                <p class="mt-0.5 text-[11px] text-gray-500">{{ __('Browse reservations by month or list.') }}</p>
            </div>
            <a href="{{ tenant_url('bookings') }}"
               class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                {{ __('← Back to list') }}
            </a>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl">
        @include('Tenant.bookings.partials.booking-calendar-section', [
            'date' => $date,
            'year' => $year,
            'month' => $month,
            'calendarWeeks' => $calendarWeeks,
            'monthBookings' => $monthBookings,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
            'calendarNavPath' => 'bookings/calendar',
        ])
    </div>
</x-tenant::app-layout>
