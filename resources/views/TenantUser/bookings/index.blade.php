@php
    $tenant = current_tenant();
    $rooms = $rooms ?? collect();
    $roomsForBooking = $rooms->map(fn($r) => [
        'id' => $r->id,
        'name' => $r->name,
        'type' => $r->type,
        'capacity' => $r->capacity,
        'price_per_night' => (float) $r->price_per_night,
        'description' => $r->description,
        'image_url' => $r->image_path ? asset('storage/' . $r->image_path) : asset('images/background.jpg'),
    ])->values()->all();
    $storeUrl = tenant_url('book');
    $bookingsForDetails = collect($bookings ?? [])->map(fn($b) => [
        'id' => $b->id,
        'room_name' => $b->room?->name ?? 'Room',
        'check_in' => $b->check_in?->format('Y-m-d'),
        'check_out' => $b->check_out?->format('Y-m-d'),
        'guest_name' => $b->guest_name ?? '',
        'guest_email' => $b->guest_email ?? '',
        'guest_phone' => $b->guest_phone ?? '',
        'notes' => $b->notes ?? '',
        'amount_payable' => $b->amount_payable ?? 0,
        'status' => $b->status,
        'is_editable' => $b->status !== 'cancelled',
        'payment_type' => $b->payment_type ?? null,
        'amount_paid' => $b->amount_paid ?? null,
    ])->values()->all();
