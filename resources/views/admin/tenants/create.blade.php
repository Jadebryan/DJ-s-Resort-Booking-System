<x-admin::app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tenants.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Tenants</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Add Tenant') }}
            </h2>
        </div>
    </x-slot>

    <div class="min-w-0 max-w-full py-12">
        <div class="mx-auto min-w-0 max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-admin::input-label for="tenant_name" :value="__('Resort / Tenant Name')" />
                        <x-admin::text-input id="tenant_name" name="tenant_name" type="text" class="mt-1 block w-full"
                                             :value="old('tenant_name')" required autofocus />
                        <x-admin::input-error :messages="$errors->get('tenant_name')" class="mt-1" />
                    </div>

                    <div>
                        @php($sfx = trim((string) config('tenancy.tenant_host_suffix', 'localhost')))
                        <x-admin::input-label for="primary_domain" :value="__('Preferred site name')" />
                        <x-admin::text-input id="primary_domain" name="primary_domain" type="text" class="mt-1 block w-full"
                                             :value="old('primary_domain')" placeholder="e.g. myresort" required />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if($sfx !== '')
                                {{ __('Short label only; we add :suffix. Not the central app host.', ['suffix' => '.'.$sfx]) }}
                            @else
                                {{ __('Full hostname for this resort. Not the central app URL.') }}
                            @endif
                        </p>
                        <x-admin::input-error :messages="$errors->get('primary_domain')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="email" :value="__('Admin email')" />
                        <x-admin::text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                             :value="old('email')" required />
                        <x-admin::input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="plan_id" :value="__('Plan')" />
                        <select id="plan_id" name="plan_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— No plan —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} ({{ number_format($plan->price_monthly, 2) }}/mo
                                    @if($plan->price_yearly)
                                        , {{ number_format($plan->price_yearly, 2) }}/yr
                                    @endif
                                    )
                                </option>
                            @endforeach
                        </select>
                        <x-admin::input-error :messages="$errors->get('plan_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="subscription_months" :value="__('Subscription length')" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Applies when a plan is selected. Each month adds :days days to subscription end.', ['days' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}
                        </p>
                        <select id="subscription_months" name="subscription_months"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (string) old('subscription_months', '1') === (string) $m ? 'selected' : '' }}>
                                    {{ $m }} {{ $m === 1 ? __('month') : __('months') }}
                                </option>
                            @endforeach
                        </select>
                        <x-admin::input-error :messages="$errors->get('subscription_months')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="password" :value="__('Admin password')" />
                        <x-admin::text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-admin::input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="password_confirmation" :value="__('Confirm password')" />
                        <x-admin::text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-admin::primary-button>{{ __('Create Tenant') }}</x-admin::primary-button>
                        <a href="{{ route('admin.tenants.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin::app-layout>
