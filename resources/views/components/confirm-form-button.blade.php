{{--
    Opens a modal instead of window.confirm(). Submit the hidden form on confirm.

    Slot: place a control with @click="open = true" (e.g. type="button") inside this component
    so it shares the same Alpine scope as the modal.

    @props: action, method (POST, DELETE, ...), title, message?, confirmLabel, cancelLabel, variant (danger|primary)
--}}
@props([
    'action',
    'method' => 'POST',
    'title' => __('Confirm'),
    'message' => null,
    'confirmLabel' => __('Confirm'),
    'cancelLabel' => __('Cancel'),
    'variant' => 'danger',
])

@php
    $m = strtoupper($method);
    $titleId = 'confirm-form-title-' . str_replace('.', '', uniqid('', true));
    $confirmClasses = $variant === 'primary'
        ? 'rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900'
        : 'rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900';
@endphp

<div {{ $attributes->class('shrink-0') }} x-data="{ open: false }" @keydown.escape.window="open = false">
    {{ $slot }}

    <form x-ref="confirmForm" method="POST" action="{{ $action }}" class="hidden">
        @csrf
        @if(! in_array($m, ['GET', 'POST'], true))
            @method($method)
        @endif
    </form>

    <div x-show="open"
         x-cloak
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="{{ $titleId }}">
        <div class="fixed inset-0 bg-black/45 backdrop-blur-sm" @click="open = false" aria-hidden="true"></div>
        <div x-show="open" @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative w-full max-w-md rounded-2xl bg-white text-left shadow-2xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                <h2 id="{{ $titleId }}" class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h2>
            </div>
            @if(filled($message))
                <div class="px-6 py-4 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ $message }}
                </div>
            @endif
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 px-6 py-4">
                <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ $cancelLabel }}
                </button>
                <button type="button" @click="$refs.confirmForm.submit()"
                        class="{{ $confirmClasses }}">
                    {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</div>
