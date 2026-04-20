<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Payments') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Review tenant subscription payments and record manual transactions.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1"
         x-data="{
            proofModalOpen: false,
            proofUrl: '',
            expandId: null,
            openProof(url) {
                this.proofUrl = url;
                this.proofModalOpen = true;
            },
            toggleExpand(id) {
                this.expandId = this.expandId === id ? null : id;
            }
         }"
         @keydown.escape.window="proofModalOpen = false">
        <x-stat-kpi-toggle storage-key="mtrbs.admin.payments.kpi.hidden" grid-class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4" accent="indigo">
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('Pending requests') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $pendingCount }}</p>
                <p class="mt-1 text-xs text-amber-900/70">{{ __('Awaiting your review') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Approved') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-950">{{ $approvedCount }}</p>
                <p class="mt-1 text-xs text-emerald-900/70">{{ __('Applied to tenants') }}</p>
            </div>
            <div class="rounded-xl border border-rose-100 bg-rose-50/50 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-rose-800/90">{{ __('Rejected') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-rose-950">{{ $rejectedCount }}</p>
                <p class="mt-1 text-xs text-rose-900/70">{{ __('Declined upgrades') }}</p>
            </div>
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">{{ __('Active subscriptions') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-indigo-950">{{ $activeSubscriptions }}</p>
                <p class="mt-1 text-xs text-indigo-900/70">{{ __('Tenants with future end date') }}</p>
            </div>
        </x-stat-kpi-toggle>

        <section class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5 space-y-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('Tenant subscription requests') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Approve or reject plan upgrades and renewals. Expand a row for proration and actions.') }}</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('admin.payments') }}" class="flex flex-col gap-2 lg:flex-row lg:flex-wrap lg:items-end">
                    <div class="w-full sm:max-w-[11rem]">
                        <label for="payment-status" class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Status') }}</label>
                        <select id="payment-status" name="payment_status"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm text-gray-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="all" @selected(($paymentStatus ?? 'all') === 'all')>{{ __('All') }}</option>
                            <option value="pending" @selected(($paymentStatus ?? '') === 'pending')>{{ __('Pending') }}</option>
                            <option value="approved" @selected(($paymentStatus ?? '') === 'approved')>{{ __('Approved') }}</option>
                            <option value="rejected" @selected(($paymentStatus ?? '') === 'rejected')>{{ __('Rejected') }}</option>
                        </select>
                    </div>
                    <div class="relative flex-1 min-w-0 lg:min-w-[14rem] lg:max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                        </span>
                        <label for="payments-search" class="sr-only">{{ __('Search requests') }}</label>
                        <input id="payments-search" type="search" name="pq" value="{{ request('pq') }}" autocomplete="off"
                               placeholder="{{ __('Tenant, email, plan, reference…') }}"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">{{ __('Apply') }}</button>
                        @if(($paymentStatus ?? 'all') !== 'all' || request()->filled('pq'))
                            <a href="{{ route('admin.payments') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Reset') }}</a>
                        @endif
                    </div>
                </form>
            </div>

            @if($upgradeRequests->isEmpty())
                <div class="px-5 py-8 text-sm text-gray-500 text-left">
                    {{ request()->filled('pq') || ($paymentStatus ?? 'all') !== 'all' ? __('No requests match your filters.') : __('No subscription requests yet.') }}
                </div>
            @else
                <div class="w-full min-w-0 overflow-x-auto">
                    <table class="w-full min-w-[720px] table-fixed divide-y divide-gray-200 text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="min-w-0 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:text-xs">{{ __('Tenant') }}</th>
                                <th class="w-[5.5rem] px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-3 sm:text-xs">{{ __('Type') }}</th>
                                <th class="hidden min-w-0 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 md:table-cell sm:text-xs">{{ __('Plan change') }}</th>
                                <th class="w-[5rem] px-2 py-2 text-right text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-3 sm:text-xs">{{ __('Due') }}</th>
                                <th class="w-[6.5rem] px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-3 sm:text-xs">{{ __('Status') }}</th>
                                <th class="hidden w-[6rem] px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 lg:table-cell sm:text-xs">{{ __('Submitted') }}</th>
                                <th class="w-[7rem] px-2 py-2 text-right text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-3 sm:text-xs">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        @foreach($upgradeRequests as $requestItem)
                            @php
                                $isRenewalRequest = $requestItem->current_plan_id && $requestItem->requested_plan_id
                                    && (int) $requestItem->current_plan_id === (int) $requestItem->requested_plan_id;
                                $rid = $requestItem->id;
                            @endphp
                            <tbody class="divide-y divide-gray-100">
                                <tr class="align-top hover:bg-gray-50/60">
                                    <td class="max-w-0 px-3 py-2.5 sm:px-4 sm:py-3">
                                        <p class="truncate font-medium text-gray-900" title="{{ $requestItem->tenant?->tenant_name ?? '—' }}">{{ $requestItem->tenant?->tenant_name ?? __('Unknown tenant') }}</p>
                                        @if($requestItem->tenant?->email)
                                            <p class="truncate text-xs text-gray-500" title="{{ $requestItem->tenant->email }}">{{ $requestItem->tenant->email }}</p>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2.5 sm:px-3 sm:py-3">
                                        @if($isRenewalRequest)
                                            <span class="inline-flex rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-800">{{ __('Renewal') }}</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-800">{{ __('Upgrade') }}</span>
                                        @endif
                                    </td>
                                    <td class="hidden max-w-0 px-3 py-2.5 text-xs text-gray-700 md:table-cell sm:py-3">
                                        <span class="block truncate" title="{{ ($requestItem->currentPlan?->name ?? '—') . ' → ' . ($requestItem->requestedPlan?->name ?? '—') }}">
                                            {{ $requestItem->currentPlan?->name ?? '—' }} → {{ $requestItem->requestedPlan?->name ?? '—' }}
                                        </span>
                                        <span class="block truncate text-[11px] text-gray-500">{{ $requestItem->requested_months }} {{ \Illuminate\Support\Str::plural('month', $requestItem->requested_months) }}</span>
                                    </td>
                                    <td class="px-2 py-2.5 text-right tabular-nums text-xs font-semibold text-gray-900 sm:px-3 sm:py-3">
                                        ₱{{ number_format((float) ($requestItem->proration_amount_due ?? 0), 0) }}
                                    </td>
                                    <td class="px-2 py-2.5 sm:px-3 sm:py-3">
                                        @if($requestItem->status === 'pending')
                                            <span class="inline-flex max-w-full truncate rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-800">{{ __('Pending') }}</span>
                                        @elseif($requestItem->status === 'approved')
                                            <span class="inline-flex max-w-full truncate rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-800">{{ __('Approved') }}</span>
                                        @else
                                            <span class="inline-flex max-w-full truncate rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-800">{{ __('Rejected') }}</span>
                                        @endif
                                    </td>
                                    <td class="hidden max-w-0 px-2 py-2.5 text-xs text-gray-600 lg:table-cell sm:px-3 sm:py-3">
                                        <span class="block truncate" title="{{ optional($requestItem->created_at)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}">{{ optional($requestItem->created_at)->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-2 py-2.5 text-right sm:px-3 sm:py-3">
                                        <div class="flex flex-col items-end gap-1">
                                            <button type="button" @click="toggleExpand({{ $rid }})"
                                                    class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-[10px] font-semibold text-gray-800 hover:bg-gray-50 sm:text-xs">
                                                <span x-show="expandId !== {{ $rid }}">{{ __('Expand') }}</span>
                                                <span x-show="expandId === {{ $rid }}" x-cloak>{{ __('Collapse') }}</span>
                                            </button>
                                            @if($requestItem->payment_proof_path)
                                                <button type="button"
                                                        @click="openProof('{{ \Illuminate\Support\Facades\Storage::url($requestItem->payment_proof_path) }}')"
                                                        class="text-[10px] font-semibold text-indigo-700 hover:text-indigo-900 sm:text-xs">{{ __('Proof') }}</button>
                                            @endif
                                            @if($requestItem->tenant_id)
                                                <a href="{{ route('admin.tenants.edit', $requestItem->tenant_id) }}" class="text-[10px] font-semibold text-gray-600 hover:text-gray-900 sm:text-xs">{{ __('Tenant') }}</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="expandId === {{ $rid }}" x-cloak class="bg-gray-50/90">
                                    <td colspan="7" class="px-4 py-4 sm:px-5 text-left">
                                        <div class="md:hidden mb-3 text-xs text-gray-700">
                                            <span class="font-medium">{{ __('Plans:') }}</span>
                                            {{ $requestItem->currentPlan?->name ?? '—' }} → {{ $requestItem->requestedPlan?->name ?? '—' }}
                                            · {{ $requestItem->requested_months }} {{ \Illuminate\Support\Str::plural('month', $requestItem->requested_months) }}
                                        </div>
                                        <p class="text-xs text-gray-600">
                                            <span class="font-medium text-gray-800">{{ __('Payment:') }}</span>
                                            {{ $requestItem->payment_method }}
                                            @if($requestItem->payment_reference)
                                                · <span class="font-mono">{{ $requestItem->payment_reference }}</span>
                                            @endif
                                        </p>
                                        @if($requestItem->payment_notes)
                                            <p class="mt-2 text-xs text-gray-600">{{ $requestItem->payment_notes }}</p>
                                        @endif

                                        @if($requestItem->proration_total_days !== null)
                                            <div class="mt-3 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] text-gray-700">
                                                <p class="font-semibold text-gray-900 mb-1">{{ __('Proration (:d-day months)', ['d' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}</p>
                                                <ul class="grid gap-1 sm:grid-cols-2">
                                                    <li>{{ __('Days left at request:') }} <span class="font-medium text-gray-900">{{ $requestItem->proration_days_remaining ?? '—' }}</span></li>
                                                    <li>{{ __('Credit (unused):') }} <span class="font-medium text-gray-900">₱{{ number_format((float) ($requestItem->proration_credit_amount ?? 0), 0) }}</span></li>
                                                    <li>{{ __('New term total:') }} <span class="font-medium text-gray-900">₱{{ number_format((float) ($requestItem->proration_new_term_total ?? 0), 0) }}</span></li>
                                                    <li>{{ __('Amount due (quoted):') }} <span class="font-medium text-gray-900">₱{{ number_format((float) ($requestItem->proration_amount_due ?? 0), 0) }}</span></li>
                                                    <li>{{ __('Base days:') }} <span class="font-medium text-gray-900">{{ $requestItem->proration_base_days ?? '—' }}</span></li>
                                                    <li>{{ __('Rollover days:') }} <span class="font-medium text-gray-900">{{ $requestItem->proration_rollover_days ?? '—' }}</span></li>
                                                    <li class="sm:col-span-2">{{ __('Total days after approval:') }} <span class="font-medium text-gray-900">{{ $requestItem->proration_total_days ?? '—' }}</span></li>
                                                </ul>
                                            </div>
                                        @endif

                                        @if($requestItem->status === 'pending')
                                            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                                                <form method="POST" action="{{ route('admin.payments.upgrade-requests.approve', $requestItem) }}" class="space-y-2">
                                                    @csrf
                                                    <textarea name="review_notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-900" placeholder="{{ __('Approval notes (optional)') }}"></textarea>
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-[11px] font-semibold text-white hover:bg-emerald-700">
                                                        {{ __('Approve and apply plan') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.payments.upgrade-requests.reject', $requestItem) }}" class="space-y-2">
                                                    @csrf
                                                    <textarea name="review_notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-900" placeholder="{{ __('Reason for rejection (required)') }}" required></textarea>
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-2 text-[11px] font-semibold text-white hover:bg-rose-700">
                                                        {{ __('Reject request') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($requestItem->review_notes)
                                            <p class="mt-3 text-xs text-gray-500">{{ __('Review notes:') }} {{ $requestItem->review_notes }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
                <div class="border-t border-gray-100 px-4 py-3">{{ $upgradeRequests->links() }}</div>
            @endif
        </section>

        <div x-show="proofModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="proofModalOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
            <div x-show="proofModalOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Payment proof') }}</h2>
                    <button type="button" @click="proofModalOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="{{ __('Close') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="bg-gray-50 p-4">
                    <img :src="proofUrl" alt="{{ __('Payment proof preview') }}" class="mx-auto max-h-[75vh] w-auto rounded-lg border border-gray-200 bg-white object-contain">
                </div>
            </div>
        </div>
    </div>
</x-admin::app-layout>
