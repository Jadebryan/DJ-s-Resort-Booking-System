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
        'payment_type' => $b->payment_type ?: 'partial',
        'is_fully_paid' => (bool) ($b->is_fully_paid ?? false),
        'amount_paid' => $b->amount_paid !== null ? (float) $b->amount_paid : null,
        'payer_full_name' => $b->payer_full_name ?? '',
        'payer_gcash_no' => $b->payer_gcash_no ?? '',
        'payer_ref' => $b->payer_ref_no ?? '',
        'is_admin_editable' => $b->status !== 'cancelled' && !($b->is_fully_paid ?? false),
        'is_signed_in_guest' => (bool) ($b->regular_user_id ?? null),
    ])->values()->all();
    $adminEditOld = null;
    if (session('openTenantBookingAdminEditId') && $errors->any()) {
        $adminEditOld = [
            'booking_id' => (int) session('openTenantBookingAdminEditId'),
            'check_in' => old('check_in'),
            'check_out' => old('check_out'),
            'guest_name' => old('guest_name'),
            'guest_email' => old('guest_email'),
            'guest_phone' => old('guest_phone'),
            'notes' => old('notes'),
            'payment_type' => old('payment_type'),
            'payer_full_name' => old('payer_full_name'),
            'payer_gcash_no' => old('payer_gcash_no'),
            'payer_ref' => old('payer_ref_no'),
            'amount_paid' => old('amount_paid'),
        ];
    }
