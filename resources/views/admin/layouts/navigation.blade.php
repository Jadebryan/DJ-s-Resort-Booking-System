@php
    $isActive = function (...$patterns) {
        foreach ($patterns as $p) {
            if (request()->routeIs($p)) return true;
        }
        return false;
    };
    $openMaintenanceCount = \App\Models\MaintenanceTicket::query()
        ->where('status', \App\Models\MaintenanceTicket::STATUS_OPEN)
        ->count();
@endphp

<aside id="admin-sidebar"
       class="fixed left-3 top-3 bottom-3 z-40 flex h-auto w-52 max-w-[min(100vw,13rem)] flex-col overflow-hidden rounded-2xl border border-white/50 bg-white/40 shadow-lg shadow-slate-900/[0.08] ring-1 ring-slate-900/[0.04] backdrop-blur-xl transition-all duration-200 ease-out -translate-x-full lg:translate-x-0"
       :class="{
           'translate-x-0': sidebarOpen,
           'lg:w-16 lg:max-w-none': sidebarCollapsed,
           'lg:w-52': !sidebarCollapsed
       }">
    {{-- Logo / brand --}}
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-gray-200/35 px-3"
         :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2" :class="{ 'lg:justify-center': sidebarCollapsed }">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-sm font-bold text-indigo-700">
                DJ
            </span>
            <span class="leading-tight" :class="{ 'lg:hidden': sidebarCollapsed }">
                <span class="block text-sm font-semibold text-gray-800">DJ's Superadmin</span>
                <span class="block text-[10px] uppercase tracking-wide text-gray-500">Resort control</span>
            </span>
        </a>
    </div>
    {{-- Collapse rail (desktop) — below logo --}}
    <button type="button"
            @click.stop="toggleSidebarRail()"
            class="hidden h-10 w-full shrink-0 items-center justify-center border-b border-gray-200/35 bg-indigo-500/[0.08] text-indigo-800 backdrop-blur-sm hover:bg-indigo-500/[0.14] lg:flex"
            :title="sidebarCollapsed ? @js(__('Expand sidebar')) : @js(__('Collapse sidebar'))"
            :aria-expanded="(!sidebarCollapsed).toString()"
            aria-controls="admin-sidebar"
            aria-label="{{ __('Toggle sidebar width') }}">
        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
    </button>

    {{-- Nav links (scroll hints only when overflow) --}}
    <div class="relative flex min-h-0 min-w-0 flex-1 flex-col" x-data="sidebarNavScroll()">
        <nav
            x-ref="panel"
            class="min-h-0 flex-1 overflow-y-auto scrollbar-none px-2 py-4 sm:px-3"
            @scroll.passive="measure()"
            @click="$parent.sidebarOpen = false">
        <ul class="space-y-0.5">
            <li>
                <a href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0H9"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Dashboard') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.tenants.index') }}" title="{{ __('Tenants') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.tenants.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20h6M3 20h5v-2a3 3 0 00-5.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm8 3a3 3 0 11-6 0 3 3 0 016 0zM7 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Tenants') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.tenant-registrations.index') }}" title="{{ __('Signups') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.tenant-registrations.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Signups') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.payments') }}" title="{{ __('Payments') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.payments') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Payments') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.maintenance') }}" title="{{ __('Maintenance') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.maintenance') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-2 1 3.25.75L11 24l1-3 .75-3.25L9.75 17zM6 2l6 6-2 2-6-6V2h2zm9 2l3 3-8 8-3-3 8-8z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Maintenance') }}</span>
                    @if($openMaintenanceCount > 0)
                        <span class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 shadow-sm ring-1 ring-amber-200"
                              :class="{ 'lg:hidden': sidebarCollapsed }"
                              title="{{ __('Open tickets') }}">
                            {{ min(99, (int) $openMaintenanceCount) }}
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports') }}" title="{{ __('Reports') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.reports') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V9a2 2 0 012-2h1a2 2 0 012 2v10M5 19v-4a2 2 0 012-2h1a2 2 0 012 2v4m8 0v-7a2 2 0 00-2-2h-1a2 2 0 00-2 2v7"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Reports') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.subscriptions.index') }}" title="{{ __('Subscriptions') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.subscriptions.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3.314 0-6 1.343-6 3v5h12v-5c0-1.657-2.686-3-6-3zm0 0V5m0 0l-2 2m2-2l2 2"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Subscriptions') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings') }}" title="{{ __('Settings') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.settings') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317L9.6 2.25 7.5 2.25l.75 2.067a7.963 7.963 0 00-2.231 1.29L4.1 4.75 3 6.85l1.823 1.012A7.963 7.963 0 004.5 12c0 .735.098 1.448.283 2.133L3 15.15l1.1 2.1 1.919-1.107A7.963 7.963 0 009 17.683L9.6 19.75h2.1l.675-2.067a7.963 7.963 0 002.231-1.29l1.919 1.107 1.1-2.1-1.783-1.017A7.963 7.963 0 0019.5 12c0-.735-.098-1.448-.283-2.133L21 8.85 19.9 6.75l-1.919 1.107a7.963 7.963 0 00-2.231-1.29L14.4 2.25h-2.1l-.675 2.067zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Settings') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.profile.edit') }}" title="{{ __('Profile') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('admin.profile.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Profile') }}</span>
                </a>
            </li>
        </ul>
        </nav>
        <div
            x-show="showTopFade"
            x-transition.opacity
            x-cloak
            class="pointer-events-none absolute inset-x-0 top-0 z-10 h-7 bg-gradient-to-b from-white/70 to-transparent"
            aria-hidden="true"></div>
        <div
            x-show="showBottomFade"
            x-transition.opacity
            x-cloak
            class="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex h-12 flex-col justify-end bg-gradient-to-t from-white/75 from-50% to-transparent pb-1"
            aria-hidden="true">
            <div class="flex justify-center">
                <span class="inline-flex items-center rounded-full bg-gray-100/95 px-2 py-0.5 shadow-sm ring-1 ring-gray-200/70" title="{{ __('More below') }}">
                    <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Log out at bottom --}}
    <div class="shrink-0 border-t border-gray-200/35 p-2 sm:p-3">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit"
                    title="{{ __('Log Out') }}"
                    class="flex w-full items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-white/45 hover:text-gray-900"
                    :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>
</aside>
