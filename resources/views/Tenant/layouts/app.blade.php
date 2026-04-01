<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @php
            $__tenantHead = request()->attributes->get('tenant');
        @endphp
        {{ $__tenantHead instanceof \App\Models\Tenant ? $__tenantHead->appDisplayName() : config('app.name', 'Laravel') }} | {{ __('Staff') }}
    </title>

    <script>
        try { if (localStorage.getItem('layout-rail-tenant-staff') === '1') document.documentElement.classList.add('rail-collapsed'); } catch (e) {}
    </script>

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 font-sans"
      x-data="dashboardShell('layout-rail-tenant-staff')">
<div class="flex min-h-screen overflow-x-hidden">

    {{-- Sidebar backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out" x-transition:leave="transition-opacity ease-in"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    {{-- Sidebar --}}
    @include('Tenant.layouts.navigation')

    {{-- Main content area (contentAttributes allow page-level Alpine x-data, e.g. for modals) --}}
    <div data-dashboard-main-rail class="flex min-w-0 flex-1 flex-col transition-[padding] duration-200 ease-out lg:pl-64"
         :class="{ 'lg:!pl-16': sidebarCollapsed }"
         {{ $contentAttributes ?? '' }}>
        @php
            $tenantCtx = request()->attributes->get('tenant');
            $isResortOwner = auth('tenant')->check() && auth('tenant')->user()->role === 'admin';
            $tenantBaseSearchItems = [
                ['label' => 'Dashboard', 'desc' => 'Overview and quick stats', 'url' => tenant_url('/dashboard'), 'keywords' => 'overview home'],
            ];
            if (tenant_staff_can('rooms', 'read')) {
                $tenantBaseSearchItems[] = ['label' => 'Rooms', 'desc' => 'Manage rooms and rates', 'url' => tenant_url('/rooms'), 'keywords' => 'accommodation prices capacity'];
            }
            if (tenant_staff_can('bookings', 'read')) {
                $tenantBaseSearchItems[] = ['label' => 'Bookings', 'desc' => 'View and manage reservations', 'url' => tenant_url('/bookings'), 'keywords' => 'reservations calendar'];
            }
            if (tenant_staff_can('reports', 'read')) {
                $tenantBaseSearchItems[] = ['label' => 'Reports', 'desc' => 'Reports and exports', 'url' => tenant_url('/reports'), 'keywords' => 'analytics export pdf csv'];
            }
            if (tenant_staff_can('payment', 'read')) {
                $tenantBaseSearchItems[] = ['label' => 'Payment', 'desc' => 'Subscription and billing', 'url' => tenant_url('/payment'), 'keywords' => 'billing subscription'];
            }
            if (tenant_staff_can('settings', 'read')) {
                $tenantBaseSearchItems[] = ['label' => 'Settings', 'desc' => 'Database, system updates, timezone, resort info', 'url' => tenant_url('/settings'), 'keywords' => 'configuration database storage version migrations'];
            }
            $tenantBaseSearchItems[] = ['label' => 'Profile', 'desc' => 'Your tenant admin profile', 'url' => tenant_url('/profile'), 'keywords' => 'account password'];
            $tenantAdminSearchItems = [];
            if (tenant_staff_can('branding', 'read')) {
                $tenantAdminSearchItems[] = ['label' => 'Branding', 'desc' => 'Logo, colors, and landing content', 'url' => tenant_url('/branding'), 'keywords' => 'logo theme colors'];
            }
            if (tenant_staff_can('staff', 'read')) {
                $tenantAdminSearchItems[] = ['label' => 'Staff', 'desc' => 'Manage staff and roles', 'url' => tenant_url('/staff'), 'keywords' => 'employees users'];
            }
            if ($isResortOwner || (tenant_rbac_ready() && tenant_staff_can('rbac', 'read'))) {
                $tenantAdminSearchItems[] = ['label' => 'Access control', 'desc' => 'Staff and guest permission sets', 'url' => tenant_url('/rbac'), 'keywords' => 'rbac roles permissions'];
            }
            if (tenant_staff_can('guests', 'read')) {
                $tenantAdminSearchItems[] = ['label' => 'Users', 'desc' => 'Registered customers and portal roles', 'url' => tenant_url('/users'), 'keywords' => 'customers guests accounts portal'];
            }
            if (tenant_staff_can('domains', 'read')) {
                $tenantAdminSearchItems[] = ['label' => 'Domains', 'desc' => 'Manage tenant domains', 'url' => tenant_url('/domains'), 'keywords' => 'hostname custom domain'];
            }
            $tenantSearchItems = array_values(array_merge($tenantBaseSearchItems, $tenantAdminSearchItems));

            $recentTenantNotifications = collect();
            $canOpenActivity = false;
            $activityRouteForAction = function (?string $action) use ($isResortOwner): string {
                if (! $action) {
                    return tenant_url('/activity');
                }

                return match (true) {
                    str_starts_with($action, 'booking.') => tenant_url('/bookings'),
                    str_starts_with($action, 'room.') => tenant_url('/rooms'),
                    str_starts_with($action, 'staff.') => tenant_staff_can('staff', 'read') ? tenant_url('/staff') : tenant_url('/dashboard'),
                    str_starts_with($action, 'domain.') => tenant_staff_can('domains', 'read') ? tenant_url('/domains') : tenant_url('/dashboard'),
                    str_starts_with($action, 'branding.') => tenant_staff_can('branding', 'read') ? tenant_url('/branding') : tenant_url('/dashboard'),
                    str_starts_with($action, 'rbac.') => ($isResortOwner || (tenant_rbac_ready() && tenant_staff_can('rbac', 'read'))) ? tenant_url('/rbac') : tenant_url('/dashboard'),
                    str_starts_with($action, 'guest.') => tenant_staff_can('guests', 'read') ? tenant_url('/users') : tenant_url('/dashboard'),
                    default => tenant_url('/activity'),
                };
            };
            $activityLabelForAction = function (?string $action): string {
                if (! $action) {
                    return 'Activity';
                }

                return match (true) {
                    str_starts_with($action, 'booking.') => 'Booking update',
                    str_starts_with($action, 'room.') => 'Room update',
                    str_starts_with($action, 'staff.') => 'Staff update',
                    str_starts_with($action, 'domain.') => 'Domain update',
                    str_starts_with($action, 'branding.') => 'Branding update',
                    default => 'Activity',
                };
            };
            if ($tenantCtx instanceof \App\Models\Tenant) {
                $plan = $tenantCtx->loadMissing('plan')->plan;
                $canOpenActivity = $plan && is_array($plan->features) && in_array('activity_logs', $plan->features, true);
            }

            if ($canOpenActivity && \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('activity_logs')) {
                try {
                    $recentTenantNotifications = \App\Models\ActivityLog::query()
                        ->latest('created_at')
                        ->limit(6)
                        ->get(['action', 'description', 'created_at']);
                } catch (\Throwable) {
                    $recentTenantNotifications = collect();
                }
            }

            if ($tenantCtx instanceof \App\Models\Tenant) {
                try {
                    $upgradeNotifications = \App\Models\TenantPlanUpgradeRequest::query()
                        ->where('tenant_id', $tenantCtx->id)
                        ->whereIn('status', ['pending', 'approved', 'rejected'])
                        ->latest('updated_at')
                        ->limit(3)
                        ->get()
                        ->map(function ($row) {
                            $when = $row->reviewed_at ?? $row->updated_at ?? $row->created_at;
                            $status = (string) $row->status;
                            $msg = match ($status) {
                                'pending' => 'Upgrade request is pending review.',
                                'approved' => 'Upgrade request was approved.',
                                'rejected' => 'Upgrade request was rejected.',
                                default => 'Upgrade request update.',
                            };

                            if ($status === 'rejected' && $row->review_notes) {
                                $msg .= ' Reason: ' . trim((string) $row->review_notes);
                            }

                            return (object) [
                                'action' => 'billing.upgrade.' . $status,
                                'description' => $msg,
                                'created_at' => $when,
                            ];
                        });

                    $recentTenantNotifications = $recentTenantNotifications
                        ->concat($upgradeNotifications)
                        ->sortByDesc('created_at')
                        ->take(8)
                        ->values();
                } catch (\Throwable) {
                    // keep existing activity list if central notification query fails
                }
            }

            $initialTenantNotificationItems = $recentTenantNotifications->map(function ($note) use ($activityRouteForAction) {
                return [
                    'action' => (string) ($note->action ?? ''),
                    'description' => (string) ($note->description ?: 'Activity update'),
                    'url' => $activityRouteForAction((string) ($note->action ?? '')),
                    'created_at' => optional($note->created_at)?->toIso8601String(),
                    'time_human' => optional($note->created_at)?->diffForHumans(),
                ];
            })->values();
        @endphp
        {{-- Top bar: menu button, page title, search, user --}}
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-gray-200/80 bg-white/95 px-4 sm:px-6 backdrop-blur supports-[backdrop-filter]:bg-white/80">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden" aria-label="{{ __('Open menu') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
    @isset($header)
                    <div class="min-w-0 text-gray-800">
                {{ $header }}
                    </div>
                @endisset
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <div class="hidden sm:block relative"
                     x-data="{
                        q: '',
                        open: false,
                        items: @js($tenantSearchItems),
                        get results() {
                            const term = (this.q || '').toLowerCase().trim();
                            if (!term) return this.items.slice(0, 6);
                            return this.items.filter((i) => (`${i.label} ${i.desc} ${i.keywords}`.toLowerCase()).includes(term)).slice(0, 8);
                        },
                        goFirstResult() {
                            const first = this.results[0];
                            if (first && first.url) window.location.href = first.url;
                        },
                     }"
                     @keydown.escape.window="open = false"
                     @click.outside="open = false">
                    <label for="tenant-global-search" class="sr-only">Search</label>
                    <input id="tenant-global-search"
                           type="search"
                           x-model="q"
                           @focus="open = true"
                           @input="open = true"
                           @keydown.enter.prevent="goFirstResult()"
                           placeholder="Search..."
                           class="h-9 w-56 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 placeholder-gray-500 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">

                    <div x-show="open"
                         x-cloak
                         x-transition:enter="ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-80 rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden z-50">
                        <template x-if="results.length === 0">
                            <p class="px-3 py-2 text-xs text-gray-500">No results</p>
                        </template>
                        <template x-for="item in results" :key="item.url">
                            <a :href="item.url" class="block px-3 py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                <p class="text-sm font-medium text-gray-800" x-text="item.label"></p>
                                <p class="text-xs text-gray-500" x-text="item.desc"></p>
                            </a>
                        </template>
                    </div>
                </div>

                <div class="relative"
                     x-data="{
                        open: false,
                        items: @js($initialTenantNotificationItems),
                        feedUrl: @js(tenant_url('/notifications/feed')),
                        seenKey: @js('tenant-admin-notif-seen-' . (auth('tenant')->id() ?? '0')),
                        seenAt: null,
                        intervalId: null,
                        get badgeCount() {
                            if (!this.seenAt) return Math.min(this.items.length, 9);
                            const seen = new Date(this.seenAt).getTime();
                            const unread = this.items.filter(i => i.created_at && new Date(i.created_at).getTime() > seen).length;
                            return Math.min(unread, 9);
                        },
                        markSeen() {
                            this.seenAt = new Date().toISOString();
                            localStorage.setItem(this.seenKey, this.seenAt);
                        },
                        async refresh() {
                            try {
                                const res = await fetch(this.feedUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                                if (!res.ok) return;
                                const data = await res.json();
                                if (Array.isArray(data.items)) this.items = data.items;
                            } catch (_) {}
                        },
                        init() {
                            this.seenAt = localStorage.getItem(this.seenKey);
                            this.refresh();
                            this.intervalId = setInterval(() => this.refresh(), 60000);
                        }
                     }"
                     @keydown.escape.window="open = false"
                     @click.outside="open = false"
                     x-init="init()">
                    <button type="button"
                            @click="open = !open; if (open) { refresh(); markSeen(); }"
                            class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <template x-if="badgeCount > 0">
                            <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-teal-500 px-1 text-[10px] font-semibold text-white">
                                <span x-text="badgeCount"></span>
                            </span>
                        </template>
                    </button>

                    <div x-show="open"
                         x-cloak
                         x-transition:enter="ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-80 rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden z-50">
                        <div class="border-b border-gray-100 px-3 py-2">
                            <p class="text-sm font-semibold text-gray-800">Notifications</p>
                        </div>
                        <template x-if="items.length === 0">
                            <p class="px-3 py-3 text-xs text-gray-500">No recent activity.</p>
                        </template>
                        <template x-if="items.length > 0">
                            <div class="max-h-72 overflow-y-auto">
                                <template x-for="note in items" :key="`${note.action}-${note.time_human}-${note.description}`">
                                    <a :href="note.url || '{{ tenant_url('/activity') }}'"
                                       class="block px-3 py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                        <p class="text-[11px] font-semibold text-teal-700" x-text="note.action && note.action.startsWith('billing.upgrade.') ? 'Billing update' : 'Activity update'"></p>
                                        <p class="text-xs font-medium text-gray-800" x-text="note.description || 'Activity update'"></p>
                                        <p class="mt-0.5 text-[11px] text-gray-500" x-text="note.time_human || 'Just now'"></p>
                                    </a>
                                </template>
                            </div>
                        </template>
                        <div class="border-t border-gray-100 px-3 py-2 text-right">
                            @if($canOpenActivity && tenant_staff_can('activity', 'read'))
                                <a href="{{ tenant_url('/activity') }}" class="text-xs font-medium text-teal-700 hover:text-teal-800">View all activity</a>
                            @else
                                <span class="text-xs text-gray-400">Activity logs not enabled in plan</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 pl-2 pr-3 py-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                        {{ strtoupper(substr(auth('tenant')->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="hidden text-sm font-medium text-gray-700 sm:inline">{{ auth('tenant')->user()->name }}</span>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            @include('Tenant.layouts.context-hints')
            {{ $slot }}
        </main>
</div>
</div>
@include('components.toast-container')
{{-- Ensure Tailwind keeps sidebar width / padding utilities used only in Alpine :class --}}
<span class="pointer-events-none hidden lg:!pl-16 lg:pl-64 lg:w-16 lg:w-64 lg:max-w-none" aria-hidden="true"></span>
@livewireScripts
</body>
</html>
