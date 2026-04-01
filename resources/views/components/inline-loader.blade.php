{{--
    Livewire: inline text/spinner next to a control (pair with wire:target).

    <button wire:click="delete({{ $id }})" wire:target="delete({{ $id }})">Delete</button>
    <x-inline-loader target="delete({{ $id }})">{{ __('Deleting…') }}</x-inline-loader>

    Classic Blade forms: use <x-form-with-busy> + <x-busy-submit> instead (see resources/js/app.js formWithBusy).
--}}
@props([
    'target',
])

<span
    {{ $attributes->class('inline-flex items-center gap-1.5 text-xs font-medium text-slate-500') }}
    wire:loading.flex
    wire:target="{{ $target }}"
    role="status"
>
    <svg class="h-3.5 w-3.5 shrink-0 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    {{ $slot }}
</span>
