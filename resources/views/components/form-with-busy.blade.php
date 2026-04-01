{{--
    Wraps a classic HTML form with Alpine submit guard + optional dimmed overlay.

    @param string|null $busyMessage  Shown in overlay (when overlay=true) and available as busyMessage in scope.
    @param bool $overlay             Full-form overlay while submitting (good for long forms / uploads).

    Pair submit controls with <x-busy-submit> inside the same form.

    <x-form-with-busy method="POST" action="..." :overlay="true" busy-message="{{ __('Saving…') }}">
        ...
        <x-busy-submit class="..." busy-text="{{ __('Saving…') }}">{{ __('Save') }}</x-busy-submit>
    </x-form-with-busy>
--}}
@props([
    'busyMessage' => null,
    'overlay' => false,
])
@php
    $msg = $busyMessage ?? __('Saving…');
@endphp
<form
    {{ $attributes->class($overlay ? ['relative', 'overflow-hidden'] : []) }}
    x-data="formWithBusy({ busyMessage: @js($msg), showOverlay: @json($overlay) })"
    @submit="handleSubmit"
    :aria-busy="submitting ? 'true' : 'false'"
>
    @if($overlay)
        <div
            x-show="submitting && showOverlay"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="absolute inset-0 z-20 flex items-center justify-center rounded-[inherit] bg-white/75 backdrop-blur-[1px]"
            role="status"
        >
            <span class="inline-flex max-w-[90%] items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-md sm:text-sm">
                <x-spinner class="h-4 w-4 text-slate-500" />
                <span x-text="busyMessage">{{ $msg }}</span>
            </span>
        </div>
    @endif
    {{ $slot }}
</form>
