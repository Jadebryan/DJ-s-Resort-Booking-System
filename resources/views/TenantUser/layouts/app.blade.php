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
    @php
        $themePrimary = $tenant instanceof \App\Models\Tenant ? (string) ($tenant->primary_color ?? '') : '';
        $themeSecondary = $tenant instanceof \App\Models\Tenant ? (string) ($tenant->secondary_color ?? '') : '';
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
      x-data="dashboardShell('layout-rail-tenant-user')">
<div class="flex h-[100dvh] min-h-0 overflow-hidden">

    {{-- Sidebar backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out" x-transition:leave="transition-opacity ease-in"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    {{-- Sidebar --}}
    @include('TenantUser.layouts.navigation')

    {{-- Main content area --}}
    <div data-dashboard-main-rail class="flex min-h-0 min-w-0 flex-1 flex-col transition-[padding] duration-200 ease-out lg:pl-[calc(0.75rem+13rem+0.75rem)]"
         :class="{ 'lg:!pl-20': sidebarCollapsed }"
         {{ $contentAttributes ?? '' }}>
        {{-- Top bar: stays visible; only <main> scrolls below --}}
        <header class="z-30 mx-3 mt-3 flex h-16 shrink-0 items-center justify-between gap-3 rounded-2xl border border-white/50 bg-white/40 px-4 shadow-lg shadow-slate-900/[0.07] ring-1 ring-slate-900/[0.04] backdrop-blur-xl sm:mx-4 sm:px-6">
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
            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @include('TenantUser.layouts.context-help')
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
            <div class="flex shrink-0 items-center gap-2 rounded-xl border border-gray-200/60 bg-white/55 pl-2 pr-3 py-1.5 shadow-sm backdrop-blur-sm min-w-0 max-w-[10rem] sm:max-w-none">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                    {{ strtoupper(substr(auth('regular_user')->user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="hidden min-w-0 truncate text-sm font-medium text-gray-700 sm:inline">{{ auth('regular_user')->user()->name }}</span>
            </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="min-h-0 min-w-0 flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            @include('TenantUser.layouts.context-hints')
            {{ $slot }}
        </main>
    </div>
</div>
@include('components.toast-container')
<span class="pointer-events-none hidden lg:!pl-20 lg:pl-[calc(0.75rem+13rem+0.75rem)] lg:w-16 lg:w-52 lg:max-w-none" aria-hidden="true"></span>
@livewireScripts
</body>
</html>
