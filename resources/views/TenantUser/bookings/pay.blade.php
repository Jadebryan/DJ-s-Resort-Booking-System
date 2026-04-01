<x-tenant-user::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Pay for booking') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Upload proof so the resort can confirm your stay.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        <div class="max-w-2xl space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-gray-900">{{ $booking->room?->name ?? 'Room' }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $booking->check_in?->format('M j, Y') }} – {{ $booking->check_out?->format('M j, Y') }}
                    ({{ $nights }} night{{ $nights !== 1 ? 's' : '' }})
                </p>
                <p class="mt-4 text-2xl font-semibold text-gray-900">₱{{ number_format($amount, 2) }}</p>

                @if($amount > 0)
                    <p class="mt-4 text-sm font-medium text-gray-700">Upload payment proof</p>
                    <p class="mt-1 text-xs text-gray-500">The resort will verify your payment and then confirm your booking. You can pay via bank transfer or other method and upload a screenshot/receipt here.</p>
                    @if($booking->payment_proof_path)
                        <p class="mt-2 text-sm text-teal-600">Proof already uploaded. You can replace it below if needed.</p>
                    @endif
                    <form method="POST" action="{{ tenant_url('user/bookings/' . $booking->id . '/upload-proof') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                        @csrf
                        <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" {{ $booking->payment_proof_path ? '' : 'required' }} class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100">
                        @error('payment_proof')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-600 transition">
                            {{ $booking->payment_proof_path ? 'Replace proof' : 'Upload proof' }}
                        </button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-gray-500">This booking has no amount to pay.</p>
                @endif
            </div>

            <a href="{{ tenant_url('user/bookings') }}" class="inline-flex items-center text-sm font-medium text-teal-600 hover:text-teal-700">
                ← Back to My Bookings
            </a>
        </div>
    </div>
</x-tenant-user::app-layout>
