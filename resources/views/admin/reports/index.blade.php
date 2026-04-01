<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Reports') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('High-level metrics about tenants and plans.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1 text-left"
         x-data="{ reportsFilter: '' }">
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">{{ __('Total tenants') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-indigo-950">{{ $tenantCount }}</p>
                <p class="mt-1 text-xs text-indigo-900/70">{{ __('All resorts on the platform') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Active tenants') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-950">{{ $activeTenantCount }}</p>
                <p class="mt-1 text-xs text-emerald-900/70">{{ __('Flagged active in admin') }}</p>
            </div>
            <div class="rounded-xl border border-violet-100 bg-violet-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-violet-800/90">{{ __('Active plans') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-violet-950">{{ $plans->count() }}</p>
                <p class="mt-1 text-xs text-violet-900/70">{{ __('Tiers in this report') }}</p>
            </div>
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('No plan assigned') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $tenantsWithoutPlan }}</p>
                <p class="mt-1 text-xs text-amber-900/70">{{ __('Tenants without plan_id') }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Tenants by plan') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Current subscription distribution across active plans.') }}</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center w-full sm:w-auto sm:gap-3">
                    <div class="relative w-full sm:w-56">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400" aria-hidden="true">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                        </span>
                        <label for="reports-plan-filter" class="sr-only">{{ __('Filter plans') }}</label>
                        <input id="reports-plan-filter" type="search" x-model="reportsFilter" autocomplete="off" placeholder="{{ __('Filter plan name…') }}"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <a href="{{ route('admin.subscriptions.index') }}" class="shrink-0 inline-flex justify-center rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-medium text-indigo-700 hover:bg-indigo-100">{{ __('Edit plans') }}</a>
                </div>
            </div>
            @if($plans->isEmpty())
                <div class="px-5 py-8 text-sm text-gray-500 text-left">
                    {{ __('No plans defined yet. Create plans to see distribution data here.') }}
                </div>
            @else
                <div class="w-full min-w-0 overflow-hidden">
                    <table class="w-full min-w-0 table-fixed divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="min-w-0 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:py-3 sm:text-xs">{{ __('Plan') }}</th>
                                <th class="w-28 px-3 py-2 text-right text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:py-3 sm:text-xs">{{ __('Tenants') }}</th>
                                <th class="hidden w-36 px-3 py-2 text-right text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:table-cell sm:px-4 sm:py-3 sm:text-xs">{{ __('Share') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($plans as $plan)
                                @php
                                    $tenantN = $tenantsByPlan->get($plan->id, 0);
                                    $share = $tenantCount > 0 ? round(100 * $tenantN / $tenantCount, 1) : 0;
                                    $rowHay = strtolower($plan->name.' '.$tenantN);
                                @endphp
                                <tr data-report-row="{{ e($rowHay) }}"
                                    x-show="!(reportsFilter || '').trim() || ($el.dataset.reportRow || '').includes((reportsFilter || '').toLowerCase().trim())"
                                    class="hover:bg-gray-50/50">
                                    <td class="max-w-0 px-3 py-2.5 text-sm font-medium text-gray-900 sm:px-4 sm:py-3">
                                        <span class="block truncate" title="{{ $plan->name }}">{{ $plan->name }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-sm tabular-nums text-gray-800 text-right sm:px-4 sm:py-3">{{ $tenantN }}</td>
                                    <td class="hidden px-3 py-2.5 text-sm tabular-nums text-gray-500 text-right sm:table-cell sm:px-4 sm:py-3">{{ $share }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-admin::app-layout>
