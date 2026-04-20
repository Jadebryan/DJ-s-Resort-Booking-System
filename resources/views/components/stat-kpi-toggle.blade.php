@props([
    'storageKey',
    'gridClass' => 'grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4',
    /** @var 'teal'|'indigo' Accent for "Show metrics" button */
    'accent' => 'teal',
])
@php
    $showClasses = $accent === 'indigo'
        ? 'text-indigo-700 shadow-sm transition hover:bg-indigo-50'
        : 'text-teal-700 shadow-sm transition hover:bg-teal-50';
@endphp
<div x-data="{
        kpiStorageKey: @js($storageKey),
        kpiHidden: false,
        init() { this.kpiHidden = localStorage.getItem(this.kpiStorageKey) === '1'; },
        toggleKpi() {
            this.kpiHidden = !this.kpiHidden;
            localStorage.setItem(this.kpiStorageKey, this.kpiHidden ? '1' : '0');
        },
    }"
    {{ $attributes->class(['space-y-2']) }}>
    <div class="flex justify-end">
        <button type="button"
                x-show="!kpiHidden"
                @click="toggleKpi()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                title="{{ __('Hide overview metrics') }}"
                aria-label="{{ __('Hide overview metrics') }}">
            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            <span class="hidden sm:inline">{{ __('Hide metrics') }}</span>
        </button>
    </div>
    <div x-show="!kpiHidden" class="{{ $gridClass }}">
        {{ $slot }}
    </div>
    <div x-show="kpiHidden"
         x-cloak
         class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-dashed border-gray-200 bg-gray-50/90 px-3 py-2.5">
        <p class="text-xs text-gray-600">{{ __('Overview metrics are hidden.') }}</p>
        <button type="button"
                @click="toggleKpi()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold {{ $showClasses }}"
                title="{{ __('Show overview metrics') }}"
                aria-label="{{ __('Show overview metrics') }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            {{ __('Show metrics') }}
        </button>
    </div>
</div>
