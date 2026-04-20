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
    @php
        $tenantTheme = request()->attributes->get('tenant');
        $themePrimary = $tenantTheme instanceof \App\Models\Tenant ? (string) ($tenantTheme->primary_color ?? '') : '';
        $themeSecondary = $tenantTheme instanceof \App\Models\Tenant ? (string) ($tenantTheme->secondary_color ?? '') : '';
        $themePrimary = preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $themePrimary) ? $themePrimary : '#0f766e';
        $themeSecondary = preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $themeSecondary) ? $themeSecondary : '#115e59';
        if (strlen($themePrimary) === 4) {
            $themePrimary = '#' . $themePrimary[1] . $themePrimary[1] . $themePrimary[2] . $themePrimary[2] . $themePrimary[3] . $themePrimary[3];
        }
        if (strlen($themeSecondary) === 4) {
            $themeSecondary = '#' . $themeSecondary[1] . $themeSecondary[1] . $themeSecondary[2] . $themeSecondary[2] . $themeSecondary[3] . $themeSecondary[3];
        }
        [$pr, $pg, $pb] = sscanf($themePrimary, '#%02x%02x%02x');
        [$sr, $sg, $sb] = sscanf($themeSecondary, '#%02x%02x%02x');
    @endphp
    <style>
        .tenant-themed {
            --tenant-primary: {{ $themePrimary }};
            --tenant-secondary: {{ $themeSecondary }};
            --tenant-primary-rgb: {{ (int) $pr }}, {{ (int) $pg }}, {{ (int) $pb }};
            --tenant-secondary-rgb: {{ (int) $sr }}, {{ (int) $sg }}, {{ (int) $sb }};
        }
        .tenant-themed .bg-teal-500 { background-color: var(--tenant-primary) !important; }
        .tenant-themed .bg-teal-600 { background-color: var(--tenant-primary) !important; }
        .tenant-themed .hover\:bg-teal-700:hover { background-color: var(--tenant-secondary) !important; }
        .tenant-themed .bg-teal-100 { background-color: rgba(var(--tenant-primary-rgb), 0.14) !important; }
        .tenant-themed .bg-teal-50 { background-color: rgba(var(--tenant-primary-rgb), 0.08) !important; }
        .tenant-themed .bg-teal-100\/80 { background-color: rgba(var(--tenant-primary-rgb), 0.16) !important; }
        .tenant-themed .bg-teal-50\/60 { background-color: rgba(var(--tenant-primary-rgb), 0.10) !important; }
        .tenant-themed .text-teal-600,
        .tenant-themed .text-teal-700,
        .tenant-themed .text-teal-800 { color: var(--tenant-primary) !important; }
        .tenant-themed .border-teal-200,
        .tenant-themed .border-teal-100,
        .tenant-themed .border-teal-500 { border-color: rgba(var(--tenant-primary-rgb), 0.45) !important; }
        .tenant-themed .ring-teal-200 { --tw-ring-color: rgba(var(--tenant-primary-rgb), 0.35) !important; }
        .tenant-themed .focus\:ring-teal-500:focus { --tw-ring-color: rgba(var(--tenant-primary-rgb), 0.45) !important; }
        .tenant-themed .focus\:border-teal-500:focus { border-color: var(--tenant-primary) !important; }
        .tenant-themed .accent-teal-600 { accent-color: var(--tenant-primary) !important; }
        .tenant-themed .hover\:bg-teal-50:hover { background-color: rgba(var(--tenant-primary-rgb), 0.10) !important; }
        .tenant-themed .hover\:bg-teal-100\/80:hover { background-color: rgba(var(--tenant-primary-rgb), 0.18) !important; }
        .tenant-themed .hover\:text-teal-700:hover { color: var(--tenant-primary) !important; }
        .tenant-themed .from-teal-50\/90 {
            --tw-gradient-from: rgba(var(--tenant-primary-rgb), 0.10) var(--tw-gradient-from-position) !important;
            --tw-gradient-to: rgba(var(--tenant-primary-rgb), 0) var(--tw-gradient-to-position) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }
    </style>
</head>
<body class="tenant-themed h-[100dvh] overflow-hidden antialiased bg-gray-50 font-sans"
      x-data="dashboardShell('layout-rail-tenant-staff')">
