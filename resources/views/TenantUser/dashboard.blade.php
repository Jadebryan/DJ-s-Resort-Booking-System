@php
    $tenant = current_tenant();
    $primary = $tenant?->primary_color ?? '#0d9488';
    $secondary = $tenant?->secondary_color ?? '#0f766e';
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
    $guestBookingsCalUrl = tenant_url('user/dashboard').'#booking-calendar';
@endphp
<x-tenant-user::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Dashboard') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Your bookings and quick ways to reserve a stay.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-5" x-data="{
        browseModalOpen: false,
        bookModalOpen: false,
        selectedRoom: null,
        bookCheckIn: '',
        bookCheckOut: '',
        roomsForBooking: @js($roomsForBooking),
        openBookModal(roomId) {
            this.selectedRoom = this.roomsForBooking.find(r => r.id == roomId) || null;
            this.bookModalOpen = !!this.selectedRoom;
            this.browseModalOpen = false;
            this.bookCheckIn = '';
            this.bookCheckOut = '';
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
    }" @keydown.escape.window="bookModalOpen = false; browseModalOpen = false; selectedRoom = null"
       @open-browse.window="browseModalOpen = true">
        <p class="text-sm text-gray-600">
            {{ __('Welcome back,') }} <span class="font-semibold text-gray-900">{{ auth('regular_user')->user()->name }}</span>
        </p>

        <x-stat-kpi-toggle storage-key="mtrbs.tenantUser.dashboard.kpi.hidden" grid-class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5" accent="teal">
            <div class="rounded-xl border border-violet-100 bg-violet-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-violet-800/90">{{ __('Rooms to book') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-violet-950">{{ $availableRoomsCount ?? ($rooms->count()) }}</p>
                <button type="button" @click="$dispatch('open-browse')" class="mt-1 text-left text-xs font-semibold text-violet-800 hover:text-violet-950">{{ __('Browse →') }}</button>
            </div>
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">{{ __('Total bookings') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-indigo-950">{{ $totalBookings ?? 0 }}</p>
                <p class="mt-1 text-xs text-indigo-900/70">{{ __('All time') }}</p>
            </div>
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('Pending') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $pendingBookings ?? 0 }}</p>
                <p class="mt-1 text-xs text-amber-900/70">{{ __('Awaiting resort') }}</p>
            </div>
            <div class="rounded-xl border border-teal-100 bg-teal-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-teal-800/90">{{ __('Confirmed') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-teal-950">{{ $confirmedBookings ?? 0 }}</p>
                <p class="mt-1 text-xs text-teal-900/70">{{ __('Active stays') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-3 shadow-sm sm:col-span-2 lg:col-span-1">
                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-600">{{ __('Cancelled') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-800">{{ $cancelledBookings ?? 0 }}</p>
                <a href="{{ tenant_url('user/bookings') }}" class="mt-1 inline-flex text-xs font-semibold text-gray-700 hover:text-gray-900">{{ __('History →') }}</a>
            </div>
        </x-stat-kpi-toggle>

        @include('TenantUser.partials.guest-dashboard-calendar')

        <section>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">{{ __('Quick actions') }}</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ tenant_url('user/bookings') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('My bookings') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('List, pay, and edit stays') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ $totalBookings ?? 0 }} {{ __('booking(s)') }}</p>
                </a>

                <a href="{{ $guestBookingsCalUrl }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Booking calendar') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Month view of your stays') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('This month at a glance') }}</p>
                </a>

                <button type="button" @click="browseModalOpen = true"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md text-left w-full min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Book a room') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Browse and reserve a stay') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Opens room gallery') }}</p>
                </button>

                <a href="{{ tenant_url('user/profile') }}"
                   class="group flex flex-col rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm transition hover:border-teal-200 hover:shadow-md min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 group-hover:text-teal-700 truncate">{{ __('Profile') }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ __('Account & password') }}</p>
                                </div>
                            </div>
                    <p class="mt-3 text-xs text-gray-600">{{ __('Settings') }}</p>
                </a>
            </div>
        </section>

        {{-- Rooms list modal (Browse) --}}
        <div x-show="browseModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="browseModalOpen = false" class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm"></div>
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
            <div @click="closeBookModal()" class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm"></div>
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

                    <form method="POST" action="{{ $storeUrl }}" class="space-y-4">
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
                            <label for="book_notes" class="block text-sm font-medium text-gray-900 mb-1">Notes (optional)</label>
                            <textarea id="book_notes" name="notes" rows="2" placeholder="Special requests..."
                                      class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900"></textarea>
                    </div>

                        <button type="submit" class="w-full py-3 rounded-xl font-semibold text-white shadow-lg hover:opacity-95 transition text-sm bg-teal-500 hover:bg-teal-600">
                            Submit booking request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-tenant-user::app-layout>
