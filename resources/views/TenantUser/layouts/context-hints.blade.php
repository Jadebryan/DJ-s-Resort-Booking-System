@php
    $rn = request()->route()?->getName() ?? '';
    $hintStorageKey = 'mtrbs.dismissHint.tenantUser.' . $rn;
@endphp
<div class="relative mb-5 rounded-xl border border-teal-100 bg-gradient-to-br from-teal-50/90 to-white px-4 py-3 shadow-sm sm:px-5 sm:py-4"
     x-data="{
        storageKey: @js($hintStorageKey),
        hidden: false,
        init() { this.hidden = localStorage.getItem(this.storageKey) === '1'; },
        dismiss() { localStorage.setItem(this.storageKey, '1'); this.hidden = true; },
     }"
     x-show="!hidden"
     x-cloak>
    <button type="button"
            @click="dismiss()"
            class="absolute right-3 top-3 rounded-lg p-1.5 text-gray-500 transition hover:bg-white/80 hover:text-gray-800"
            aria-label="{{ __('Dismiss tips') }}">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <div class="min-w-0 pr-10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-teal-800">{{ __('Helpful links') }}</p>
        @include('TenantUser.layouts.partials.context-hints-body')
    </div>
</div>