<div class="flex h-[100dvh] min-h-0 overflow-hidden">

    {{-- Sidebar backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out" x-transition:leave="transition-opacity ease-in"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    {{-- Sidebar --}}
    @include('Tenant.layouts.navigation')

    {{-- Main content area (contentAttributes allow page-level Alpine x-data, e.g. for modals) --}}
    <div data-dashboard-main-rail class="flex min-h-0 min-w-0 flex-1 flex-col transition-[padding] duration-200 ease-out lg:pl-[calc(0.75rem+13rem+0.75rem)]"
         :class="{ 'lg:!pl-20': sidebarCollapsed }"
         {{ $contentAttributes ?? '' }}>
        @php
            $tenantCtx = request()->attributes->get('tenant');
            $isResortOwner = auth('tenant')->check() && auth('tenant')->user()->role === 'admin';
            $tenantBaseSearchItems = [];
            if (tenant_staff_can('dashboard', 'read')) {
                $tenantBaseSearchItems[] = ['label' => 'Dashboard', 'desc' => 'Overview and quick stats', 'url' => tenant_url('/dashboard'), 'keywords' => 'overview home'];
            }
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
                    default => tenant_staff_can('activity', 'read') ? tenant_url('/activity') : tenant_url('/dashboard'),
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

            // Determine if a schema update is available for this resort (tenant admin only).
            $settingsUpdateAvailable = false;
            $latestVersionForBubble = '1.0.0';
            if ($isResortOwner && $tenantCtx instanceof \App\Models\Tenant) {
                try {
                    $currentVersion = (string) (\App\Models\Tenant::query()->whereKey($tenantCtx->id)->value('version') ?? ($tenantCtx->version ?? '1.0.0'));
                    $latestVersionForBubble = (string) app(\App\Services\PlatformReleaseVersionService::class)->latestSchemaVersion();
                    $settingsUpdateAvailable = version_compare($currentVersion !== '' ? $currentVersion : '1.0.0', $latestVersionForBubble !== '' ? $latestVersionForBubble : '1.0.0', '<');
                } catch (\Throwable) {
                    $settingsUpdateAvailable = false;
                    $latestVersionForBubble = '1.0.0';
                }
            }
        @endphp
        {{-- Top bar: stays visible; only <main> scrolls below --}}
        <header class="z-30 mx-3 mt-3 flex h-16 shrink-0 items-center justify-between gap-4 rounded-2xl border border-white/50 bg-white/40 px-4 shadow-lg shadow-slate-900/[0.07] ring-1 ring-slate-900/[0.04] backdrop-blur-xl sm:mx-4 sm:px-6">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-gray-600 hover:bg-white/50 lg:hidden" aria-label="{{ __('Open menu') }}">
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
                           class="h-9 w-56 rounded-xl border border-gray-200/60 bg-white/55 px-3 text-sm text-gray-800 placeholder-gray-500 shadow-sm backdrop-blur-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/25">

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

                @include('Tenant.layouts.context-help')

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
                            class="relative rounded-xl p-2 text-gray-500 transition hover:bg-white/50 hover:text-gray-700 hover:shadow-sm"
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
                <div class="flex items-center gap-2 rounded-xl border border-gray-200/60 bg-white/55 pl-2 pr-3 py-1.5 shadow-sm backdrop-blur-sm">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                        {{ strtoupper(substr(auth('tenant')->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="hidden text-sm font-medium text-gray-700 sm:inline">{{ auth('tenant')->user()->name }}</span>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="min-h-0 min-w-0 flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            @include('Tenant.layouts.context-hints')
            @php
                $tenantSubBanner = request()->attributes->get('tenant');
                $showSubscriptionExpiredBanner = auth('tenant')->check()
                    && $tenantSubBanner instanceof \App\Models\Tenant
                    && $tenantSubBanner->is_active
                    && $tenantSubBanner->subscriptionIsExpired();
            @endphp
            @if($showSubscriptionExpiredBanner)
                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 shadow-sm ring-1 ring-amber-100 sm:px-5" role="status">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-amber-950">
                            <span class="font-semibold">{{ __('Subscription ended.') }}</span>
                            {{ __('Submit a renewal so platform admin can extend your plan. Renewals appear as “Renewal” in their queue when you stay on the same plan.') }}
                        </p>
                        @if(tenant_staff_can('payment', 'read'))
                            <a href="{{ tenant_url('/payment') }}#subscription-renew"
                               class="inline-flex shrink-0 items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-700 sm:text-sm">
                                {{ __('Renew subscription') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            {{ $slot }}
        </main>

        @if($settingsUpdateAvailable)
            <div
                x-cloak
                x-data="{
                    dismissed: false,
                    key: @js('tenant-settings-update-bubble-dismissed-' . (auth('tenant')->id() ?? 0) . '-' . ($latestVersionForBubble ?? '1.0.0')),
                    init() {
                        try { this.dismissed = sessionStorage.getItem(this.key) === '1' } catch (e) {}
                    },
                    dismiss() {
                        this.dismissed = true;
                        try { sessionStorage.setItem(this.key, '1') } catch (e) {}
                    }
                }"
                x-init="init()"
                x-show="!dismissed"
                class="fixed bottom-4 right-4 z-40 sm:bottom-6 sm:right-6">
                <div class="relative w-[320px] max-w-[calc(100vw-2rem)] rounded-xl border border-teal-200 bg-white px-4 py-3 shadow-lg ring-1 ring-teal-200/60">
                    <button
                        type="button"
                        @click="dismiss()"
                        class="absolute right-2 top-2 inline-flex h-6 w-6 items-center justify-center rounded-md text-slate-400 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/30"
                        aria-label="{{ __('Dismiss update notification') }}"
                        title="{{ __('Dismiss') }}">
                        <span class="text-base font-semibold leading-none">×</span>
                    </button>

                    <p class="font-semibold text-teal-800">{{ __('New update available') }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-600">{{ __('Apply the latest database changes from Settings → System updates.') }}</p>

                    <a
                        href="{{ tenant_url('/settings') }}"
                        class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-500/30">
                        {{ __('Go to Settings') }}
                    </a>
                </div>
            </div>
        @endif
</div>
</div>
@include('components.toast-container')
{{-- Ensure Tailwind keeps sidebar width / padding utilities used only in Alpine :class --}}
<span class="pointer-events-none hidden lg:!pl-20 lg:pl-[calc(0.75rem+13rem+0.75rem)] lg:w-16 lg:w-52 lg:max-w-none" aria-hidden="true"></span>
@livewireScripts
</body>
</html>
