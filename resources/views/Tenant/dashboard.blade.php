<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Dashboard') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Resort overview, bookings, and quick links for staff.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5">
        <p class="text-sm text-gray-600">
            {{ __('Welcome back,') }} <span class="font-semibold text-gray-900">{{ auth('tenant')->user()->name }}</span>
            @if(auth('tenant')->user()->role === 'admin')
                <span class="text-gray-400">·</span> <span class="text-xs font-medium uppercase tracking-wide text-teal-700">{{ __('Admin') }}</span>
            @endif
        </p>

        @php
            $dashRooms = tenant_staff_can('rooms', 'read');
            $dashBookings = tenant_staff_can('bookings', 'read');
            $dashReports = tenant_staff_can('reports', 'read');
            $dashSettings = tenant_staff_can('settings', 'read');
            $dashPayment = tenant_staff_can('payment', 'read');
            $dashBranding = tenant_staff_can('branding', 'read');
            $dashStaff = tenant_staff_can('staff', 'read');
            $dashDomains = tenant_staff_can('domains', 'read');
        @endphp

        @if($dashRooms || $dashBookings || $dashReports)
        <x-stat-kpi-toggle storage-key="mtrbs.tenant.dashboard.kpi.hidden" grid-class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5" accent="teal">
            @if($dashRooms)
            <div class="rounded-xl border border-gray-200 bg-white/90 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ __('Rooms listed') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900">{{ $roomsCount ?? 0 }}</p>
                <a href="{{ tenant_url('rooms') }}" class="mt-1 inline-flex text-xs font-semibold text-teal-700 hover:text-teal-900">{{ __('Manage →') }}</a>
            </div>
            @endif
            @if($dashBookings)
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">{{ __('All bookings') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-indigo-950">{{ $totalBookings ?? 0 }}</p>
                <p class="mt-1 text-xs text-indigo-900/70">{{ __('Lifetime count') }}</p>
            </div>
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('Pending') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $pendingBookings ?? 0 }}</p>
                <p class="mt-1 text-xs text-amber-900/70">{{ __('Need action') }}</p>
            </div>
            <div class="rounded-xl border border-teal-100 bg-teal-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-teal-800/90">{{ __('Confirmed') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-teal-950">{{ $confirmedBookings ?? 0 }}</p>
                <p class="mt-1 text-xs text-teal-900/70">{{ __('Active stays') }}</p>
            </div>
            @endif
            @if($dashReports)
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 px-4 py-3 shadow-sm sm:col-span-2 xl:col-span-1">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Est. revenue') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-950">₱{{ number_format($totalRevenue ?? 0, 0) }}</p>
                <p class="mt-1 text-xs text-emerald-900/70">{{ __('From confirmed') }}</p>
            </div>
            @endif
        </x-stat-kpi-toggle>
        @endif

        @if($dashBookings && ($cancelledBookings ?? 0) > 0)
            <div class="rounded-lg border border-gray-200 bg-gray-50/80 px-4 py-2 text-xs text-gray-600">
                {{ __(':count cancelled bookings on record.', ['count' => $cancelledBookings]) }}
                <a href="{{ tenant_url('bookings') }}" class="ml-1 font-semibold text-teal-700 hover:text-teal-900">{{ __('View bookings') }}</a>
            </div>
        @endif

        @if($dashBookings)
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                 x-data="{ calView: 'grid' }">
            {{-- Toolbar (reference-style) --}}
            <div class="flex flex-col gap-4 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ $dashboardCalendarMonth->format('F Y') }}</h2>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('Bookings and stays overlapping this month.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="#dash-cal-today"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Today') }}
                    </a>
                    <a href="{{ tenant_url('bookings') }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-700"
                       title="{{ __('Filter / all bookings') }}">
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
                    @if($tenantHasBookingCalendar ?? false)
                        <a href="{{ tenant_url('bookings') }}#booking-calendar"
                           class="hidden sm:inline-flex text-xs font-semibold text-teal-700 hover:text-teal-800">{{ __('Full view') }}</a>
                    @endif
                    <a href="{{ tenant_url('book') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                        <span class="mr-1 text-base leading-none">+</span> {{ __('New booking') }}
                    </a>
                </div>
            </div>

            {{-- Month grid --}}
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
                        @foreach($dashboardCalendarWeeks as $week)
                            <tr>
                                @foreach($week as $cell)
                                    @php
                                        $isToday = $cell['date']->isToday();
                                        $inMonth = $cell['isCurrentMonth'];
                                    @endphp
                                    <td @if($isToday && $inMonth) id="dash-cal-today" @endif
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
                                                <a href="{{ tenant_url('bookings') }}"
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

            {{-- List view (x-if: inert until Alpine — avoids flash of both views) --}}
            <template x-if="calView === 'list'">
                <div class="divide-y divide-gray-100 px-3 py-2 sm:px-5">
                    @forelse($dashboardMonthBookings ?? [] as $booking)
                        @php
                            $guestLabel = $booking->guest_name ?? $booking->user?->name ?? __('Guest');
                            $accent = $booking->status === 'confirmed' ? 'border-l-teal-500' : ($booking->status === 'pending' ? 'border-l-amber-400' : 'border-l-gray-300');
                            $bg = $booking->status === 'confirmed' ? 'bg-teal-50/30' : ($booking->status === 'pending' ? 'bg-amber-50/30' : 'bg-gray-50');
                        @endphp
                        <a href="{{ tenant_url('bookings') }}" class="flex gap-3 py-3 first:pt-2 transition hover:bg-gray-50/80">
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

            {{-- Category / status legend --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-gray-100 bg-gray-50/50 px-4 py-3 text-xs text-gray-600 sm:px-5">
                <span class="font-semibold text-gray-400">{{ __('Status') }}</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-1 rounded-full bg-teal-500"></span> {{ __('Confirmed') }}</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-1 rounded-full bg-amber-400"></span> {{ __('Pending') }}</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-1 rounded-full bg-gray-300"></span> {{ __('Cancelled / other') }}</span>
            </div>
        </section>
        @endif

        @if($dashRooms || $dashBookings || $dashReports || $dashSettings || $dashPayment || $dashBranding || $dashStaff || $dashDomains)
        <section>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">{{ __('Manage your resort') }}</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @if($dashRooms)
                <a href="{{ tenant_url('rooms') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Rooms & cottages') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Manage rooms and pricing') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ $roomsCount ?? 0 }} {{ __('room(s) listed') }}</p>
                </a>
                @endif

                @if($dashBookings)
                <a href="{{ tenant_url('bookings') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Bookings') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Confirm or cancel reservations') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ $pendingBookings ?? 0 }} {{ __('pending') }}</p>
                </a>
                @endif

                @if($dashReports)
                <a href="{{ tenant_url('reports') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Reports') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Totals and financial summary') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('View reports') }}</p>
                </a>
                @endif

                @if($dashSettings)
                <a href="{{ tenant_url('settings') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-slate-600 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317L9.6 2.25 7.5 2.25l.75 2.067a7.963 7.963 0 00-2.231 1.29L4.1 4.75 3 6.85l1.823 1.012A7.963 7.963 0 004.5 12c0 .735.098 1.448.283 2.133L3 15.15l1.1 2.1 1.919-1.107A7.963 7.963 0 009 17.683L9.6 19.75h2.1l.675-2.067a7.963 7.963 0 002.231-1.29l1.919 1.107 1.1-2.1-1.783-1.017A7.963 7.963 0 0019.5 12c0-.735-.098-1.448-.283-2.133L21 8.85 19.9 6.75l-1.919 1.107a7.963 7.963 0 00-2.231-1.29L14.4 2.25h-2.1l-.675 2.067zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-slate-800 truncate">{{ __('Settings') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Database, updates, resort info') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Configure') }}</p>
                </a>
                @endif

                @if($dashBranding)
                <a href="{{ tenant_url('branding') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Branding') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Logo and colors') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Customize') }}</p>
                </a>
                @endif

                @if($dashStaff)
                <a href="{{ tenant_url('staff') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Staff') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Manage staff accounts') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Manage') }}</p>
                </a>
                @endif

                @if($dashDomains)
                <a href="{{ tenant_url('domains') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Domains') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Custom hostname') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Attach your domain') }}</p>
                </a>
                @endif

                @if($dashPayment)
                <a href="{{ tenant_url('payment') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Payment') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Subscription portal') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Plan & billing') }}</p>
                </a>
                @endif
            </div>
        </section>
        @endif
    </div>
</x-tenant::app-layout>
