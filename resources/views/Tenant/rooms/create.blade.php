<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <a href="{{ tenant_url('rooms') }}" class="text-xs font-medium text-teal-700 hover:text-teal-900">{{ __('← Back to rooms') }}</a>
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl mt-1">{{ __('Add room / cottage') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Create a bookable unit with photos and nightly rate.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl py-8 sm:py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 min-w-0">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 overflow-hidden">
                <form method="POST" action="{{ tenant_url('rooms') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <x-tenant::input-label for="name" :value="__('Name')" />
                        <x-tenant::text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus constraint="title" />
                        <x-tenant::input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="type" :value="__('Type')" />
                        <select id="type" name="type" class="mt-1 block w-full border-slate-300 bg-white text-slate-900 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            <option value="room" {{ old('type', 'room') === 'room' ? 'selected' : '' }}>Room</option>
                            <option value="cottage" {{ old('type') === 'cottage' ? 'selected' : '' }}>Cottage</option>
                        </select>
                        <x-tenant::input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="description" :value="__('Description (optional)')" />
                        <textarea id="description" name="description" rows="3" {{ \App\Support\InputHtmlAttributes::textarea(5000) }} class="mt-1 block w-full border-slate-300 bg-white text-slate-900 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">{{ old('description') }}</textarea>
                        <x-tenant::input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="capacity" :value="__('Capacity (optional)')" />
                        <x-tenant::text-input id="capacity" name="capacity" type="number" min="1" inputmode="numeric" pattern="[0-9]*" maxlength="12" class="mt-1 block w-full" :value="old('capacity')" />
                        <x-tenant::input-error :messages="$errors->get('capacity')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="price_per_night" :value="__('Price per night')" />
                        <x-tenant::text-input id="price_per_night" name="price_per_night" type="number" step="0.01" min="0" inputmode="decimal" class="mt-1 block w-full" :value="old('price_per_night', '0')" required />
                        <x-tenant::input-error :messages="$errors->get('price_per_night')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="images" :value="__('Images (optional)')" />
                        <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png"
                               class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-200
                                      file:mr-4 file:rounded-md file:border-0 file:bg-sky-50 file:px-4 file:py-2
                                      file:text-sm file:font-medium file:text-sky-700 hover:file:bg-sky-100">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            You can select multiple images. The first one will be used as the room thumbnail.
                        </p>
                        <x-tenant::input-error :messages="$errors->get('images')" class="mt-1" />
                        <x-tenant::input-error :messages="$errors->get('images.*')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_available" name="is_available" type="checkbox" value="1" {{ old('is_available', true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <x-tenant::input-label for="is_available" :value="__('Available for booking')" class="inline" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-tenant::primary-button>{{ __('Create') }}</x-tenant::primary-button>
                        <a href="{{ tenant_url('rooms') }}" class="text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-tenant::app-layout>
