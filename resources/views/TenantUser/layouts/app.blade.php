@php
    $tenant = current_tenant();
    $siteName = $tenant instanceof \App\Models\Tenant ? $tenant->appDisplayName() : config('app.name', 'Resort');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $siteName }} | Guest</title>

    <script>
        try { if (localStorage.getItem('layout-rail-tenant-user') === '1') document.documentElement.classList.add('rail-collapsed'); } catch (e) {}
    </script>

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 font-sans"
      x-data="dashboardShell('layout-rail-tenant-user')">
<div class="flex min-h-screen overflow-x-hidden">

    {{-- Sidebar backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out" x-transition:leave="transition-opacity ease-in"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    {{-- Sidebar --}}
    @include('TenantUser.layouts.navigation')

    {{-- Main content area --}}
    <div class="flex min-w-0 flex-1 flex-col transition-[padding] duration-200 ease-out lg:pl-64"
         :class="{ 'lg:!pl-16': sidebarCollapsed }"
         {{ $contentAttributes ?? '' }}>
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-3 border-b border-gray-200/80 bg-white/95 px-4 sm:px-6 backdrop-blur supports-[backdrop-filter]:bg-white/80">
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
            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <div class="relative"
                 x-data="{
                    open: false,
                    items: [],
                    feedUrl: @js(route('tenant.user.notifications.feed')),
                    seenKey: @js('tenant-user-notif-seen-' . (auth('regular_user')->id() ?? '0')),
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
                        <p class="px-3 py-3 text-xs text-gray-500">No recent booking updates.</p>
                    </template>
                    <template x-if="items.length > 0">
                        <div class="max-h-72 overflow-y-auto">
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
            <div class="flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 pl-2 pr-3 py-1.5 min-w-0 max-w-[10rem] sm:max-w-none">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                    {{ strtoupper(substr(auth('regular_user')->user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="hidden min-w-0 truncate text-sm font-medium text-gray-700 sm:inline">{{ auth('regular_user')->user()->name }}</span>
            </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            @include('TenantUser.layouts.context-hints')
            {{ $slot }}
        </main>
    </div>
</div>
@include('components.toast-container')
<span class="pointer-events-none hidden lg:!pl-16 lg:pl-64 lg:w-16 lg:w-64 lg:max-w-none" aria-hidden="true"></span>
@livewireScripts
</body>
</html>
