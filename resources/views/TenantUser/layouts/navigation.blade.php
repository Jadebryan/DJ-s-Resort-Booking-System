@php
    $tenant = current_tenant();
    $siteName = $tenant instanceof \App\Models\Tenant ? $tenant->appDisplayName() : config('app.name', 'Resort');
    $url = fn (string $path) => tenant_url($path);
    $isActive = function (...$patterns) {
        foreach ($patterns as $p) {
            if (request()->routeIs($p)) return true;
        }
        return false;
    };
@endphp
<aside id="tenant-user-sidebar"
       class="fixed left-0 top-0 z-40 flex h-screen w-64 max-w-[min(100vw,16rem)] flex-col border-r border-gray-200/80 bg-white shadow-lg transition-all duration-200 ease-out -translate-x-full lg:translate-x-0"
       :class="{
           'translate-x-0': sidebarOpen,
           'lg:w-16 lg:max-w-none': sidebarCollapsed,
           'lg:w-64': !sidebarCollapsed
       }">
    {{-- Logo / brand --}}
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-gray-100 px-3"
         :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
        <a href="{{ $url('/user/dashboard') }}" class="flex min-w-0 items-center gap-2" :class="{ 'lg:justify-center': sidebarCollapsed }">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-sm font-semibold text-teal-600">
                {{ strtoupper(substr($siteName, 0, 2)) }}
            </div>
            <span class="truncate text-lg font-semibold text-gray-800"
                  :class="{ 'lg:hidden': sidebarCollapsed }">{{ $siteName }}</span>
        </a>
    </div>
    {{-- Collapse rail (desktop) — below logo --}}
    <button type="button"
            @click.stop="toggleSidebarRail()"
            class="hidden h-10 w-full shrink-0 items-center justify-center border-b border-gray-100 bg-teal-50/60 text-teal-800 hover:bg-teal-100/80 lg:flex"
            :title="sidebarCollapsed ? @js(__('Expand sidebar')) : @js(__('Collapse sidebar'))"
            :aria-expanded="(!sidebarCollapsed).toString()"
            aria-controls="tenant-user-sidebar"
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
                <a href="{{ $url('/user/dashboard') }}" title="{{ __('Dashboard') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.user.dashboard') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Dashboard') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ $url('/user/bookings') }}" title="{{ __('My Bookings') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.user.bookings.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('My Bookings') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ $url('/user/profile') }}" title="{{ __('Profile') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.user.profile.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
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
            class="pointer-events-none absolute inset-x-0 top-0 z-10 h-7 bg-gradient-to-b from-white to-transparent"
            aria-hidden="true"></div>
        <div
            x-show="showBottomFade"
            x-transition.opacity
            x-cloak
            class="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex h-12 flex-col justify-end bg-gradient-to-t from-white from-50% to-transparent pb-1"
            aria-hidden="true">
            <div class="flex justify-center">
                <span class="inline-flex items-center rounded-full bg-gray-100/95 px-2 py-0.5 shadow-sm ring-1 ring-gray-200/70" title="{{ __('More below') }}">
                    <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Log out at bottom --}}
    <div class="shrink-0 border-t border-gray-100 p-2 sm:p-3">
        <form method="POST" action="{{ $url('/user/logout') }}">
            @csrf
            <button type="submit"
                    title="{{ __('Log Out') }}"
                    class="flex w-full items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                    :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>
</aside>
