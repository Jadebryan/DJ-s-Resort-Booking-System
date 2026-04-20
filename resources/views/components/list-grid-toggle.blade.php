@props([
    'storageKey',
    /** @var 'list'|'grid' */
    'defaultView' => 'list',
    /** @var 'teal'|'indigo' */
    'accent' => 'teal',
    /** Classes for the row that contains toolbar, view toggle, and optional toolbarEnd */
    'toolbarRowClass' => 'mb-4 flex flex-wrap items-center justify-between gap-3',
    /** Wrapper around list/grid panels (e.g. horizontal padding) */
    'contentClass' => 'w-full min-w-0',
])
<div {{ $attributes->merge(['class' => 'w-full min-w-0']) }} x-data="{
        listGridMode: @js($defaultView),
        listGridStorageKey: @js($storageKey),
        init() {
            try {
                const v = localStorage.getItem(this.listGridStorageKey);
                if (v === 'grid' || v === 'list') {
                    this.listGridMode = v;
                }
            } catch (e) {}
        },
        setListGridMode(m) {
            this.listGridMode = m;
            try {
                localStorage.setItem(this.listGridStorageKey, m);
            } catch (e) {}
        },
    }">
    <div class="{{ $toolbarRowClass }}">
        <div class="min-w-0 flex-1">
            {{ $toolbar ?? '' }}
        </div>
        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-2.5">
            <x-list-grid-toggle-buttons :accent="$accent" />
            {{ $toolbarEnd ?? '' }}
        </div>
    </div>
    <div class="{{ $contentClass }}">
        <div x-show="listGridMode === 'list'" x-cloak class="w-full min-w-0">
            {{ $list }}
        </div>
        <div x-show="listGridMode === 'grid'" x-cloak class="w-full min-w-0">
            {{ $grid }}
        </div>
    </div>
</div>
