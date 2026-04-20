@props([
    /** @var 'teal'|'indigo' */
    'accent' => 'teal',
])
@php
    $activeBtn = $accent === 'indigo'
        ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200/80'
        : 'bg-white text-teal-700 shadow-sm ring-1 ring-gray-200/80';
    $inactiveBtn = 'text-gray-500 hover:text-gray-800';
@endphp
<div class="inline-flex shrink-0 rounded-lg border border-gray-200 bg-gray-50 p-0.5 shadow-sm" role="group" aria-label="{{ __('View layout') }}">
    <button type="button"
            @click="setListGridMode('list')"
            :class="listGridMode === 'list' ? '{{ $activeBtn }}' : '{{ $inactiveBtn }}'"
            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-semibold transition"
            :aria-pressed="listGridMode === 'list'"
            title="{{ __('List view') }}">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <span class="hidden sm:inline">{{ __('List') }}</span>
    </button>
    <button type="button"
            @click="setListGridMode('grid')"
            :class="listGridMode === 'grid' ? '{{ $activeBtn }}' : '{{ $inactiveBtn }}'"
            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-semibold transition"
            :aria-pressed="listGridMode === 'grid'"
            title="{{ __('Grid view') }}">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        <span class="hidden sm:inline">{{ __('Grid') }}</span>
    </button>
</div>
