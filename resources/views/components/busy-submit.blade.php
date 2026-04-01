{{--
    Submit button with inline spinner. Must be inside <x-form-with-busy> (same form scope).

    @param string|null $busyText  Label while submitting (e.g. __('Signing in…')).
--}}
@props([
    'busyText' => null,
])
@php
    $busy = $busyText ?? __('Saving…');
@endphp
<button
    type="submit"
    {{ $attributes->merge([
        'class' => 'inline-flex min-h-10 items-center justify-center font-semibold transition disabled:cursor-not-allowed disabled:opacity-70',
    ]) }}
    :disabled="submitting"
>
    <span x-show="!submitting" class="inline-flex w-full items-center justify-center gap-2">
        {{ $slot }}
    </span>
    <span x-show="submitting" x-cloak class="inline-flex w-full items-center justify-center gap-2">
        <x-spinner class="h-4 w-4" />
        <span>{{ $busy }}</span>
    </span>
</button>
