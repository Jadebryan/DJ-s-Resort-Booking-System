<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ tenant_url('user/profile/send-verification') }}">
        @csrf
    </form>

    <form method="post" action="{{ tenant_url('user/profile') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-tenant-user::input-label for="name" :value="__('Name')" />
            <x-tenant-user::text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" constraint="personName" />
            <x-tenant-user::input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-tenant-user::input-label for="email" :value="__('Email')" />
            <x-tenant-user::text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" constraint="email" />
            <x-tenant-user::input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-tenant-user::primary-button>{{ __('Save') }}</x-tenant-user::primary-button>
        </div>
    </form>
</section>
