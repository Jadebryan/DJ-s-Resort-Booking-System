<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Superadmin overview') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Monitor tenants, plans, payments, and platform health in one place.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1 text-left"
         x-data="{ dashFilter: '' }">
        <x-stat-kpi-toggle storage-key="mtrbs.admin.dashboard.kpi.hidden" grid-class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5" accent="indigo">
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">{{ __('Total tenants') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-indigo-950">{{ $tenantCount ?? '—' }}</p>
                <p class="mt-1 text-xs text-indigo-900/70">{{ ($activeTenantCount ?? 0) }} {{ __('active') }}</p>
            </div>
            <div class="rounded-xl border border-violet-100 bg-violet-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-violet-800/90">{{ __('Active plans') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-violet-950">{{ $planCount ?? '—' }}</p>
                <p class="mt-1 text-xs text-violet-900/70">{{ __('Subscription tiers') }}</p>
            </div>
            <div class="rounded-xl border border-sky-100 bg-sky-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-sky-800/90">{{ __('Custom domains') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-sky-950">{{ $domainCount ?? '—' }}</p>
                <p class="mt-1 text-xs text-sky-900/70">{{ __('Mapped hostnames') }}</p>
            </div>
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('Open signups') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $openSignupCount ?? 0 }}</p>
                <a href="{{ route('admin.tenant-registrations.index') }}" class="mt-1 inline-flex text-xs font-semibold text-amber-900 hover:text-amber-950">{{ __('Review applications →') }}</a>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 shadow-sm sm:col-span-2 lg:col-span-1">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Shortcuts') }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ route('admin.tenants.index') }}" class="rounded-lg bg-white/80 px-2 py-1 text-[11px] font-medium text-emerald-900 ring-1 ring-emerald-200/80 hover:bg-white">{{ __('Tenants') }}</a>
                    <a href="{{ route('admin.payments') }}" class="rounded-lg bg-white/80 px-2 py-1 text-[11px] font-medium text-emerald-900 ring-1 ring-emerald-200/80 hover:bg-white">{{ __('Payments') }}</a>
                    <a href="{{ route('admin.reports') }}" class="rounded-lg bg-white/80 px-2 py-1 text-[11px] font-medium text-emerald-900 ring-1 ring-emerald-200/80 hover:bg-white">{{ __('Reports') }}</a>
                </div>
            </div>
        </x-stat-kpi-toggle>

        <section class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.3fr)]">
            <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-4 py-3 sm:px-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('Quick actions') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Common operations you’ll use every day.') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-5">
                    <a href="{{ route('admin.tenants.index') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20h6M3 20h5v-2a3 3 0 00-5.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ __('Tenants') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Create, edit, and remove tenant accounts.') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.tenant-registrations.index') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700 group-hover:bg-amber-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-amber-800">{{ __('Signups') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Approve new resort applications.') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.payments') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ __('Payments') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Review upgrades and renewals.') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.reports') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V9a2 2 0 012-2h1a2 2 0 012 2v10M5 19v-4a2 2 0 012-2h1a2 2 0 012 2v4m8 0v-7a2 2 0 00-2-2h-1a2 2 0 00-2 2v7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ __('Reports') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Platform- and tenant-level analytics.') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.subscriptions.index') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3.314 0-6 1.343-6 3v5h12v-5c0-1.657-2.686-3-6-3zm0 0V5m0 0l-2 2m2-2l2 2"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ __('Subscriptions') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Manage plans, features, and pricing.') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.maintenance') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-2 1 3.25.75L11 24l1-3 .75-3.25L9.75 17zM6 2l6 6-2 2-6-6V2h2z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ __('Maintenance') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Track internal tasks and incidents.') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.settings') }}"
                       class="group flex flex-col rounded-xl border border-gray-200/80 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-200 hover:bg-white hover:shadow sm:col-span-2 xl:col-span-1">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317L9.6 2.25 7.5 2.25l.75 2.067a7.963 7.963 0 00-2.231 1.29L4.1 4.75 3 6.85l1.823 1.012A7.963 7.963 0 004.5 12c0 .735.098 1.448.283 2.133L3 15.15l1.1 2.1 1.919-1.107A7.963 7.963 0 009 17.683L9.6 19.75h2.1l.675-2.067a7.963 7.963 0 002.231-1.29l1.919 1.107 1.1-2.1-1.783-1.017A7.963 7.963 0 0019.5 12c0-.735-.098-1.448-.283-2.133L21 8.85 19.9 6.75l-1.919 1.107a7.963 7.963 0 00-2.231-1.29L14.4 2.25h-2.1l-.675 2.067zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ __('Settings') }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ __('Platform‑wide configuration.') }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden flex flex-col min-h-0 min-w-0">
                <div class="flex flex-col gap-2 border-b border-gray-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5 shrink-0">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('Recent tenants') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Latest tenants created on the platform.') }}</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-48">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                            </span>
                            <label for="dash-tenant-filter" class="sr-only">{{ __('Filter list') }}</label>
                            <input id="dash-tenant-filter" type="search" x-model="dashFilter" autocomplete="off" placeholder="{{ __('Filter…') }}"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-2 text-xs text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <a href="{{ route('admin.tenants.index') }}" class="shrink-0 inline-flex justify-center rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-medium text-indigo-700 hover:bg-indigo-100">{{ __('View all') }}</a>
                    </div>
                </div>
                <div class="w-full min-w-0 overflow-hidden flex-1">
                    @forelse(($recentTenants ?? []) as $tenant)
                        @php
                            $pd = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->first();
                            $__dashBlob = strtolower(trim(implode(' ', array_filter([
                                $tenant->tenant_name,
                                $tenant->email ?? '',
                                $pd?->domain,
                                $tenant->plan->name ?? '',
                            ]))));
                        @endphp
                        <div class="border-b border-gray-100 last:border-b-0 px-4 py-2.5 sm:px-5"
                             data-dash-tenant="{{ e($__dashBlob) }}"
                             x-show="!(dashFilter || '').trim() || ($el.dataset.dashTenant || '').includes((dashFilter || '').toLowerCase().trim())">
                            <div class="flex items-start justify-between gap-3 min-w-0">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate" title="{{ $tenant->tenant_name }}">{{ $tenant->tenant_name }}</p>
                                    <p class="text-xs text-gray-500 truncate" title="{{ ($pd?->domain ?? '') . ' · ' . ($tenant->plan->name ?? '') }}">
                                        <span class="font-mono text-[11px]">{{ $pd?->domain ?? __('No domain') }}</span>
                                        <span class="text-gray-300"> · </span>{{ $tenant->plan->name ?? __('No plan') }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $tenant->created_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
                                </div>
                                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="shrink-0 text-xs font-semibold text-indigo-600 hover:text-indigo-700">{{ __('Manage') }}</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-sm text-gray-500 text-left">
                            {{ __('No tenants yet. Create your first tenant to get started.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-admin::app-layout>
