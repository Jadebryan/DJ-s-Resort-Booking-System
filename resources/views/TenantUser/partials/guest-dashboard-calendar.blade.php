@php
    $gMonth = $guestDashboardCalendarMonth ?? now()->startOfMonth();
    $gWeeks = $guestDashboardCalendarWeeks ?? [];
    $gListBookings = $guestDashboardMonthBookings ?? collect();
    $gBookingsQs = http_build_query(['year' => $gMonth->year, 'month' => $gMonth->month]);
    $gBookingsUrl = tenant_url('user/bookings');
    $gBookingDetailBase = $gBookingsUrl.'?'.$gBookingsQs.'#guest-booking-';
    $gFullCalUrl = $gBookingsUrl.'?'.$gBookingsQs.'#booking-calendar';
@endphp
{{-- Mirrors tenant staff dashboard calendar; data is this guest’s bookings only. --}}
<section id="booking-calendar"
         class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
         x-data="{ calView: 'grid' }">
    <div class="flex flex-col gap-4 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div class="min-w-0">
            <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ $gMonth->format('F Y') }}</h2>
            <p class="mt-0.5 text-xs text-gray-500">{{ __('Your bookings and stays overlapping this month.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="#guest-dash-cal-today"
               class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                {{ __('Today') }}
            </a>
            <a href="{{ $gBookingsUrl }}#guest-bookings-list"
               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-700"
               title="{{ __('My bookings list') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            </a>
            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50/80 p-0.5 shadow-sm">
                <button type="button"
                        @click="calView = 'grid'"
                        :class="calView === 'grid' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    {{ __('Month') }}
                </button>
                <button type="button"
                        @click="calView = 'list'"
                        :class="calView === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    {{ __('List') }}
                </button>
            </div>
            <a href="{{ $gFullCalUrl }}"
               class="hidden sm:inline-flex text-xs font-semibold text-teal-700 hover:text-teal-800">{{ __('Full view') }}</a>
            <a href="{{ tenant_url('book') }}"
               class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                <span class="mr-1 text-base leading-none">+</span> {{ __('New booking') }}
            </a>
        </div>
    </div>

    <div x-show="calView === 'grid'" class="min-w-0 overflow-x-auto">
        <table class="min-w-[720px] w-full border-collapse">
            <thead>
                <tr>
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow)
                        <th class="w-[14.28%] border-b border-gray-200 bg-gray-50/90 px-1 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ __($dow) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($gWeeks as $week)
                    <tr>
                        @foreach($week as $cell)
                            @php
                                $isToday = $cell['date']->isToday();
                                $inMonth = $cell['isCurrentMonth'];
                            @endphp
                            <td @if($isToday && $inMonth) id="guest-dash-cal-today" @endif
                                class="align-top border-b border-r border-gray-100 bg-white p-1 last:border-r-0 min-h-[116px] w-[14.28%] transition-colors {{ ! $inMonth ? 'bg-gray-50/70' : '' }} {{ $isToday && $inMonth ? 'bg-teal-50/30 ring-1 ring-inset ring-teal-200/60' : '' }} scroll-mt-24">
                                <div class="flex justify-end px-0.5 pt-1">
                                    @if($inMonth)
                                        @if($isToday)
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-semibold text-white shadow-sm">{{ $cell['date']->format('j') }}</span>
                                        @else
                                            <span class="px-1 text-xs font-semibold text-gray-800">{{ $cell['date']->format('j') }}</span>
                                        @endif
                                    @else
                                        <span class="px-1 text-xs font-medium text-gray-400">{{ $cell['date']->format('j') }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 space-y-1 px-0.5 pb-1">
                                    @foreach($cell['bookings']->take(3) as $booking)
                                        @php
                                            $guestLabel = $booking->guest_name ?? $booking->user?->name ?? __('Guest');
                                            $accent = $booking->status === 'confirmed' ? 'border-l-teal-500' : ($booking->status === 'pending' ? 'border-l-amber-400' : 'border-l-gray-300');
                                            $bg = $booking->status === 'confirmed' ? 'bg-teal-50/50' : ($booking->status === 'pending' ? 'bg-amber-50/40' : 'bg-gray-100/80');
                                        @endphp
                                        <a href="{{ $gBookingDetailBase.$booking->id }}"
                                           class="flex min-h-[2.25rem] flex-col justify-center rounded-md border border-y border-r border-gray-100/90 {{ $bg }} py-1 pl-2 pr-1.5 text-left shadow-sm ring-1 ring-gray-900/5 border-l-[3px] {{ $accent }} transition hover:ring-gray-300/60"
                                           title="{{ $booking->room?->name ?? __('Room') }} — {{ $guestLabel }} ({{ $booking->status }})">
                                            <span class="text-[10px] font-medium tabular-nums text-gray-500">{{ $booking->check_in->format('M j') }}</span>
                                            <span class="truncate text-[11px] font-semibold leading-tight text-gray-900">{{ $booking->room?->name ?? '—' }}</span>
                                            <span class="truncate text-[10px] text-gray-600">{{ $guestLabel }}</span>
                                        </a>
                                    @endforeach
                                    @if($cell['bookings']->count() > 3)
                                        <p class="px-0.5 text-center text-[10px] font-medium text-gray-500">+{{ $cell['bookings']->count() - 3 }} {{ __('more') }}</p>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <template x-if="calView === 'list'">
        <div class="divide-y divide-gray-100 px-3 py-2 sm:px-5">
            @forelse($gListBookings as $booking)
                @php
                    $guestLabel = $booking->guest_name ?? $booking->user?->name ?? __('Guest');
                    $accent = $booking->status === 'confirmed' ? 'border-l-teal-500' : ($booking->status === 'pending' ? 'border-l-amber-400' : 'border-l-gray-300');
                    $bg = $booking->status === 'confirmed' ? 'bg-teal-50/30' : ($booking->status === 'pending' ? 'bg-amber-50/30' : 'bg-gray-50');
                @endphp
                <a href="{{ $gBookingDetailBase.$booking->id }}" class="flex gap-3 py-3 first:pt-2 transition hover:bg-gray-50/80">
                    <div class="flex shrink-0 flex-col items-center justify-center rounded-lg border border-gray-100 bg-white px-2 py-1.5 text-center shadow-sm">
                        <span class="text-[10px] font-semibold uppercase text-gray-400">{{ $booking->check_in->format('M') }}</span>
                        <span class="text-lg font-bold tabular-nums text-gray-900">{{ $booking->check_in->format('j') }}</span>
                    </div>
                    <div class="min-w-0 flex-1 rounded-lg border border-y border-r border-gray-100 {{ $bg }} border-l-[3px] {{ $accent }} py-2 pl-3 pr-3 shadow-sm">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <span class="truncate text-sm font-semibold text-gray-900">{{ $booking->room?->name ?? '—' }}</span>
                            <span class="shrink-0 text-[11px] font-medium capitalize text-gray-500">{{ $booking->status }}</span>
                        </div>
                        <p class="mt-0.5 truncate text-xs text-gray-600">{{ $guestLabel }}</p>
                        <p class="mt-1 text-[11px] tabular-nums text-gray-500">{{ $booking->check_in->format('M j') }} – {{ $booking->check_out->format('M j') }}</p>
                    </div>
                </a>
            @empty
                <p class="py-8 text-center text-sm text-gray-500">{{ __('No bookings overlap this month yet.') }}</p>
            @endforelse
        </div>
    </template>

    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-gray-100 bg-gray-50/50 px-4 py-3 text-xs text-gray-600 sm:px-5">
        <span class="font-semibold text-gray-400">{{ __('Status') }}</span>
        <span class="inline-flex items-center gap-2"><span class="h-3 w-1 rounded-full bg-teal-500"></span> {{ __('Confirmed') }}</span>
        <span class="inline-flex items-center gap-2"><span class="h-3 w-1 rounded-full bg-amber-400"></span> {{ __('Pending') }}</span>
        <span class="inline-flex items-center gap-2"><span class="h-3 w-1 rounded-full bg-gray-300"></span> {{ __('Cancelled / other') }}</span>
    </div>
</section>
