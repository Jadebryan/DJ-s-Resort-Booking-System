<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ tenant_url('profile/send-verification') }}">
        @csrf
    </form>

    <form method="post" action="{{ tenant_url('profile') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-tenant::input-label for="name" :value="__('Name')" />
            <x-tenant::text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-tenant::input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-tenant::input-label for="email" :value="__('Email')" />
            <x-tenant::text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-tenant::input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-tenant::primary-button>{{ __('Save') }}</x-tenant::primary-button>
        </div>
    </form>
</section>
