@php
    $rn = request()->route()?->getName() ?? '';
    $hintStorageKey = 'mtrbs.dismissHint.admin.' . $rn;
@endphp
<div class="relative"
     x-data="{ open: false, hintKey: @js($hintStorageKey), showBannerAgain() { localStorage.removeItem(this.hintKey); window.location.reload(); } }"
     @keydown.escape.window="open = false"
     @click.outside="open = false">
    <button type="button"
            @click="open = !open"
            class="rounded-xl p-2 text-gray-500 transition hover:bg-white hover:text-indigo-700 hover:shadow-sm"
            :aria-expanded="open"
            aria-label="{{ __('Page help') }}"
            title="{{ __('Shortcuts & tips for this page') }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </button>
    <div x-show="open"
         x-cloak
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 z-[60] mt-2 w-[min(100vw-2rem,24rem)] max-h-[min(70vh,28rem)] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
        <div class="sticky top-0 flex items-start justify-between gap-2 border-b border-gray-100 bg-gradient-to-r from-indigo-50/90 to-white px-3 py-2.5">
            <p class="text-sm font-semibold text-gray-900">{{ __('Shortcuts & tips') }}</p>
            <button type="button"
                    @click="showBannerAgain()"
                    class="shrink-0 text-[11px] font-medium text-indigo-700 hover:text-indigo-900"
                    title="{{ __('Show the tips banner again on this page') }}">
                {{ __('Show banner') }}
            </button>
        </div>
        <div class="px-3 py-3 text-left">
            @include('admin.layouts.partials.context-hints-body')
        </div>
    </div>
</div>
