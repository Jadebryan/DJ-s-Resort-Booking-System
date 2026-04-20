<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
    >
        {{ __('Delete Account') }}
    </button>

    <x-tenant-user::modal name="confirm-user-deletion" maxWidth="md" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <x-form-with-busy method="post" action="{{ tenant_url('user/profile') }}" class="p-5 sm:p-6" :overlay="true" busy-message="{{ __('Deleting account...') }}">
            @csrf
            @method('delete')

            <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-5">
                <x-tenant-user::input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-tenant-user::text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-tenant-user::input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <x-tenant-user::secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-tenant-user::secondary-button>

                <x-busy-submit
                    class="inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700 disabled:opacity-60"
                    busy-text="{{ __('Deleting...') }}"
                >
                    {{ __('Delete Account') }}
                </x-busy-submit>
            </div>
        </x-form-with-busy>
    </x-tenant-user::modal>
</section>
