<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Subscriptions') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Manage plan pricing, limits, and included features.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1 text-left"
         x-data="{ subscriptionFilter: '' }">
        <x-stat-kpi-toggle storage-key="mtrbs.admin.subscriptions.kpi.hidden" grid-class="grid grid-cols-1 gap-3 sm:grid-cols-3" accent="indigo">
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">{{ __('Plans in catalog') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-indigo-950">{{ $planStats['total'] ?? $plans->count() }}</p>
                <p class="mt-1 text-xs text-indigo-900/70">{{ __('All records') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Active') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-950">{{ $planStats['active'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-emerald-900/70">{{ __('Shown to new signups') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-600">{{ __('Inactive') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900">{{ $planStats['inactive'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-600">{{ __('Hidden from selection') }}</p>
            </div>
        </x-stat-kpi-toggle>

        <section class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Plan catalog') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Update pricing, limits, and features. Filter the list when you have many plans.') }}</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center w-full sm:w-auto">
                    <div class="relative w-full sm:w-52">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400" aria-hidden="true">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                        </span>
                        <label for="subscription-filter" class="sr-only">{{ __('Filter plans') }}</label>
                        <input id="subscription-filter" type="search" x-model="subscriptionFilter" autocomplete="off" placeholder="{{ __('Search name, slug, features…') }}"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <button type="submit" form="plans-form"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 whitespace-nowrap shrink-0">
                        {{ __('Save all plans') }}
                    </button>
                </div>
            </div>

            <form id="plans-form" method="POST" action="{{ route('admin.subscriptions.update') }}" class="p-4 sm:p-5 space-y-4">
                @csrf

                @if($plans->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No plans found. Run your plan seeder first.') }}</p>
                @else
                    @foreach($plans as $index => $plan)
                        @php
                            $selectedFeatures = old("plans.$index.features");
                            if (! is_array($selectedFeatures)) {
                                $selectedFeatures = is_array($plan->features) ? $plan->features : [];
                            }
                            $rowSearch = strtolower(trim(implode(' ', array_filter([
                                $plan->name,
                                $plan->slug,
                                $plan->description,
                                is_array($plan->features) ? implode(' ', $plan->features) : '',
                            ]))));
                        @endphp

                        <div class="rounded-lg border border-gray-200 bg-gray-50/30 p-4 space-y-4"
                             data-plan-search="{{ e($rowSearch) }}"
                             x-show="!(subscriptionFilter || '').trim() || ($el.dataset.planSearch || '').includes((subscriptionFilter || '').toLowerCase().trim())">
                            <input type="hidden" name="plans[{{ $index }}][id]" value="{{ $plan->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Plan name') }}</label>
                                    <input type="text" name="plans[{{ $index }}][name]" value="{{ old("plans.$index.name", $plan->name) }}"
                                           {{ \App\Support\InputHtmlAttributes::title(255) }}
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Sort order') }}</label>
                                    <input type="number" min="0" max="9999" name="plans[{{ $index }}][sort_order]" value="{{ old("plans.$index.sort_order", $plan->sort_order) }}"
                                           inputmode="numeric" maxlength="4"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Description') }}</label>
                                <textarea rows="2" name="plans[{{ $index }}][description]"
                                          {{ \App\Support\InputHtmlAttributes::textarea(2000) }}
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">{{ old("plans.$index.description", $plan->description) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Monthly price') }}</label>
                                    <input type="number" min="0" step="0.01" name="plans[{{ $index }}][price_monthly]" value="{{ old("plans.$index.price_monthly", $plan->price_monthly) }}"
                                           inputmode="decimal"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Yearly price') }}</label>
                                    <input type="number" min="0" step="0.01" name="plans[{{ $index }}][price_yearly]" value="{{ old("plans.$index.price_yearly", $plan->price_yearly) }}"
                                           inputmode="decimal"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Max rooms') }}</label>
                                    <input type="number" min="1" name="plans[{{ $index }}][max_rooms]" value="{{ old("plans.$index.max_rooms", $plan->max_rooms) }}"
                                           placeholder="{{ __('Leave empty for unlimited') }}"
                                           inputmode="numeric" maxlength="12"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                                </div>
                                <div class="flex items-end">
                                    <input type="hidden" name="plans[{{ $index }}][is_active]" value="0">
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               name="plans[{{ $index }}][is_active]" value="1"
                                               @checked(old("plans.$index.is_active", $plan->is_active))>
                                        {{ __('Active') }}
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">{{ __('Included features') }}</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 rounded-lg border border-gray-200 bg-white p-3">
                                    @foreach($featureCatalog as $featureKey => $featureLabel)
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                            <input type="checkbox"
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                   name="plans[{{ $index }}][features][]"
                                                   value="{{ $featureKey }}"
                                                   @checked(in_array($featureKey, $selectedFeatures, true))>
                                            <span>{{ __($featureLabel) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                @endif
            </form>
        </section>
    </div>
</x-admin::app-layout>
