<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Rooms & Cottages') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Inventory guests can book—pricing, photos, and availability.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6" x-data="{
        rooms: @js($roomsForJs ?? []),
        openCreate: @js(session('openModal') === 'create'),
        openEdit: @js(session('openModal') === 'edit'),
        editRoomId: @js(session('editRoomId')),
        currentRoom: null,
        editForm: { name: '', description: '', type: 'room', capacity: '', price_per_night: '', is_available: true },
        listGridMode: 'grid',
        listGridStorageKey: 'mtrbs.tenant.rooms.index.view',
        roomFilter: '',
        typeFilter: 'all',
        statusFilter: 'all',
        init() {
            try {
                const v = localStorage.getItem(this.listGridStorageKey);
                if (v === 'grid' || v === 'list') {
                    this.listGridMode = v;
                }
            } catch (e) {}
            if (this.openEdit && this.editRoomId && this.rooms.length) {
                const r = this.rooms.find(room => room.id == this.editRoomId);
                if (r) {
                    this.currentRoom = r;
                    this.editForm = {
                        name: @js(old('name')),
                        description: @js(old('description')),
                        type: @js(old('type', 'room')),
                        capacity: @js(old('capacity')),
                        price_per_night: @js(old('price_per_night')),
                        is_available: @js(old('is_available', true))
                    };
                    if (this.editForm.name === null) this.editForm = { ...r, capacity: r.capacity ?? '', description: r.description ?? '' };
                }
            }
        },
        setListGridMode(m) {
            this.listGridMode = m;
            try {
                localStorage.setItem(this.listGridStorageKey, m);
            } catch (e) {}
        },
        roomRowVisible(el) {
            const q = (this.roomFilter || '').toLowerCase().trim();
            const blob = (el.dataset.roomSearch || '').toLowerCase();
            if (q && !blob.includes(q)) return false;
            if (this.typeFilter !== 'all' && (el.dataset.roomType || '') !== this.typeFilter) return false;
            if (this.statusFilter === 'available' && el.dataset.roomAvailable !== '1') return false;
            if (this.statusFilter === 'unavailable' && el.dataset.roomAvailable === '1') return false;
            return true;
        },
        openCreateModal() { this.openCreate = true; },
        closeCreateModal() { this.openCreate = false; },
        openEditModal(roomOrId) {
            const room = typeof roomOrId === 'object' ? roomOrId : this.rooms.find(r => r.id == roomOrId);
            if (!room) return;
            this.currentRoom = room;
            this.editForm = {
                name: room.name,
                description: room.description ?? '',
                type: room.type,
                capacity: room.capacity ?? '',
                price_per_night: room.price_per_night,
                is_available: room.is_available
            };
            this.openEdit = true;
        },
        closeEditModal() { this.openEdit = false; this.currentRoom = null; }
    }">
        @if($atLimit ?? false)
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                You have reached your plan limit ({{ $maxRooms }} rooms). Contact the platform to upgrade and add more rooms.
            </div>
        @endif

        @if($rooms->isEmpty())
            <div class="rounded-xl border border-gray-200/80 bg-white p-8 text-center shadow-sm">
                <p class="text-gray-600">No rooms or cottages yet.</p>
                @if(!($atLimit ?? false) && tenant_staff_can('rooms', 'create'))
                    <button type="button" @click="openCreateModal()" class="mt-3 text-teal-600 font-medium hover:text-teal-700 hover:underline">
                        Add your first room or cottage
                    </button>
                @endif
            </div>
        @else
            <div class="w-full min-w-0">
                {{-- Toolbar: shared label row + h-10 controls so everything aligns on one baseline --}}
                <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between lg:gap-x-4 lg:gap-y-3">
                    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-3">
                        <div class="w-full min-w-0 sm:min-w-[min(100%,18rem)] sm:flex-1 lg:max-w-md">
                            <label for="rooms-index-search" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Search') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                                </span>
                                <input id="rooms-index-search" type="search" x-model="roomFilter" autocomplete="off"
                                       placeholder="{{ __('Name, description, type…') }}"
                                       class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="w-full min-w-[11rem] sm:w-40">
                            <label for="rooms-filter-type" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Type') }}</label>
                            <div class="relative">
                                <select id="rooms-filter-type" x-model="typeFilter"
                                        class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-9 text-sm text-gray-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                    <option value="all">{{ __('All types') }}</option>
                                    <option value="room">{{ __('Room') }}</option>
                                    <option value="cottage">{{ __('Cottage') }}</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="w-full min-w-[11rem] sm:w-40">
                            <label for="rooms-filter-status" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Status') }}</label>
                            <div class="relative">
                                <select id="rooms-filter-status" x-model="statusFilter"
                                        class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-9 text-sm text-gray-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                    <option value="all">{{ __('All statuses') }}</option>
                                    <option value="available">{{ __('Available') }}</option>
                                    <option value="unavailable">{{ __('Unavailable') }}</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col gap-1 sm:ml-auto">
                        <span class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Actions') }}</span>
                        <div class="flex h-10 flex-wrap items-center gap-2 sm:flex-nowrap">
                            <x-list-grid-toggle-buttons accent="teal" />
                            @if(!($atLimit ?? false) && tenant_staff_can('rooms', 'create'))
                                <button type="button" @click="openCreateModal()"
                                        class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Add Room / Cottage') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="w-full min-w-0">
                    <div x-show="listGridMode === 'list'" x-cloak class="w-full min-w-0">
                    <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
                        <table class="min-w-[720px] w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Guests') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Price / night') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($rooms as $room)
                                    @php
                                        $roomSearchBlob = strtolower(implode(' ', array_filter([
                                            $room->name,
                                            $room->description,
                                            $room->type,
                                            $room->capacity !== null ? (string) $room->capacity : '',
                                            number_format($room->price_per_night, 0),
                                        ], fn ($v) => $v !== null && $v !== '')));
                                    @endphp
                                    <tr class="hover:bg-gray-50/50"
                                        data-room-search="{{ e($roomSearchBlob) }}"
                                        data-room-type="{{ e($room->type) }}"
                                        data-room-available="{{ $room->is_available ? '1' : '0' }}"
                                        x-show="roomRowVisible($el)">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $room->name }}</td>
                                        <td class="px-4 py-3 capitalize text-gray-700">{{ $room->type }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $room->capacity ?? '—' }}</td>
                                        <td class="px-4 py-3 tabular-nums text-gray-900">₱{{ number_format($room->price_per_night, 0) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $room->is_available ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $room->is_available ? __('Available') : __('Unavailable') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if(tenant_staff_can('rooms', 'update'))
                                                <button type="button" @click="openEditModal({{ $room->id }})" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</button>
                                            @endif
                                            @if(tenant_staff_can('rooms', 'delete'))
                                                <x-confirm-form-button class="inline-block ml-1" :action="tenant_url('rooms/' . $room->id)" method="DELETE" :title="__('Delete room')" :message="__('Delete this room? Existing bookings that reference it may be affected.')" :confirm-label="__('Delete')">
                                                    <button type="button" @click="open = true" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                                </x-confirm-form-button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    </div>

                    <div x-show="listGridMode === 'grid'" x-cloak class="w-full min-w-0">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($rooms as $room)
                    @php
                        $roomThumbUrl = $room->image_path ? asset('storage/' . $room->image_path) : asset('images/background.jpg');
                        $roomSearchBlob = strtolower(implode(' ', array_filter([
                            $room->name,
                            $room->description,
                            $room->type,
                            $room->capacity !== null ? (string) $room->capacity : '',
                            number_format($room->price_per_night, 0),
                        ], fn ($v) => $v !== null && $v !== '')));
                    @endphp
                    <div class="overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-sm transition hover:border-teal-200 hover:shadow-md"
                         data-room-search="{{ e($roomSearchBlob) }}"
                         data-room-type="{{ e($room->type) }}"
                         data-room-available="{{ $room->is_available ? '1' : '0' }}"
                         x-show="roomRowVisible($el)">
                        <div class="aspect-[4/3] bg-gray-200 relative">
                            <img src="{{ $roomThumbUrl }}" alt="{{ $room->name }}"
                                 class="h-full w-full object-cover"
                                 onerror="this.onerror=null; this.src='{{ asset('images/background.jpg') }}';">
                            <span class="absolute top-2 left-2 inline-flex items-center rounded-full border border-white/80 bg-white/90 px-2 py-0.5 text-[10px] font-semibold capitalize text-teal-800 shadow-sm backdrop-blur-sm">
                                {{ $room->type }}
                            </span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold text-gray-900" title="{{ $room->name }}">{{ $room->name }}</h3>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $room->is_available ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $room->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </div>
                            @if($room->description)
                                <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $room->description }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                @if($room->capacity)
                                    <span>{{ $room->capacity }} guest(s)</span>
                                @endif
                                <span class="font-medium text-gray-900">₱{{ number_format($room->price_per_night, 0) }} <span class="font-normal text-gray-500">/ night</span></span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if(tenant_staff_can('rooms', 'update'))
                                <button type="button" @click="openEditModal({{ $room->id }})"
                                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:border-teal-200 hover:text-teal-700">
                                    Edit
                                </button>
                                @endif
                                @if(tenant_staff_can('rooms', 'delete'))
                                <x-confirm-form-button
                                    class="inline-block"
                                    :action="tenant_url('rooms/' . $room->id)"
                                    method="DELETE"
                                    :title="__('Delete room')"
                                    :message="__('Delete this room? Existing bookings that reference it may be affected.')"
                                    :confirm-label="__('Delete')">
                                    <button type="button" @click="open = true" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50">
                                        {{ __('Delete') }}
                                    </button>
                                </x-confirm-form-button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Create modal --}}
    @if(tenant_staff_can('rooms', 'create'))
    <div x-show="openCreate" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto px-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm" @click="closeCreateModal()"></div>
        <div class="relative mx-auto max-w-lg rounded-xl bg-white p-6 shadow-xl sm:my-8">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Add Room / Cottage</h2>
                <button type="button" @click="closeCreateModal()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ tenant_url('rooms') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="create_name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="create_name" name="name" type="text" value="{{ old('name') }}" required
                           {{ \App\Support\InputHtmlAttributes::title(255) }}
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="create_type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select id="create_type" name="type" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        <option value="room" {{ old('type', 'room') === 'room' ? 'selected' : '' }}>Room</option>
                        <option value="cottage" {{ old('type') === 'cottage' ? 'selected' : '' }}>Cottage</option>
                    </select>
                    @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="create_description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea id="create_description" name="description" rows="2" {{ \App\Support\InputHtmlAttributes::textarea(5000) }} class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create_capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                        <input id="create_capacity" name="capacity" type="number" min="1" value="{{ old('capacity') }}"
                               inputmode="numeric" pattern="[0-9]*" maxlength="12"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="create_price" class="block text-sm font-medium text-gray-700">Price per night</label>
                        <input id="create_price" name="price_per_night" type="number" step="0.01" min="0" value="{{ old('price_per_night', '0') }}" required
                               {{ \App\Support\InputHtmlAttributes::money() }}
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('price_per_night') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input id="create_available" name="is_available" type="checkbox" value="1" {{ old('is_available', true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <label for="create_available" class="text-sm text-gray-700">Available for booking</label>
                </div>
                <div>
                    <label for="create_images" class="block text-sm font-medium text-gray-700">Images (optional)</label>
                    <input id="create_images" name="images[]" type="file" multiple accept="image/jpeg,image/png"
                           class="mt-1 block w-full text-sm text-gray-700
                                  file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 file:px-4 file:py-2
                                  file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100">
                    <p class="mt-1 text-xs text-gray-500">
                        You can select multiple images. The first one will be used as the room thumbnail.
                    </p>
                    @error('images') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('images.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeCreateModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Create</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit modal (dynamic form bound to currentRoom) --}}
    @if(tenant_staff_can('rooms', 'update'))
    <div x-show="openEdit" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto px-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm" @click="closeEditModal()"></div>
        <div class="relative mx-auto max-w-lg rounded-xl bg-white p-6 shadow-xl sm:my-8" @click.self="closeEditModal()">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Edit room</h2>
                <button type="button" @click="closeEditModal()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="currentRoom">
                <form :action="currentRoom.update_url" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="edit_name" name="name" type="text" x-model="editForm.name" required
                               {{ \App\Support\InputHtmlAttributes::title(255) }}
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="edit_type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select id="edit_type" name="type" x-model="editForm.type" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                            <option value="room">Room</option>
                            <option value="cottage">Cottage</option>
                        </select>
                        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                        <textarea id="edit_description" name="description" rows="2" x-model="editForm.description"
                                  {{ \App\Support\InputHtmlAttributes::textarea(5000) }}
                                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500"></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                            <input id="edit_capacity" name="capacity" type="number" min="1" x-model="editForm.capacity"
                                   inputmode="numeric" pattern="[0-9]*" maxlength="12"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                            @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="edit_price" class="block text-sm font-medium text-gray-700">Price per night</label>
                            <input id="edit_price" name="price_per_night" type="number" step="0.01" min="0" x-model="editForm.price_per_night" required
                                   {{ \App\Support\InputHtmlAttributes::money() }}
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                            @error('price_per_night') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="edit_available" name="is_available" type="checkbox" value="1" x-model="editForm.is_available"
                               class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <label for="edit_available" class="text-sm text-gray-700">Available for booking</label>
                    </div>
                    <div>
                        <label for="edit_images" class="block text-sm font-medium text-gray-700">Add images (optional)</label>
                        <input id="edit_images" name="images[]" type="file" multiple accept="image/jpeg,image/png"
                               class="mt-1 block w-full text-sm text-gray-700
                                      file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 file:px-4 file:py-2
                                      file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100">
                        <p class="mt-1 text-xs text-gray-500">
                            You can upload more photos for this room. Existing images will stay; the first uploaded image is used as the thumbnail if none is set.
                        </p>
                        @error('images') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('images.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeEditModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Update</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
    @endif
    </div>
</x-tenant::app-layout>
