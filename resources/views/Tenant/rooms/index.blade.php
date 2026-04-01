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
        init() {
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
        @if(!($atLimit ?? false))
            <div class="flex justify-end">
                <button type="button" @click="openCreateModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Room / Cottage') }}
                </button>
            </div>
        @endif
        @if($atLimit ?? false)
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                You have reached your plan limit ({{ $maxRooms }} rooms). Contact the platform to upgrade and add more rooms.
            </div>
        @endif

        @if($rooms->isEmpty())
            <div class="rounded-xl border border-gray-200/80 bg-white p-8 text-center shadow-sm">
                <p class="text-gray-600">No rooms or cottages yet.</p>
                @if(!($atLimit ?? false))
                    <button type="button" @click="openCreateModal()" class="mt-3 text-teal-600 font-medium hover:text-teal-700 hover:underline">
                        Add your first room or cottage
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($rooms as $room)
                    @php
                        $roomThumbUrl = $room->image_path ? asset('storage/' . $room->image_path) : asset('images/background.jpg');
                    @endphp
                    <div class="overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-sm transition hover:border-teal-200 hover:shadow-md">
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
                                <button type="button" @click="openEditModal({{ $room->id }})"
                                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:border-teal-200 hover:text-teal-700">
                                    Edit
                                </button>
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
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Create modal --}}
    <div x-show="openCreate" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto px-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="closeCreateModal()"></div>
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
                    <textarea id="create_description" name="description" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create_capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                        <input id="create_capacity" name="capacity" type="number" min="1" value="{{ old('capacity') }}"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                        @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="create_price" class="block text-sm font-medium text-gray-700">Price per night</label>
                        <input id="create_price" name="price_per_night" type="number" step="0.01" min="0" value="{{ old('price_per_night', '0') }}" required
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

    {{-- Edit modal (dynamic form bound to currentRoom) --}}
    <div x-show="openEdit" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto px-4"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="closeEditModal()"></div>
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
                                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500"></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                            <input id="edit_capacity" name="capacity" type="number" min="1" x-model="editForm.capacity"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-teal-500">
                            @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="edit_price" class="block text-sm font-medium text-gray-700">Price per night</label>
                            <input id="edit_price" name="price_per_night" type="number" step="0.01" min="0" x-model="editForm.price_per_night" required
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
    </div>
</x-tenant::app-layout>