@endphp
<x-tenant::app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3 min-w-0">
            <div class="leading-tight min-w-0">
                <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Bookings') }}</h1>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Manage guest reservations and payment status.') }}</p>
            </div>
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
        adminEditOld: @js($adminEditOld),
        tenantCanEditBookings: @json(tenant_staff_can('bookings', 'update')),
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
            let merged = b ? { ...b } : null;
            if (merged && this.adminEditOld && Number(this.adminEditOld.booking_id) === Number(id)) {
                merged = {
                    ...merged,
                    check_in: this.adminEditOld.check_in ?? merged.check_in,
                    check_out: this.adminEditOld.check_out ?? merged.check_out,
                    guest_name: this.adminEditOld.guest_name ?? merged.guest_name,
                    guest_email: this.adminEditOld.guest_email ?? merged.guest_email,
                    guest_phone: this.adminEditOld.guest_phone ?? merged.guest_phone,
                    notes: this.adminEditOld.notes ?? merged.notes,
                    payment_type: this.adminEditOld.payment_type ?? merged.payment_type,
                    payer_full_name: this.adminEditOld.payer_full_name ?? merged.payer_full_name,
                    payer_gcash_no: this.adminEditOld.payer_gcash_no ?? merged.payer_gcash_no,
                    payer_ref: this.adminEditOld.payer_ref ?? merged.payer_ref,
                    amount_paid: this.adminEditOld.amount_paid !== undefined && this.adminEditOld.amount_paid !== null ? this.adminEditOld.amount_paid : merged.amount_paid,
                };
            }
            this.selectedBooking = merged;
            this.detailsModalOpen = !!this.selectedBooking;
        },
        closeDetails() {
            this.detailsModalOpen = false;
            this.selectedBooking = null;
        },
        init() {
            const reopenId = @js(session('openTenantBookingAdminEditId'));
            if (reopenId) {
                this.openDetails(reopenId);
            }
        }
    }" @keydown.escape.window="proofModalOpen = false; detailsModalOpen = false">
        @php
            $bc = collect($bookings ?? []);
            $bPending = $bc->where('status', 'pending')->count();
            $bConfirmed = $bc->where('status', 'confirmed')->count();
            $bCancelled = $bc->where('status', 'cancelled')->count();
        @endphp
        @if($bookings->isNotEmpty())
            <x-stat-kpi-toggle storage-key="mtrbs.tenant.bookings-index.kpi.hidden" grid-class="grid grid-cols-2 gap-3 sm:grid-cols-4 w-full min-w-0 max-w-4xl" accent="teal">
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
            </x-stat-kpi-toggle>
        @endif

        @if($canUseCalendar && $calendarPayload)
            @php
                $cal = $calendarPayload;
                $bookingCellListUrl = tenant_url('bookings') . '?' . http_build_query(['year' => $cal['year'], 'month' => $cal['month']]) . '#bookings-list';
            @endphp
            <div class="w-full min-w-0">
                @include('Tenant.bookings.partials.booking-calendar-section', array_merge($cal, [
                    'calendarNavPath' => 'bookings',
                    'bookingsListSectionId' => 'bookings-list',
                    'bookingCellListUrl' => $bookingCellListUrl,
                ]))
            </div>
        @endif

        <div id="bookings-list" class="w-full min-w-0 max-w-full scroll-mt-24 rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
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
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span class="font-medium text-gray-900">{{ $booking->guest_name ?? $booking->user?->name ?? '—' }}</span>
                                            @if($booking->regular_user_id)
                                                <span class="inline-flex shrink-0 items-center rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-800"
                                                      title="{{ __('Booked while logged into the guest portal') }}">{{ __('Signed in') }}</span>
                                            @else
                                                <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                                                      title="{{ __('Booked without a guest account') }}">{{ __('Public') }}</span>
                                            @endif
                                        </div>
                                        @if($booking->guest_email ?? $booking->user?->email)
                                            <span class="mt-0.5 block text-xs text-gray-500">{{ $booking->guest_email ?? $booking->user?->email }}</span>
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
                                        @if($booking->is_fully_paid && tenant_staff_can('bookings', 'read'))
                                            <a href="{{ tenant_url('bookings/'.$booking->id.'/receipt') }}"
                                               target="_blank" rel="noopener noreferrer"
                                               title="{{ __('Print payment receipt (thermal)') }}"
                                               class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-1.5 text-gray-700 transition hover:bg-gray-50 mr-2"
                                               aria-label="{{ __('Print receipt') }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </a>
                                        @endif
                                        @if($booking->status === 'pending')
                                            @if(tenant_staff_can('bookings', 'confirm'))
                                            <form action="{{ tenant_url('bookings/' . $booking->id . '/confirm') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Confirm" aria-label="Confirm booking" class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 p-1.5 text-teal-700 transition hover:bg-teal-100 mr-2">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                            @endif
                                            @if(tenant_staff_can('bookings', 'cancel'))
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
                                            @endif
                                            @if(! tenant_staff_can('bookings', 'confirm') && ! tenant_staff_can('bookings', 'cancel'))
                                            —
                                            @endif
                                        @elseif($booking->status === 'confirmed')
                                            @if(tenant_staff_can('bookings', 'cancel'))
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
                    @if ($errors->any() && session('openTenantBookingAdminEditId'))
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                            <p class="font-medium">{{ __('Please fix the errors below.') }}</p>
                            <ul class="mt-1 list-disc list-inside text-xs">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
                            <template x-if="!selectedBooking.is_fully_paid && selectedBooking.status !== 'cancelled'">
                                <span class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-900">
                                    Balance due / partial
                                </span>
                            </template>
                        </p>
                        <p class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-500">{{ __('Guest source') }}</span>
                            <span x-show="selectedBooking.is_signed_in_guest" class="inline-flex items-center rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-800">{{ __('Signed-in guest') }}</span>
                            <span x-show="!selectedBooking.is_signed_in_guest" class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">{{ __('Public / no account') }}</span>
                        </p>
                    </div>

                    <template x-if="selectedBooking && selectedBooking.is_admin_editable && tenantCanEditBookings">
                        <form method="POST" enctype="multipart/form-data" class="space-y-4"
                              :action="'{{ tenant_url('bookings') }}/' + selectedBooking.id">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Check-in</label>
                                    <input type="date" name="check_in" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.check_in">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Check-out</label>
                                    <input type="date" name="check_out" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.check_out">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Guest name</label>
                                    <input type="text" name="guest_name" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.guest_name">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Guest phone</label>
                                    <input type="text" name="guest_phone"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.guest_phone">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Guest email</label>
                                <input type="email" name="guest_email"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                       x-model="selectedBooking.guest_email">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Notes</label>
                                <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                          x-model="selectedBooking.notes"></textarea>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-3">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment</p>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment type</label>
                                    <select name="payment_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                            x-model="selectedBooking.payment_type">
                                        <option value="partial">Partial payment</option>
                                        <option value="full">Full payment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount paid (₱)</label>
                                    <input type="number" name="amount_paid" step="0.01" min="0" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.amount_paid">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Payer name</label>
                                    <input type="text" name="payer_full_name" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.payer_full_name">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Method / account</label>
                                    <input type="text" name="payer_gcash_no" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.payer_gcash_no">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference no.</label>
                                    <input type="text" name="payer_ref_no" required
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                           x-model="selectedBooking.payer_ref">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Replace payment proof (optional)</label>
                                    <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/jpg"
                                           class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-teal-800">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to keep the existing proof.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2 pt-2">
                                <button type="button" @click="closeDetails()"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                                    Save changes
                                </button>
                            </div>
                        </form>
                    </template>

                    <template x-if="selectedBooking && (!selectedBooking.is_admin_editable || !tenantCanEditBookings)">
                        <div class="space-y-4">
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
                                    <span x-text="selectedBooking.payer_gcash_no || '—'"></span>
                                </p>
                                <p class="mt-1 text-sm text-gray-900">
                                    <span class="font-medium">Ref. No:</span>
                                    <span x-text="selectedBooking.payer_ref || '—'"></span>
                                </p>
                            </div>

                            <template x-if="selectedBooking && selectedBooking.is_fully_paid">
                                <div class="no-print">
                                    <a :href="`{{ tenant_url('bookings') }}/` + selectedBooking.id + `/receipt`"
                                       target="_blank" rel="noopener noreferrer"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-100">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        {{ __('Open printable receipt') }}
                                    </a>
                                    <p class="mt-1 text-center text-[11px] text-gray-500">{{ __('Opens in a new tab; use Print for 80mm thermal printers.') }}</p>
                                </div>
                            </template>

                            <div class="flex justify-end pt-2">
                                <button type="button"
                                        @click="closeDetails()"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Close
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-tenant::app-layout>
