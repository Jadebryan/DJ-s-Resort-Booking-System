<x-tenant-user::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('GCash payment') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Booking') }} #{{ $booking->id }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-lg mx-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @if($success ?? false)
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-700 mb-4">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Payment successful') }}</h2>
        @else
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-800 mb-4">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Payment status') }}</h2>
        @endif

        <p class="mt-3 text-sm text-gray-600">{{ $message }}</p>

        <div class="mt-6">
            <a href="{{ route('tenant.user.bookings.index') }}"
               class="inline-flex w-full items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
                {{ __('Back to My Bookings') }}
            </a>
        </div>
    </div>
</x-tenant-user::app-layout>
