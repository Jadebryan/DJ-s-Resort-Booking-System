@php
    $bookingsForDetails = collect($bookings ?? [])->map(fn($b) => [
        'id' => $b->id,
        'room_name' => $b->room?->name ?? '—',
        'guest_name' => $b->guest_name ?? $b->user?->name ?? '—',
        'guest_email' => $b->guest_email ?? $b->user?->email ?? '',
        'guest_phone' => $b->guest_phone ?? '',
        'check_in' => $b->check_in?->format('Y-m-d'),
        'check_out' => $b->check_out?->format('Y-m-d'),
        'status' => $b->status,
        'notes' => $b->notes ?? '',
        'amount_payable' => $b->amount_payable ?? 0,
        'payment_type' => $b->payment_type ?? null,
        'is_fully_paid' => (bool) ($b->is_fully_paid ?? false),
        'amount_paid' => $b->amount_paid ?? null,
        'payer_full_name' => $b->payer_full_name ?? '',
        'payer_method' => $b->payer_gcash_no ?? '',
        'payer_ref' => $b->payer_ref_no ?? '',
    ])->values()->all();
@endphp
<x-tenant::app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3 min-w-0">
            <div class="leading-tight min-w-0">
                <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Bookings') }}</h1>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Manage guest reservations and payment status.') }}</p>
            </div>
            @if($canUseCalendar ?? false)
                <a href="{{ tenant_url('bookings/calendar') }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-700 sm:px-4 sm:text-sm">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('Calendar') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6" x-data="{
        proofModalOpen: false,
        proofImage: '',
        proofName: '',
        proofMethod: '',
        proofRef: '',
        detailsModalOpen: false,
        selectedBooking: null,
        bookingsForDetails: @js($bookingsForDetails),
        openProof(url, name, method, ref) {
            this.proofImage = url;
            this.proofName = name || '—';
            this.proofMethod = method || '—';
            this.proofRef = ref || '—';
            this.proofModalOpen = true;
        },
        closeProof() { this.proofModalOpen = false; },
        openDetails(id) {
            const b = this.bookingsForDetails.find(x => x.id == id);
            this.selectedBooking = b ? { ...b } : null;
            this.detailsModalOpen = !!this.selectedBooking;
        },
        closeDetails() {
            this.detailsModalOpen = false;
            this.selectedBooking = null;
        }
    }" @keydown.escape.window="proofModalOpen = false; detailsModalOpen = false">
        @php
            $bc = collect($bookings ?? []);
            $bPending = $bc->where('status', 'pending')->count();
            $bConfirmed = $bc->where('status', 'confirmed')->count();
            $bCancelled = $bc->where('status', 'cancelled')->count();
        @endphp
        @if($bookings->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 w-full min-w-0 max-w-4xl">
                <div class="rounded-xl border border-gray-200 bg-white/90 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">{{ __('Total') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-gray-900">{{ $bc->count() }}</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-800">{{ __('Pending') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-amber-950">{{ $bPending }}</p>
                </div>
                <div class="rounded-xl border border-teal-100 bg-teal-50/60 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-teal-800">{{ __('Confirmed') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-teal-950">{{ $bConfirmed }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50/80 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-600">{{ __('Cancelled') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-gray-800">{{ $bCancelled }}</p>
                </div>
            </div>
        @endif

        <div class="w-full min-w-0 max-w-full rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            @if($bookings->isEmpty())
                <div class="p-8 text-center text-gray-600">
                    <p>No bookings yet. Bookings will appear here when customers make reservations.</p>
                </div>
            @else
                <div class="w-full min-w-0 overflow-x-auto">
                    <table class="min-w-[720px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Room</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Guest</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Check-in</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Check-out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($bookings as $booking)
                                <tr class="transition hover:bg-gray-50/50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $booking->room?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $booking->guest_name ?? $booking->user?->name ?? '—' }}
                                        @if($booking->guest_email ?? $booking->user?->email)
                                            <span class="block text-xs text-gray-500">{{ $booking->guest_email ?? $booking->user?->email }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->check_in?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->check_out?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($booking->status === 'confirmed')
                                            <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-700">Confirmed</span>
                                        @elseif($booking->status === 'cancelled')
                                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">Cancelled</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($booking->payment_proof_path)
                                            <div class="space-y-0.5">
                                                <div class="text-gray-900 font-medium">{{ $booking->payer_full_name ?? '—' }}</div>
                                                <div class="text-xs text-gray-500">Method: {{ $booking->payer_gcash_no ?? '—' }}</div>
                                                <div class="text-xs text-gray-500">Ref: {{ $booking->payer_ref_no ?? '—' }}</div>
                                                <button type="button"
                                                    data-proof-url="{{ asset('storage/' . $booking->payment_proof_path) }}"
                                                    data-proof-name="{{ e($booking->payer_full_name ?? '—') }}"
                                                    data-proof-method="{{ e($booking->payer_gcash_no ?? '—') }}"
                                                    data-proof-ref="{{ e($booking->payer_ref_no ?? '—') }}"
                                                    @click="openProof($event.currentTarget.dataset.proofUrl, $event.currentTarget.dataset.proofName, $event.currentTarget.dataset.proofMethod, $event.currentTarget.dataset.proofRef)"
                                                    class="inline-block text-teal-600 hover:text-teal-800 font-medium">
                                                    View proof
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <button type="button"
                                                @click="openDetails({{ $booking->id }})"
                                                title="See details"
                                                class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 p-1.5 text-teal-700 transition hover:bg-teal-100 mr-2"
                                                aria-label="See details">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        @if($booking->status === 'pending')
                                            <form action="{{ tenant_url('bookings/' . $booking->id . '/confirm') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Confirm" aria-label="Confirm booking" class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 p-1.5 text-teal-700 transition hover:bg-teal-100 mr-2">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                            <x-confirm-form-button
                                                class="inline-block"
                                                :action="tenant_url('bookings/' . $booking->id . '/cancel')"
                                                method="POST"
                                                :title="__('Cancel booking')"
                                                :message="__('Cancel this booking? The guest may need to be notified separately.')"
                                                :confirm-label="__('Cancel booking')">
                                                <button type="button" @click="open = true" title="{{ __('Cancel') }}" aria-label="{{ __('Cancel booking') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-1.5 text-red-600 transition hover:bg-red-50">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </x-confirm-form-button>
                                        @elseif($booking->status === 'confirmed')
                                            <x-confirm-form-button
                                                class="inline-block"
                                                :action="tenant_url('bookings/' . $booking->id . '/cancel')"
                                                method="POST"
                                                :title="__('Cancel booking')"
                                                :message="__('Cancel this booking? The guest may need to be notified separately.')"
                                                :confirm-label="__('Cancel booking')">
                                                <button type="button" @click="open = true" title="{{ __('Cancel') }}" aria-label="{{ __('Cancel booking') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-1.5 text-red-600 transition hover:bg-red-50">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </x-confirm-form-button>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Payment proof image modal --}}
        <div x-show="proofModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="closeProof()" class="fixed inset-0 bg-black/60"></div>
            <div x-show="proofModalOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative max-w-2xl w-full max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between shrink-0 border-b border-gray-200 px-4 py-3">
                    <h3 class="text-lg font-semibold text-gray-900">Payment proof</h3>
                    <button type="button" @click="closeProof()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 space-y-3 shrink-0 border-b border-gray-100 bg-gray-50/50">
                    <p class="text-sm text-gray-700"><span class="font-medium">Name:</span> <span x-text="proofName"></span></p>
                    <p class="text-sm text-gray-700"><span class="font-medium">Method:</span> <span x-text="proofMethod"></span></p>
                    <p class="text-sm text-gray-700"><span class="font-medium">Ref. No:</span> <span x-text="proofRef"></span></p>
                </div>
                <div class="flex-1 overflow-auto p-4 flex items-center justify-center min-h-[200px] bg-gray-100">
                    <img :src="proofImage" alt="Payment proof" class="max-w-full max-h-[60vh] w-auto h-auto object-contain rounded-lg shadow-inner">
                </div>
            </div>
        </div>

        {{-- Booking details modal --}}
        <div x-show="detailsModalOpen && selectedBooking" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="closeDetails()" class="fixed inset-0 bg-black/60"></div>
            <div x-show="detailsModalOpen && selectedBooking" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative max-w-xl w-full max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between shrink-0 border-b border-gray-200 px-4 py-3">
                    <h3 class="text-lg font-semibold text-gray-900">Booking details</h3>
                    <button type="button" @click="closeDetails()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Room</p>
                        <p class="mt-1 font-semibold text-gray-900" x-text="selectedBooking.room_name"></p>
                        <p class="mt-2 text-sm font-semibold text-teal-700">
                            Amount payable:
                            ₱<span x-text="Number(selectedBooking.amount_payable || 0).toLocaleString('en-PH', { maximumFractionDigits: 0 })"></span>
                        </p>
                        <p class="mt-2 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  :class="{
                                      'bg-teal-100 text-teal-700': selectedBooking.status === 'confirmed',
                                      'bg-gray-100 text-gray-600': selectedBooking.status === 'cancelled',
                                      'bg-amber-100 text-amber-800': selectedBooking.status === 'pending'
                                  }"
                                  x-text="selectedBooking.status ? selectedBooking.status.charAt(0).toUpperCase() + selectedBooking.status.slice(1) : ''"></span>
                            <template x-if="selectedBooking.is_fully_paid">
                                <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                    Fully paid
                                </span>
                            </template>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Check-in</p>
                            <p class="mt-1 text-sm text-gray-900" x-text="selectedBooking.check_in"></p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Check-out</p>
                            <p class="mt-1 text-sm text-gray-900" x-text="selectedBooking.check_out"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Guest name</p>
                            <p class="mt-1 text-sm text-gray-900" x-text="selectedBooking.guest_name"></p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Guest phone</p>
                            <p class="mt-1 text-sm text-gray-900" x-text="selectedBooking.guest_phone || '—'"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Guest email</p>
                        <p class="mt-1 text-sm text-gray-900" x-text="selectedBooking.guest_email || '—'"></p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Notes</p>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line" x-text="selectedBooking.notes || '—'"></p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment</p>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="font-medium">Type:</span>
                            <span x-text="selectedBooking.payment_type ? (selectedBooking.payment_type === 'full' ? 'Full payment' : 'Partial payment') : '—'"></span>
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="font-medium">Amount paid:</span>
                            <span x-text="selectedBooking.amount_paid != null ? '₱' + Number(selectedBooking.amount_paid).toLocaleString('en-PH', { maximumFractionDigits: 2 }) : '—'"></span>
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="font-medium">Payer:</span>
                            <span x-text="selectedBooking.payer_full_name || '—'"></span>
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="font-medium">Method:</span>
                            <span x-text="selectedBooking.payer_method || '—'"></span>
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="font-medium">Ref. No:</span>
                            <span x-text="selectedBooking.payer_ref || '—'"></span>
                        </p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button"
                                @click="closeDetails()"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-tenant::app-layout>