@endphp
<x-tenant-user::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('My Bookings') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Track status, pay online, or book another stay.') }}</p>
        </div>
    </x-slot>

    @php
        $openPaymentModal = session('openPaymentModal');
        $openDetailsModal = session('openDetailsModal');
        $openBookModalRoomId = session('openBookModalRoomId');
        $oldDetails = array_filter([
            'check_in' => old('check_in'),
            'check_out' => old('check_out'),
            'guest_name' => old('guest_name'),
            'guest_email' => old('guest_email'),
            'guest_phone' => old('guest_phone'),
            'notes' => old('notes'),
        ], fn($v) => $v !== null && $v !== '');
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-5" x-data="{
        browseModalOpen: false,
        bookModalOpen: false,
        payModalOpen: false,
        detailsModalOpen: false,
        payingBookingId: null,
        selectedRoom: null,
        selectedBooking: null,
        bookCheckIn: '',
        bookCheckOut: '',
        roomsForBooking: @js($roomsForBooking),
        bookingsForDetails: @js($bookingsForDetails),
        openBookModal(roomId) {
            this.selectedRoom = this.roomsForBooking.find(r => r.id == roomId) || null;
            this.bookModalOpen = !!this.selectedRoom;
            this.browseModalOpen = false;
            this.bookCheckIn = '';
            this.bookCheckOut = '';
        },
        openPayModal(bookingId) {
            this.payingBookingId = bookingId;
            this.payModalOpen = true;
        },
        closePayModal() {
            this.payModalOpen = false;
            this.payingBookingId = null;
        },
        openDetailsModal(bookingId) {
            const b = this.bookingsForDetails.find(x => x.id == bookingId);
            this.selectedBooking = b ? { ...b } : null;
            if (this.selectedBooking && this.oldDetails && Object.keys(this.oldDetails).length) {
                Object.assign(this.selectedBooking, this.oldDetails);
            }
            this.detailsModalOpen = !!this.selectedBooking;
        },
        oldDetails: @json($oldDetails),
        closeDetailsModal() {
            this.detailsModalOpen = false;
            this.selectedBooking = null;
        },
        closeBookModal() {
            this.bookModalOpen = false;
            this.selectedRoom = null;
            this.bookCheckIn = '';
            this.bookCheckOut = '';
        }
        ,
        nights() {
            if (!this.bookCheckIn || !this.bookCheckOut) return 0;
            const a = new Date(this.bookCheckIn);
            const b = new Date(this.bookCheckOut);
            if (Number.isNaN(a.getTime()) || Number.isNaN(b.getTime())) return 0;
            const diff = Math.ceil((b.getTime() - a.getTime()) / 86400000);
            return Math.max(0, diff);
        },
        payable() {
            const n = this.nights();
            const rate = Number(this.selectedRoom?.price_per_night || 0);
            if (!rate || n <= 0) return 0;
            return rate * n;
        },
        money(v) {
            try {
                return Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            } catch (e) {
                return String(v || 0);
            }
        }
    }" @keydown.escape.window="bookModalOpen = false; browseModalOpen = false; payModalOpen = false; detailsModalOpen = false; selectedRoom = null; payingBookingId = null; selectedBooking = null"
       x-init="$nextTick(() => { @js($openPaymentModal) && openPayModal(@js($openPaymentModal)); @js($openDetailsModal) && openDetailsModal(@js($openDetailsModal)); @js($openBookModalRoomId) && openBookModal(@js($openBookModalRoomId)); })">

        @if(!empty($calendarPayload))
            @php
                $cal = $calendarPayload;
                $bookingCellListUrl = tenant_url('user/bookings').'?'.http_build_query(['year' => $cal['year'], 'month' => $cal['month']]);
            @endphp
            <div class="w-full min-w-0">
                @include('Tenant.bookings.partials.booking-calendar-section', array_merge($cal, [
                    'calendarNavPath' => 'user/bookings',
                    'bookingsListSectionId' => 'guest-bookings-list',
                    'bookingCellListUrl' => $bookingCellListUrl,
                    'bookingCellAnchorPrefix' => '#guest-booking-',
                    'calendarSubtitle' => __('Your stays overlapping this month.'),
                    'newBookingUrl' => tenant_url('book'),
                ]))
            </div>
        @endif

        @php
            $bu = collect($bookings ?? []);
            $buPending = $bu->where('status', 'pending')->count();
            $buConfirmed = $bu->where('status', 'confirmed')->count();
            $buCancelled = $bu->where('status', 'cancelled')->count();
        @endphp
        @if($bookings->isNotEmpty())
            <x-stat-kpi-toggle storage-key="mtrbs.tenantUser.bookings-index.kpi.hidden" grid-class="grid grid-cols-2 gap-3 sm:grid-cols-4 w-full min-w-0" accent="teal">
                <div class="rounded-xl border border-gray-200 bg-white/90 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500">{{ __('Total') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-gray-900">{{ $bu->count() }}</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-800">{{ __('Pending') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-amber-950">{{ $buPending }}</p>
                </div>
                <div class="rounded-xl border border-teal-100 bg-teal-50/60 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-teal-800">{{ __('Confirmed') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-teal-950">{{ $buConfirmed }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50/80 px-3 py-2.5 shadow-sm">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-gray-600">{{ __('Cancelled') }}</p>
                    <p class="text-xl font-semibold tabular-nums text-gray-800">{{ $buCancelled }}</p>
                </div>
            </x-stat-kpi-toggle>
        @endif

        <div id="guest-bookings-list" class="scroll-mt-24 space-y-4">
        @if($bookings->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 shadow-sm text-center">
                <p class="text-sm text-gray-600">{{ __('You have no bookings yet.') }}</p>
                <button type="button" @click="browseModalOpen = true"
                    class="mt-4 inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 transition">
                    {{ __('Book a room') }}
                </button>
            </div>
        @else
            <x-list-grid-toggle storage-key="mtrbs.tenantUser.bookings.index.view" default-view="grid" accent="teal">
                <x-slot name="list">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                        <table class="min-w-[640px] w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Room') }}</th>
                                    <th class="px-4 py-3">{{ __('Dates') }}</th>
                                    <th class="px-4 py-3">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($bookings as $booking)
                                    <tr id="guest-booking-{{ $booking->id }}" class="scroll-mt-24 hover:bg-gray-50/80">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $booking->room?->name ?? __('Room') }}</td>
                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $booking->check_in?->format('M j, Y') }} – {{ $booking->check_out?->format('M j, Y') }}</td>
                                        <td class="px-4 py-3 tabular-nums text-gray-800">₱{{ number_format($booking->amount_payable, 0) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                @if($booking->status === 'confirmed') bg-teal-100 text-teal-800
                                                @elseif($booking->status === 'cancelled') bg-gray-100 text-gray-600
                                                @else bg-amber-100 text-amber-800 @endif">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" @click="openDetailsModal({{ $booking->id }})" class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 p-1.5 text-teal-700 hover:bg-teal-100" aria-label="{{ __('See details') }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                            @if($booking->status !== 'cancelled' && !$booking->is_fully_paid)
                                                <button type="button" @click="openPayModal({{ $booking->id }})" class="ml-1 inline-flex items-center rounded-lg bg-teal-500 px-2 py-1 text-xs font-medium text-white hover:bg-teal-600">{{ __('Pay') }}</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-slot>
                <x-slot name="grid">
            <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
                @foreach($bookings as $booking)
                    <div id="guest-booking-{{ $booking->id }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm min-w-0 scroll-mt-24">
                        <div class="flex flex-wrap items-start justify-between gap-3 min-w-0">
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-900 truncate" title="{{ $booking->room?->name ?? 'Room' }}">{{ $booking->room?->name ?? 'Room' }}</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $booking->check_in?->format('M j, Y') }} – {{ $booking->check_out?->format('M j, Y') }}
                                </p>
                                <p class="mt-2 text-sm font-semibold text-teal-700">
                                    Amount payable: ₱{{ number_format($booking->amount_payable, 0) }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($booking->status === 'confirmed') bg-teal-100 text-teal-800
                                @elseif($booking->status === 'cancelled') bg-gray-100 text-gray-600
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button type="button" @click="openDetailsModal({{ $booking->id }})"
                                title="See details"
                                class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 p-1.5 text-teal-700 transition hover:bg-teal-100"
                                aria-label="See details">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            @if($booking->status !== 'cancelled')
                                @if($booking->is_fully_paid)
                                    <span class="inline-flex items-center rounded-lg bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-500 opacity-60 cursor-not-allowed" aria-disabled="true">
                                        Paid
                                    </span>
                                @else
                                    <button type="button" @click="openPayModal({{ $booking->id }})"
                                       class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-teal-600 transition">
                                        Pay
                                    </button>
                                @endif
                            @endif
                            <button type="button" @click="browseModalOpen = true"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                Book another room
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
                </x-slot>
            </x-list-grid-toggle>
        @endif
        </div>

        {{-- Rooms list modal (Browse) --}}
        <div x-show="browseModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="browseModalOpen = false" class="fixed inset-0 bg-black/50"></div>
            <div x-show="browseModalOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between shrink-0 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-900">Available rooms</h2>
                    <button type="button" @click="browseModalOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    @if($rooms->isEmpty())
                        <p class="text-center text-gray-500 py-8">No rooms available at the moment.</p>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($rooms as $room)
                                @php $roomImgUrl = $room->image_path ? asset('storage/' . $room->image_path) : asset('images/background.jpg'); @endphp
                                <button type="button" @click="openBookModal({{ $room->id }})"
                                   class="group rounded-xl border border-gray-200 bg-white overflow-hidden hover:border-teal-200 hover:shadow-md transition text-left w-full">
                                    <div class="aspect-[4/3] bg-gray-200 relative overflow-hidden">
                                        <img src="{{ $roomImgUrl }}" alt="{{ $room->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                             onerror="this.onerror=null; this.src='{{ asset('images/background.jpg') }}';">
                                        <span class="absolute top-3 left-3 inline-flex items-center rounded-full text-[10px] px-2 py-0.5 font-medium border capitalize shadow-sm bg-teal-100 text-teal-700 border-teal-200">{{ $room->type }}</span>
                                        <span class="absolute top-3 right-3 text-sm font-semibold text-white drop-shadow-md bg-gray-900/60 px-2 py-1 rounded-lg">₱{{ number_format($room->price_per_night, 0) }}/night</span>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 group-hover:text-teal-700">{{ $room->name }}</h3>
                                        <p class="mt-1 text-xs text-gray-500">
                                            @if($room->capacity)
                                                Up to {{ $room->capacity }} guests
                                            @endif
                                            @if($room->description)
                                                {{ $room->capacity ? ' · ' : '' }}{{ Str::limit($room->description, 50) }}
                                            @endif
                                        </p>
                                        <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-teal-600">Book this room →</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Book form modal --}}
        <div x-show="bookModalOpen && selectedRoom" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="closeBookModal()" class="fixed inset-0 bg-black/50"></div>
            <div x-show="bookModalOpen && selectedRoom" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="shrink-0 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900" x-text="selectedRoom ? selectedRoom.name : ''"></h2>
                    <button type="button" @click="closeBookModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-gray-50">
                        <div class="aspect-video bg-gray-200 relative" x-show="selectedRoom">
                            <img :src="selectedRoom && selectedRoom.image_url ? selectedRoom.image_url : '{{ asset('images/background.jpg') }}'"
                                 :alt="selectedRoom ? selectedRoom.name : ''"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='{{ asset('images/background.jpg') }}'">
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="inline-flex items-center rounded-full text-[10px] px-2 py-0.5 font-medium border capitalize bg-teal-100 text-teal-700 border-teal-200"
                                      x-text="selectedRoom ? selectedRoom.type : ''"></span>
                                <span class="text-sm font-semibold text-gray-900" x-show="selectedRoom">
                                    ₱<span x-text="selectedRoom ? selectedRoom.price_per_night.toLocaleString('en-PH', {maximumFractionDigits:0}) : ''"></span><span class="text-gray-500 font-normal">/night</span>
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-500" x-show="selectedRoom && selectedRoom.capacity" x-text="selectedRoom && selectedRoom.capacity ? 'Up to ' + selectedRoom.capacity + ' guests' : ''"></p>
                            <p class="mt-2 text-sm text-gray-600" x-show="selectedRoom && selectedRoom.description" x-text="selectedRoom ? selectedRoom.description : ''"></p>
                        </div>
                    </div>

                    <form method="POST" action="{{ $storeUrl }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="room_id" :value="selectedRoom ? selectedRoom.id : ''">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="book_check_in" class="block text-sm font-medium text-gray-900 mb-1">Check-in</label>
                                <input type="date" id="book_check_in" name="check_in" required min="{{ date('Y-m-d') }}"
                                       x-ref="bookCheckIn"
                                       x-model="bookCheckIn"
                                       @change="let d = $event.target.value; bookCheckIn = d; if (d && $refs.bookCheckOut) { let next = new Date(d); next.setDate(next.getDate() + 1); $refs.bookCheckOut.min = next.toISOString().slice(0,10); if ($refs.bookCheckOut.value && $refs.bookCheckOut.value < $refs.bookCheckOut.min) { $refs.bookCheckOut.value = ''; bookCheckOut = ''; } }"
                                       class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                            </div>
                            <div>
                                <label for="book_check_out" class="block text-sm font-medium text-gray-900 mb-1">Check-out</label>
                                <input type="date" id="book_check_out" name="check_out" required
                                       x-ref="bookCheckOut"
                                       x-model="bookCheckOut"
                                       @input="bookCheckOut = $event.target.value"
                                       class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-900">{{ __('Amount payable') }}</p>
                                <p class="text-sm font-bold text-teal-700 tabular-nums">
                                    ₱<span x-text="money(payable())"></span>
                                </p>
                            </div>
                            <p class="mt-1 text-xs text-gray-600">
                                <span x-text="nights() ? (nights() + ' night(s) × ₱' + money(selectedRoom ? selectedRoom.price_per_night : 0) + '/night') : '{{ __('Select your dates to calculate the total.') }}'"></span>
                            </p>
                        </div>

                        <p class="text-sm text-gray-600">Booking as <strong>{{ auth('regular_user')->user()->name }}</strong> ({{ auth('regular_user')->user()->email }})</p>

                        <div>
                            <label for="book_notes_idx" class="block text-sm font-medium text-gray-900 mb-1">Notes (optional)</label>
                            <textarea id="book_notes_idx" name="notes" rows="2" placeholder="Special requests..."
                                      {{ \App\Support\InputHtmlAttributes::textarea(1000) }}
                                      class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900"></textarea>
                        </div>
                        @error('room_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('check_in') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('check_out') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Payment details (required)</p>
                                    <p class="text-xs text-gray-500">Upload proof now so your request includes payment right away.</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Payment type</label>
                                <select name="payment_type" required
                                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                    <option value="">Select</option>
                                    <option value="full" {{ old('payment_type') === 'full' ? 'selected' : '' }}>Full payment</option>
                                    <option value="partial" {{ old('payment_type') === 'partial' ? 'selected' : '' }}>Partial payment</option>
                                </select>
                                @error('payment_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Full Name</label>
                                <input name="payer_full_name" type="text" value="{{ old('payer_full_name', auth('regular_user')->user()->name) }}" required
                                       {{ \App\Support\InputHtmlAttributes::personName() }}
                                       class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                @error('payer_full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">Payment method</label>
                                    <select name="payer_gcash_no" required
                                            class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                        <option value="">Select method</option>
                                        @foreach (['GCash', 'Maya', 'GrabPay', 'ShopeePay', 'Coins.ph', 'BPI Online', 'BDO Online', 'UnionBank Online', 'PNB Digital', 'Other wallet / bank'] as $method)
                                            <option value="{{ $method }}" {{ old('payer_gcash_no') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                    @error('payer_gcash_no') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">Ref. No.</label>
                                    <input name="payer_ref_no" type="text" value="{{ old('payer_ref_no') }}" required
                                           {{ \App\Support\InputHtmlAttributes::reference() }}
                                           class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                    @error('payer_ref_no') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">Amount paid (PHP)</label>
                                    <input name="amount_paid" type="number" step="0.01" min="0" value="{{ old('amount_paid') }}" required
                                           {{ \App\Support\InputHtmlAttributes::money() }}
                                           class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                    @error('amount_paid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">Payment proof (JPG/PNG)</label>
                                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png" required
                                           class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100">
                                    @error('payment_proof') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl font-semibold text-white shadow-lg hover:opacity-95 transition text-sm bg-teal-500 hover:bg-teal-600">
                            Submit booking with payment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Pay modal (upload proof + details) --}}
        <div x-show="payModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="closePayModal()" class="fixed inset-0 bg-black/50"></div>
            <div x-show="payModalOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="shrink-0 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Upload payment proof</h2>
                    <button type="button" @click="closePayModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <p class="text-sm text-gray-600">After uploading, the resort will verify your payment and then confirm your booking.</p>

                    @if(!empty($paymongoEnabled))
                        <div class="rounded-xl border border-teal-200 bg-teal-50/60 p-4 space-y-3">
                            <p class="text-sm font-semibold text-teal-900">{{ __('Pay with GCash (online)') }}</p>
                            <p class="text-xs text-teal-800/90">{{ __('You will be redirected to GCash via PayMongo. Minimum amount is ₱20. The resort still confirms your booking after payment.') }}</p>
                            <form method="POST"
                                  :action="payingBookingId ? `{{ tenant_path_prefix() }}/user/bookings/${payingBookingId}/pay-gcash` : '#'"
                                  class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">{{ __('Payment type') }}</label>
                                    <select name="payment_type" required
                                            class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="full">{{ __('Full payment') }}</option>
                                        <option value="partial">{{ __('Partial payment') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">{{ __('Amount (₱) — partial only') }}</label>
                                    <input type="number" name="amount_paid" step="0.01" min="0"
                                           {{ \App\Support\InputHtmlAttributes::money() }}
                                           class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900"
                                           placeholder="{{ __('Leave blank for full payment') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">{{ __('Name on GCash / billing') }}</label>
                                    <input name="payer_full_name" type="text" required value="{{ old('payer_full_name', auth('regular_user')->user()->name) }}"
                                           {{ \App\Support\InputHtmlAttributes::personName() }}
                                           class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                </div>
                                <button type="submit"
                                        :disabled="!payingBookingId"
                                        class="w-full rounded-xl bg-teal-600 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 disabled:cursor-not-allowed disabled:bg-gray-400">
                                    {{ __('Continue to GCash') }}
                                </button>
                            </form>
                        </div>
                        <p class="text-center text-xs font-medium text-gray-500">{{ __('— or upload proof manually —') }}</p>
                    @endif

                    <form method="POST"
                          :action="`{{ tenant_path_prefix() }}/user/bookings/${payingBookingId}/upload-proof`"
                          enctype="multipart/form-data"
                          class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Payment type</label>
                            <select name="payment_type" required
                                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                <option value="">Select</option>
                                <option value="full" {{ old('payment_type') === 'full' ? 'selected' : '' }}>Full payment</option>
                                <option value="partial" {{ old('payment_type') === 'partial' ? 'selected' : '' }}>Partial payment</option>
                            </select>
                            @error('payment_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Full Name</label>
                            <input name="payer_full_name" type="text" value="{{ old('payer_full_name') }}" required
                                   {{ \App\Support\InputHtmlAttributes::personName() }}
                                   class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                            @error('payer_full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Payment method</label>
                                <select name="payer_gcash_no" required
                                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                    <option value="">Select method</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Maya">Maya</option>
                                    <option value="GrabPay">GrabPay</option>
                                    <option value="ShopeePay">ShopeePay</option>
                                    <option value="Coins.ph">Coins.ph</option>
                                    <option value="BPI Online">BPI Online</option>
                                    <option value="BDO Online">BDO Online</option>
                                    <option value="UnionBank Online">UnionBank Online</option>
                                    <option value="PNB Digital">PNB Digital</option>
                                    <option value="Other wallet / bank">Other wallet / bank</option>
                                </select>
                                @error('payer_gcash_no') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Ref. No.</label>
                                <input name="payer_ref_no" type="text" value="{{ old('payer_ref_no') }}" required
                                       {{ \App\Support\InputHtmlAttributes::reference() }}
                                       class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                @error('payer_ref_no') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Amount paid (₱)</label>
                                <input name="amount_paid" type="number" step="0.01" min="0" value="{{ old('amount_paid') }}" required
                                       {{ \App\Support\InputHtmlAttributes::money() }}
                                       class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                                @error('amount_paid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Payment proof (photo)</label>
                                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png" required
                                       class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100">
                                @error('payment_proof') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="closePayModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                                Submit proof
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Booking details modal (view + edit) --}}
        <div x-show="detailsModalOpen && selectedBooking" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="closeDetailsModal()" class="fixed inset-0 bg-black/50"></div>
            <div x-show="detailsModalOpen && selectedBooking" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="shrink-0 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Booking details</h2>
                    <button type="button" @click="closeDetailsModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <p class="text-sm text-gray-600">Review and update your booking below. Room cannot be changed.</p>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Room</p>
                        <p class="mt-1 font-semibold text-gray-900" x-text="selectedBooking && selectedBooking.room_name"></p>
                        <p class="mt-2 text-sm font-semibold text-teal-700">
                            Amount payable: ₱<span x-text="selectedBooking ? Number(selectedBooking.amount_payable).toLocaleString('en-PH', {maximumFractionDigits:0}) : ''"></span>
                        </p>
                        <p class="mt-1 text-sm text-gray-700" x-show="selectedBooking && selectedBooking.amount_paid != null">
                            Amount paid:
                            ₱<span x-text="Number(selectedBooking.amount_paid).toLocaleString('en-PH', { maximumFractionDigits: 2 })"></span>
                            <span class="ml-1 text-xs text-gray-500" x-show="selectedBooking.payment_type">
                                (<span x-text="selectedBooking.payment_type === 'full' ? 'Full payment' : 'Partial payment'"></span>)
                            </span>
                        </p>
                        <p class="mt-1">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  :class="{
                                      'bg-teal-100 text-teal-800': selectedBooking && selectedBooking.status === 'confirmed',
                                      'bg-gray-100 text-gray-600': selectedBooking && selectedBooking.status === 'cancelled',
                                      'bg-amber-100 text-amber-800': selectedBooking && selectedBooking.status === 'pending'
                                  }"
                                  x-text="selectedBooking ? selectedBooking.status.charAt(0).toUpperCase() + selectedBooking.status.slice(1) : ''"></span>
                        </p>
                    </div>

                    <form method="POST" :action="`{{ tenant_path_prefix() }}/user/bookings/${selectedBooking ? selectedBooking.id : ''}`" class="space-y-4" x-ref="detailsForm">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Check-in</label>
                                <input type="date" name="check_in" x-model="selectedBooking && selectedBooking.check_in"
                                       :min="'{{ date('Y-m-d') }}'"
                                       :readonly="selectedBooking && !selectedBooking.is_editable"
                                       :class="selectedBooking && !selectedBooking.is_editable ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-900">
                                @error('check_in') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1">Check-out</label>
                                <input type="date" name="check_out" x-model="selectedBooking && selectedBooking.check_out"
                                       :readonly="selectedBooking && !selectedBooking.is_editable"
                                       :class="selectedBooking && !selectedBooking.is_editable ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-900">
                                @error('check_out') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Guest name</label>
                            <input type="text" name="guest_name" x-model="selectedBooking && selectedBooking.guest_name"
                                   {{ \App\Support\InputHtmlAttributes::personName() }}
                                   :readonly="selectedBooking && !selectedBooking.is_editable"
                                   :class="selectedBooking && !selectedBooking.is_editable ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                   class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-900" placeholder="Your name">
                            @error('guest_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Guest email</label>
                            <input type="email" name="guest_email" x-model="selectedBooking && selectedBooking.guest_email"
                                   {{ \App\Support\InputHtmlAttributes::email() }}
                                   :readonly="selectedBooking && !selectedBooking.is_editable"
                                   :class="selectedBooking && !selectedBooking.is_editable ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                   class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-900" placeholder="email@example.com">
                            @error('guest_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Guest phone</label>
                            <input type="text" name="guest_phone" x-model="selectedBooking && selectedBooking.guest_phone"
                                   {{ \App\Support\InputHtmlAttributes::phone() }}
                                   :readonly="selectedBooking && !selectedBooking.is_editable"
                                   :class="selectedBooking && !selectedBooking.is_editable ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                   class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-900" placeholder="Phone number">
                            @error('guest_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Notes</label>
                            <textarea name="notes" rows="3" x-model="selectedBooking && selectedBooking.notes"
                                      {{ \App\Support\InputHtmlAttributes::textarea(1000) }}
                                      :readonly="selectedBooking && !selectedBooking.is_editable"
                                      :class="selectedBooking && !selectedBooking.is_editable ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                                      class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-900" placeholder="Special requests..."></textarea>
                            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <template x-if="selectedBooking && selectedBooking.is_editable">
                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="closeDetailsModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                                    Save changes
                                </button>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-tenant-user::app-layout>
