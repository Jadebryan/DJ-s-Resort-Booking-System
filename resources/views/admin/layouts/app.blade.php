<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | Admin</title>

    <script>
        try { if (localStorage.getItem('layout-rail-admin') === '1') document.documentElement.classList.add('rail-collapsed'); } catch (e) {}
    </script>

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 font-sans"
      x-data="dashboardShell('layout-rail-admin')">
<div class="flex min-h-screen overflow-x-hidden">

    {{-- Sidebar backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out" x-transition:leave="transition-opacity ease-in"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    {{-- Sidebar --}}
    @include('admin.layouts.navigation')

    {{-- Main content area --}}
    <div data-dashboard-main-rail class="flex min-w-0 flex-1 flex-col transition-[padding] duration-200 ease-out lg:pl-64"
         :class="{ 'lg:!pl-16': sidebarCollapsed }">
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
                        items: @js([
                            ['label' => 'Dashboard', 'desc' => 'Overview and quick actions', 'url' => route('admin.dashboard'), 'keywords' => 'home overview stats'],
                            ['label' => 'Tenants', 'desc' => 'Manage resorts and domains', 'url' => route('admin.tenants.index'), 'keywords' => 'resorts domains plans'],
                            ['label' => 'Signups', 'desc' => 'Review tenant registration requests', 'url' => route('admin.tenant-registrations.index'), 'keywords' => 'applications approvals'],
                            ['label' => 'Payments', 'desc' => 'Track subscription payments', 'url' => route('admin.payments'), 'keywords' => 'billing invoices'],
                            ['label' => 'Maintenance', 'desc' => 'Maintenance board and tickets', 'url' => route('admin.maintenance'), 'keywords' => 'tasks incidents support'],
                            ['label' => 'Reports', 'desc' => 'Plan and tenant metrics', 'url' => route('admin.reports'), 'keywords' => 'analytics export'],
                            ['label' => 'Subscriptions', 'desc' => 'Edit plans and features', 'url' => route('admin.subscriptions.index'), 'keywords' => 'plans features pricing'],
                            ['label' => 'Settings', 'desc' => 'Platform-wide configuration', 'url' => route('admin.settings'), 'keywords' => 'configuration system'],
                            ['label' => 'Profile', 'desc' => 'Your superadmin account', 'url' => route('admin.profile.edit'), 'keywords' => 'account password'],
                        ]),
                        get results() {
                            const term = (this.q || '').toLowerCase().trim();
                            if (!term) return this.items.slice(0, 6);
                            return this.items.filter((i) => {
                                const hay = `${i.label} ${i.desc} ${i.keywords}`.toLowerCase();
                                return hay.includes(term);
                            }).slice(0, 8);
                        },
                        updateGlobalFilter() {
                            window.dispatchEvent(new CustomEvent('admin-global-search', {
                                detail: (this.q || '').toLowerCase().trim(),
                            }));
                        },
                        goFirstResult() {
                            const first = this.results[0];
                            if (first && first.url) window.location.href = first.url;
                        },
                     }"
                     @keydown.escape.window="open = false"
                     @click.outside="open = false">
                    <label for="admin-search" class="sr-only">Search</label>
                    <input id="admin-search"
                           type="search"
                           x-model="q"
                           @focus="open = true"
                           @input="open = true; updateGlobalFilter()"
                           @keydown.enter.prevent="goFirstResult()"
                           placeholder="Search..."
                           autocomplete="off"
                           class="h-9 w-56 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">

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
                            <a :href="item.url"
                               class="block px-3 py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                <p class="text-sm font-medium text-gray-800" x-text="item.label"></p>
                                <p class="text-xs text-gray-500" x-text="item.desc"></p>
                            </a>
                        </template>
                    </div>
                </div>
                <div class="relative"
                     x-data="{
                        open: false,
                        items: [],
                        feedUrl: @js(route('admin.notifications.feed')),
                        seenKey: @js('admin-notif-seen-' . (auth('admin')->id() ?? '0')),
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
                     x-init="init()"
                     @keydown.escape.window="open = false"
                     @click.outside="open = false">
                    <button type="button"
                            @click="open = !open; if (open) { refresh(); markSeen(); }"
                            class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <template x-if="badgeCount > 0">
                            <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-indigo-500 px-1 text-[10px] font-semibold text-white">
                                <span x-text="badgeCount"></span>
                            </span>
                        </template>
                    </button>
                    <div x-show="open"
                         x-cloak
                         x-transition:enter="ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-96 rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden z-50">
                        <div class="border-b border-gray-100 px-3 py-2">
                            <p class="text-sm font-semibold text-gray-800">Notifications</p>
                        </div>
                        <template x-if="items.length === 0">
                            <p class="px-3 py-3 text-xs text-gray-500">No recent updates.</p>
                        </template>
                        <template x-if="items.length > 0">
                            <div class="max-h-80 overflow-y-auto">
                                <template x-for="item in items" :key="`${item.kind}-${item.created_at}-${item.description}`">
                                    <a :href="item.url || '#'" class="block px-3 py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                        <p class="text-xs font-medium text-gray-800" x-text="item.description"></p>
                                        <p class="mt-0.5 text-[11px] text-gray-500" x-text="item.time_human || 'Just now'"></p>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 pl-2 pr-3 py-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        {{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="hidden text-sm font-medium text-gray-700 sm:inline">{{ auth('admin')->user()->name }}</span>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="min-w-0 flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6">
            @include('admin.layouts.context-hints')
            {{ $slot }}
        </main>
    </div>
</div>
@include('components.toast-container')
<span class="pointer-events-none hidden lg:!pl-16 lg:pl-64 lg:w-16 lg:w-64 lg:max-w-none" aria-hidden="true"></span>
@livewireScripts
</body>
</html>