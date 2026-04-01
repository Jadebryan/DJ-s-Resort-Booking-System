<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Resort signups') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Review subscription choices, payment references, then approve to provision the tenant.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1"
         x-data="{
             detailOpen: false,
             d: {},
             openDetails(payload) { this.d = payload; this.detailOpen = true; },
             closeDetails() { this.detailOpen = false; },
             init() {
                 this.$watch('detailOpen', v => document.body.classList.toggle('overflow-y-hidden', v));
             }
         }"
         @keydown.escape.window="if (detailOpen) closeDetails()">

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('Awaiting payment') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $stats['awaiting_payment'] }}</p>
            </div>
            <div class="rounded-xl border border-sky-100 bg-sky-50/60 px-4 py-3">
                <p class="text-[11px] font-medium uppercase tracking-wide text-sky-800/90">{{ __('Ready to review') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-sky-950">{{ $stats['pending_review'] }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Approved (all time)') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-950">{{ $stats['approved_total'] }}</p>
            </div>
            <div class="rounded-xl border border-red-100 bg-red-50/50 px-4 py-3">
                <p class="text-[11px] font-medium uppercase tracking-wide text-red-800/90">{{ __('Rejected (all time)') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-red-950">{{ $stats['rejected_total'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Open applications') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $requests->total() }} {{ $requests->total() === 1 ? __('open application') : __('open applications') }}
                    </p>
                </div>
                <form method="GET" action="{{ route('admin.tenant-registrations.index') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:max-w-md sm:flex-row sm:items-center">
                    @if(request()->filled('hq'))
                        <input type="hidden" name="hq" value="{{ request('hq') }}">
                    @endif
                    @if(($historyStatus ?? 'all') !== 'all')
                        <input type="hidden" name="history_status" value="{{ $historyStatus }}">
                    @endif
                    <div class="relative w-full sm:min-w-[14rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                        </span>
                        <label for="signup-search" class="sr-only">{{ __('Search signups') }}</label>
                        <input id="signup-search" type="search" name="q" value="{{ request('q') }}" autocomplete="off"
                               placeholder="{{ __('Resort, domain, email, plan, payment…') }}"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700 sm:py-2">
                            {{ __('Search') }}
                        </button>
                        @if(request()->filled('q'))
                            <a href="{{ route('admin.tenant-registrations.index') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 sm:py-2">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="w-full min-w-0 overflow-x-auto overflow-y-hidden">
                @if($requests->isEmpty())
                    <p class="px-5 py-8 text-sm text-gray-500 text-left">
                        {{ request()->filled('q') ? __('No pending signups match your search.') : __('No pending signups.') }}
                    </p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Resort') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Domain') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Plan') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Payment') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($requests as $r)
                                <tr class="align-top">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ $r->tenant_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $r->admin_name }} · {{ $r->admin_email }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-800">{{ $r->primary_domain }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $r->plan?->name ?? '—' }}
                                        @if($r->subscription_months)
                                            <span class="block text-xs text-gray-500">{{ $r->subscription_months }} {{ __('mo') }} · ₱{{ number_format($r->amountDue(), 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        <span class="font-medium">{{ $r->payment_provider ?? '—' }}</span>
                                        @if($r->payment_reference)
                                            <br><span class="font-mono">{{ $r->payment_reference }}</span>
                                        @endif
                                        @if($r->payment_notes)
                                            <p class="mt-1 text-gray-500">{{ \Illuminate\Support\Str::limit($r->payment_notes, 120) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($r->status === \App\Models\TenantRegistrationRequest::STATUS_AWAITING_PAYMENT)
                                            <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800">{{ __('Awaiting payment') }}</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-800">{{ __('Ready to review') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-left align-top space-y-2">
                                        <button type="button"
                                                @click="openDetails(@js([
                                                    'tenant_name' => $r->tenant_name,
                                                    'primary_domain' => $r->primary_domain,
                                                    'admin_name' => $r->admin_name,
                                                    'admin_email' => $r->admin_email,
                                                    'plan_name' => $r->plan?->name,
                                                    'subscription_months' => $r->subscription_months,
                                                    'amount_formatted' => $r->subscription_months ? number_format($r->amountDue(), 2) : null,
                                                    'status' => $r->status,
                                                    'payment_provider' => $r->payment_provider,
                                                    'payment_reference' => $r->payment_reference,
                                                    'payment_notes' => $r->payment_notes,
                                                    'payment_proof_url' => $r->payment_proof_path ? \Illuminate\Support\Facades\Storage::url($r->payment_proof_path) : null,
                                                    'applied_at_label' => $r->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A'),
                                                    'reviewed_at_label' => null,
                                                    'reviewer_name' => null,
                                                    'rejection_reason' => null,
                                                    'tenant_manage_url' => null,
                                                ]))"
                                                class="block w-full max-w-[220px] rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-center text-xs font-semibold text-gray-800 hover:bg-gray-50">
                                            {{ __('View details') }}
                                        </button>
                                        @if($r->status === \App\Models\TenantRegistrationRequest::STATUS_PENDING_REVIEW)
                                            <x-confirm-form-button
                                                class="block w-full max-w-[220px]"
                                                :action="route('admin.tenant-registrations.approve', $r)"
                                                method="POST"
                                                :title="__('Approve & provision')"
                                                :message="__('Provision this resort and notify the applicant?')"
                                                :confirm-label="__('Approve & provision')"
                                                variant="primary">
                                                <button type="button" @click="open = true" class="block w-full rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">{{ __('Approve & provision') }}</button>
                                            </x-confirm-form-button>
                                            <form method="POST" action="{{ route('admin.tenant-registrations.reject', $r) }}" class="max-w-[220px] space-y-1">
                                                @csrf
                                                <textarea name="rejection_reason" rows="2" placeholder="{{ __('Optional reason…') }}"
                                                          class="w-full min-w-[200px] rounded border border-gray-200 px-2 py-1 text-xs"></textarea>
                                                <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">{{ __('Reject') }}</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">{{ __('Approve after payment') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-4 py-3 border-t border-gray-100 text-left">{{ $requests->links() }}</div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5 space-y-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('Decision history') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $history->total() }} {{ \Illuminate\Support\Str::plural('decision', $history->total()) }}
                            @if($historyStatus !== 'all' || request()->filled('hq'))
                                <span class="text-gray-400">·</span> {{ __('filtered') }}
                            @endif
                        </p>
                    </div>
                </div>
                <form method="GET" action="{{ route('admin.tenant-registrations.index') }}" class="flex flex-col gap-2 lg:flex-row lg:flex-wrap lg:items-end">
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <div class="w-full sm:max-w-[11rem]">
                        <label for="history-status" class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Decision') }}</label>
                        <select id="history-status" name="history_status"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm text-gray-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="all" @selected($historyStatus === 'all')>{{ __('All') }}</option>
                            <option value="approved" @selected($historyStatus === 'approved')>{{ __('Approved') }}</option>
                            <option value="rejected" @selected($historyStatus === 'rejected')>{{ __('Rejected') }}</option>
                        </select>
                    </div>
                    <div class="relative flex-1 min-w-0 lg:min-w-[14rem] lg:max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                        </span>
                        <label for="history-search" class="sr-only">{{ __('Search history') }}</label>
                        <input id="history-search" type="search" name="hq" value="{{ request('hq') }}" autocomplete="off"
                               placeholder="{{ __('Resort, domain, email, plan, payment…') }}"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                            {{ __('Apply') }}
                        </button>
                        @if($historyStatus !== 'all' || request()->filled('hq'))
                            <a href="{{ route('admin.tenant-registrations.index', array_filter(['q' => request('q')])) }}"
                               class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Reset history') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="w-full min-w-0 overflow-x-auto">
                @if($history->isEmpty())
                    <p class="px-5 py-8 text-sm text-gray-500 text-left">
                        {{ request()->filled('hq') || $historyStatus !== 'all' ? __('No decisions match your filters.') : __('No approved or rejected applications yet.') }}
                    </p>
                @else
                    <table class="min-w-full table-fixed divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="min-w-0 w-[18%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:text-xs">{{ __('Resort') }}</th>
                                <th class="min-w-0 w-[16%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:text-xs">{{ __('Domain') }}</th>
                                <th class="hidden min-w-0 w-[14%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 md:table-cell sm:px-4 sm:text-xs">{{ __('Plan') }}</th>
                                <th class="hidden min-w-0 w-[12%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 lg:table-cell sm:px-4 sm:text-xs">{{ __('Amount') }}</th>
                                <th class="hidden min-w-0 w-[14%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 xl:table-cell sm:px-4 sm:text-xs">{{ __('Payment ref.') }}</th>
                                <th class="min-w-0 w-[14%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:text-xs">{{ __('Decided') }}</th>
                                <th class="min-w-0 w-[10%] px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:text-xs">{{ __('Status') }}</th>
                                <th class="w-[7rem] px-2 py-2 text-right text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-4 sm:text-xs">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($history as $h)
                                <tr class="align-top hover:bg-gray-50/50">
                                    <td class="max-w-0 px-3 py-2.5 sm:px-4 sm:py-3">
                                        <p class="truncate font-medium text-gray-900" title="{{ $h->tenant_name }}">{{ $h->tenant_name }}</p>
                                        <p class="truncate text-xs text-gray-500" title="{{ $h->admin_email }}">{{ $h->admin_email }}</p>
                                    </td>
                                    <td class="max-w-0 px-3 py-2.5 font-mono text-xs text-gray-800 sm:px-4 sm:py-3">
                                        <span class="block truncate" title="{{ $h->primary_domain }}">{{ $h->primary_domain }}</span>
                                    </td>
                                    <td class="hidden max-w-0 px-3 py-2.5 text-gray-700 md:table-cell sm:px-4 sm:py-3">
                                        <span class="block truncate" title="{{ $h->plan?->name ?? '—' }}">{{ $h->plan?->name ?? '—' }}</span>
                                        @if($h->subscription_months)
                                            <span class="block truncate text-xs text-gray-500">{{ $h->subscription_months }} {{ __('mo') }}</span>
                                        @endif
                                    </td>
                                    <td class="hidden max-w-0 px-3 py-2.5 tabular-nums text-gray-800 lg:table-cell sm:px-4 sm:py-3">
                                        @if($h->plan)
                                            <span class="text-xs">₱{{ number_format($h->amountDue(), 2) }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="hidden max-w-0 px-3 py-2.5 text-xs text-gray-600 xl:table-cell sm:px-4 sm:py-3">
                                        @if($h->payment_reference)
                                            <span class="block truncate font-mono" title="{{ $h->payment_reference }}">{{ $h->payment_reference }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="max-w-0 px-3 py-2.5 text-xs text-gray-600 sm:px-4 sm:py-3">
                                        <span class="block truncate" title="{{ $h->reviewed_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}">
                                            {{ $h->reviewed_at?->timezone(config('app.timezone'))->format('M j, Y') ?? '—' }}
                                        </span>
                                        @if($h->reviewer)
                                            <span class="block truncate text-[11px] text-gray-400" title="{{ $h->reviewer->name }}">{{ $h->reviewer->name }}</span>
                                        @endif
                                    </td>
                                    <td class="max-w-0 px-3 py-2.5 sm:px-4 sm:py-3">
                                        @if($h->status === \App\Models\TenantRegistrationRequest::STATUS_APPROVED)
                                            <span class="inline-flex max-w-full items-center truncate rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-800 sm:text-[11px]">{{ __('Approved') }}</span>
                                        @else
                                            <span class="inline-flex max-w-full items-center truncate rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-800 sm:text-[11px]">{{ __('Rejected') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2.5 text-right sm:px-4 sm:py-3">
                                        <div class="flex flex-col items-end gap-1.5">
                                            <button type="button"
                                                    @click="openDetails(@js([
                                                        'tenant_name' => $h->tenant_name,
                                                        'primary_domain' => $h->primary_domain,
                                                        'admin_name' => $h->admin_name,
                                                        'admin_email' => $h->admin_email,
                                                        'plan_name' => $h->plan?->name,
                                                        'subscription_months' => $h->subscription_months,
                                                        'amount_formatted' => $h->plan ? number_format($h->amountDue(), 2) : null,
                                                        'status' => $h->status,
                                                        'payment_provider' => $h->payment_provider,
                                                        'payment_reference' => $h->payment_reference,
                                                        'payment_notes' => $h->payment_notes,
                                                        'payment_proof_url' => $h->payment_proof_path ? \Illuminate\Support\Facades\Storage::url($h->payment_proof_path) : null,
                                                        'applied_at_label' => $h->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A'),
                                                        'reviewed_at_label' => $h->reviewed_at?->timezone(config('app.timezone'))->format('M j, Y g:i A'),
                                                        'reviewer_name' => $h->reviewer?->name,
                                                        'rejection_reason' => $h->rejection_reason,
                                                        'tenant_manage_url' => $h->approved_tenant_id ? route('admin.tenants.edit', $h->approved_tenant_id) : null,
                                                    ]))"
                                                    class="inline-flex rounded-lg border border-gray-200 bg-white px-2 py-1 text-[10px] font-semibold text-gray-800 hover:bg-gray-50 sm:px-2.5 sm:text-xs">
                                                {{ __('Details') }}
                                            </button>
                                            @if($h->approved_tenant_id)
                                                <a href="{{ route('admin.tenants.edit', $h->approved_tenant_id) }}"
                                                   class="inline-flex rounded-lg border border-indigo-100 bg-indigo-50/80 px-2 py-1 text-[10px] font-semibold text-indigo-800 hover:bg-indigo-100 sm:px-2.5 sm:text-xs">
                                                    {{ __('Tenant') }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="border-t border-gray-100 px-4 py-3">{{ $history->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Teleport to body so fixed overlay is not clipped by <main overflow-auto> or stacked under the header --}}
        <template x-teleport="body">
        <div x-show="detailOpen" x-cloak
             class="fixed inset-0 z-[100] flex items-end justify-center sm:items-center p-0 sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="signup-detail-title">
            <div class="absolute inset-0 bg-gray-900/60" @click="closeDetails()" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg sm:max-w-2xl max-h-[92vh] sm:max-h-[90vh] flex flex-col rounded-t-2xl sm:rounded-xl bg-white shadow-xl border border-gray-200 overflow-hidden"
                 @click.stop>
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5 shrink-0">
                    <div class="min-w-0">
                        <h2 id="signup-detail-title" class="text-sm font-semibold text-gray-900 truncate" x-text="d.tenant_name"></h2>
                        <p class="text-[11px] text-gray-500 font-mono truncate" x-text="d.primary_domain"></p>
                    </div>
                    <button type="button" @click="closeDetails()"
                            class="shrink-0 rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="sr-only">{{ __('Close') }}</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 px-4 py-4 sm:px-5 space-y-5 text-left">
                    <div x-show="d.applied_at_label" class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2">
                        <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('Applied') }}</p>
                        <p class="mt-0.5 text-sm text-gray-900" x-text="d.applied_at_label"></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Resort & contact') }}</h3>
                        <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-gray-500">{{ __('Admin') }}</dt>
                                <dd class="text-gray-900"><span x-text="d.admin_name"></span> · <span x-text="d.admin_email"></span></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">{{ __('Status') }}</dt>
                                <dd class="mt-0.5">
                                    <span x-show="d.status === @js(\App\Models\TenantRegistrationRequest::STATUS_AWAITING_PAYMENT)" class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800">{{ __('Awaiting payment') }}</span>
                                    <span x-show="d.status === @js(\App\Models\TenantRegistrationRequest::STATUS_PENDING_REVIEW)" class="inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-800">{{ __('Ready to review') }}</span>
                                    <span x-show="d.status === @js(\App\Models\TenantRegistrationRequest::STATUS_APPROVED)" class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('Approved') }}</span>
                                    <span x-show="d.status === @js(\App\Models\TenantRegistrationRequest::STATUS_REJECTED)" class="inline-flex rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-800">{{ __('Rejected') }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div x-show="d.reviewed_at_label" class="rounded-lg border border-gray-200 bg-white px-3 py-3 space-y-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Review') }}</h3>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-gray-500">{{ __('Decided at') }}</dt>
                                <dd class="text-gray-900" x-text="d.reviewed_at_label"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">{{ __('Reviewer') }}</dt>
                                <dd class="text-gray-900" x-text="d.reviewer_name || '—'"></dd>
                            </div>
                        </dl>
                        <div x-show="d.rejection_reason" class="pt-1 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500">{{ __('Rejection reason') }}</p>
                            <p class="mt-1 text-sm text-red-900/90 whitespace-pre-wrap" x-text="d.rejection_reason"></p>
                        </div>
                        <div x-show="d.tenant_manage_url" class="pt-2">
                            <a :href="d.tenant_manage_url" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 hover:text-indigo-900">
                                {{ __('Open tenant in admin') }}
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Plan & amount') }}</h3>
                        <p class="mt-2 text-sm text-gray-900">
                            <span x-text="d.plan_name || '—'"></span>
                            <span class="block text-xs text-gray-500 mt-0.5" x-text="d.subscription_months ? (d.subscription_months + ' {{ __('mo') }} · ₱' + d.amount_formatted) : '—'"></span>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Payment') }}</h3>
                        <dl class="mt-2 space-y-2 text-sm">
                            <div>
                                <dt class="text-xs text-gray-500">{{ __('Provider') }}</dt>
                                <dd class="text-gray-900" x-text="d.payment_provider || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">{{ __('Reference') }}</dt>
                                <dd class="font-mono text-xs text-gray-800" x-text="d.payment_reference || '—'"></dd>
                            </div>
                            <div x-show="d.payment_notes">
                                <dt class="text-xs text-gray-500">{{ __('Notes from applicant') }}</dt>
                                <dd class="mt-1 text-gray-700 whitespace-pre-wrap text-sm" x-text="d.payment_notes"></dd>
                            </div>
                        </dl>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-3">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Payment proof') }}</h3>
                        <template x-if="d.payment_proof_url">
                            <div class="mt-3">
                                <a :href="d.payment_proof_url" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-indigo-700 hover:text-indigo-900">{{ __('Open full size in new tab') }}</a>
                                <div class="mt-2 max-h-[min(50vh,420px)] overflow-auto rounded-md border border-gray-200 bg-white p-1">
                                    <img :src="d.payment_proof_url" alt="{{ __('Payment proof') }}" class="max-w-full h-auto mx-auto block rounded">
                                </div>
                            </div>
                        </template>
                        <template x-if="!d.payment_proof_url">
                            <p class="mt-2 text-sm text-gray-500">{{ __('No payment proof image has been uploaded for this application yet.') }}</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </div>
</x-admin::app-layout>
