<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <a href="{{ tenant_url('staff') }}" class="text-xs font-medium text-teal-700 hover:text-teal-900">{{ __('← Back to staff') }}</a>
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl mt-1 truncate" title="{{ $member->name }}">{{ __('Edit staff member') }}: {{ $member->name }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Update name, email, role, or password.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl py-8 sm:py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 min-w-0">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 overflow-hidden">
                <form method="POST" action="{{ tenant_url('staff/' . $member->id) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-tenant::input-label for="name" :value="__('Name')" />
                        <x-tenant::text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $member->name)" required autofocus constraint="personName" />
                        <x-tenant::input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="email" :value="__('Email')" />
                        <x-tenant::text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $member->email)" required constraint="email" />
                        <x-tenant::input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="password" :value="__('New password (leave blank to keep current)')" />
                        <x-tenant::text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                        <x-tenant::input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <x-tenant::input-label for="password_confirmation" :value="__('Confirm new password')" />
                        <x-tenant::text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-tenant::input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" class="mt-1 block w-full border-slate-300 bg-white text-slate-900 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            <option value="staff" {{ old('role', $member->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="admin" {{ old('role', $member->role) === 'admin' ? 'selected' : '' }}>Owner / Admin</option>
                        </select>
                        <x-tenant::input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-tenant::primary-button>{{ __('Update') }}</x-tenant::primary-button>
                        <a href="{{ tenant_url('staff') }}" class="text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-tenant::app-layout>
