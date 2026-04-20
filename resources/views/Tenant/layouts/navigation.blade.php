{{-- Sidebar navigation (no topbar) --}}
@php
    $url = fn (string $path) => tenant_url($path);
    $isActive = function (...$patterns) {
        foreach ($patterns as $p) {
            if (request()->routeIs($p)) return true;
        }
        return false;
    };
    $tenantCtx = request()->attributes->get('tenant');
    $openSupportCount = 0;
    if ($tenantCtx instanceof \App\Models\Tenant) {
        $prefix = 'tenant#' . $tenantCtx->id . ' ';
        $openSupportCount = \App\Models\MaintenanceTicket::query()
            ->where('status', \App\Models\MaintenanceTicket::STATUS_OPEN)
            ->where('related_tenant', 'like', $prefix . '%')
            ->count();
    }
    $tenantName = $tenantCtx instanceof \App\Models\Tenant ? $tenantCtx->appDisplayName() : config('app.name', 'Resort');
    $tenantLogoPath = $tenantCtx?->logo_path;
    $tenantInitials = collect(preg_split('/\s+/', trim((string) $tenantName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    if ($tenantInitials === '') {
        $tenantInitials = 'R';
    }
    $isResortOwner = auth('tenant')->check() && auth('tenant')->user()->role === 'admin';
@endphp
<aside id="tenant-staff-sidebar"
       class="fixed left-3 top-3 bottom-3 z-40 flex h-auto w-52 max-w-[min(100vw,13rem)] flex-col overflow-hidden rounded-2xl border border-white/50 bg-white/40 shadow-lg shadow-slate-900/[0.08] ring-1 ring-slate-900/[0.04] backdrop-blur-xl transition-all duration-200 ease-out -translate-x-full lg:translate-x-0"
       :class="{
           'translate-x-0': sidebarOpen,
           'lg:w-16 lg:max-w-none': sidebarCollapsed,
           'lg:w-52': !sidebarCollapsed
       }">
    {{-- Logo / brand --}}
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-gray-200/35 px-3"
         :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
        <a href="{{ $url('/dashboard') }}" class="flex min-w-0 items-center gap-2" :class="{ 'lg:justify-center': sidebarCollapsed }">
            @if($tenantLogoPath)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($tenantLogoPath) }}"
                     alt="{{ $tenantName }} logo"
                     class="h-8 w-8 shrink-0 rounded-lg border border-gray-200 bg-white object-cover">
            @else
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-[11px] font-bold tracking-wide text-teal-700">
                    {{ $tenantInitials }}
                </span>
            @endif
            <span class="truncate text-sm font-semibold text-gray-800"
                  :class="{ 'lg:hidden': sidebarCollapsed }">{{ $tenantName }}</span>
        </a>
    </div>
    {{-- Collapse rail (desktop) — below logo --}}
    <button type="button"
            @click.stop="toggleSidebarRail()"
            class="hidden h-10 w-full shrink-0 items-center justify-center border-b border-gray-200/35 bg-white/30 text-teal-800 backdrop-blur-sm hover:bg-white/45 lg:flex"
            :title="sidebarCollapsed ? @js(__('Expand sidebar')) : @js(__('Collapse sidebar'))"
            :aria-expanded="(!sidebarCollapsed).toString()"
            aria-controls="tenant-staff-sidebar"
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
            @if(tenant_staff_can('dashboard', 'read'))
            <li>
                <a href="{{ $url('/dashboard') }}" title="{{ __('Dashboard') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.dashboard') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Dashboard') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('rooms', 'read'))
            <li>
                <a href="{{ $url('/rooms') }}" title="{{ __('Rooms') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.rooms.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Rooms') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('bookings', 'read'))
            <li>
                <a href="{{ $url('/bookings') }}" title="{{ __('Bookings') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.bookings.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Bookings') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('reports', 'read'))
            <li>
                <a href="{{ $url('/reports') }}" title="{{ __('Reports') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.reports.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Reports') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('branding', 'read'))
            <li>
                <a href="{{ $url('/branding') }}" title="{{ __('Branding') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.branding.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Branding') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('staff', 'read'))
            <li>
                <a href="{{ $url('/staff') }}" title="{{ __('Staff') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.staff.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Staff') }}</span>
                </a>
            </li>
            @endif
            @if($isResortOwner || (tenant_rbac_ready() && tenant_staff_can('rbac', 'read')))
            <li>
                <a href="{{ $url('/rbac') }}" title="{{ __('Access control') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.rbac.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Access control') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('guests', 'read'))
            <li>
                <a href="{{ $url('/users') }}" title="{{ __('Users') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.users.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Users') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('domains', 'read'))
            <li>
                <a href="{{ $url('/domains') }}" title="{{ __('Domains') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.domains.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Domains') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('payment', 'read'))
            <li>
                <a href="{{ $url('/payment') }}" title="{{ __('Payment') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.payment.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Payment') }}</span>
                </a>
            </li>
            @endif
            @if(tenant_staff_can('support', 'read'))
            <li>
                <a href="{{ $url('/support') }}" title="{{ __('Support') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.support.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414m0 0A8.003 8.003 0 005.05 16.95m11.9-9.9L15.536 8.464m0 0A5 5 0 018.464 15.536m7.072-7.072L12 12m0 0l-1.5 4.5L12 12zm0 0l4.5-1.5L12 12z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Support') }}</span>
                    @if($openSupportCount > 0)
                        <span class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 shadow-sm ring-1 ring-amber-200"
                              :class="{ 'lg:hidden': sidebarCollapsed }"
                              title="{{ __('Open tickets') }}">
                            {{ min(99, (int) $openSupportCount) }}
                        </span>
                    @endif
                </a>
            </li>
            @endif
            @if(tenant_staff_can('settings', 'read'))
            <li>
                <a href="{{ $url('/settings') }}" title="{{ __('Settings') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.settings.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
                   :class="{ 'lg:justify-center lg:px-2': sidebarCollapsed }">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ __('Settings') }}</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ $url('/profile') }}" title="{{ __('Profile') }}"
                   class="flex items-center gap-3 rounded-r-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive('tenant.profile.*') ? 'bg-teal-500 text-white shadow-sm' : 'text-gray-600 hover:bg-white/45 hover:text-gray-900' }}"
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
        <form method="POST" action="{{ $url('/logout') }}">
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
