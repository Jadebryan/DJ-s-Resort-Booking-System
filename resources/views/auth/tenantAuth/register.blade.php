<x-tenant::guest-layout container-class="max-w-3xl" :compact="true" :dense="true">
    <div class="mb-2 text-center">
        <p class="text-[9px] font-semibold uppercase tracking-[0.18em] text-slate-600 mb-0.5 sm:text-[10px]">
            Get your own resort space
        </p>
        <h1 class="text-base font-semibold leading-tight text-slate-900 dark:text-white sm:text-lg">
            Create a tenant for your resort
        </h1>
        <p class="mt-0.5 text-[10px] leading-snug text-slate-600 sm:text-[11px]">
            We’ll spin up a dedicated dashboard, database, and login for your team.
        </p>
    </div>

    <x-form-with-busy method="POST" action="{{ url('/tenant/register') }}" class="space-y-3" :overlay="true" busy-message="{{ __('Submitting your application…') }}">
        @csrf

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-start lg:gap-5">
            {{-- Left: subscription --}}
            <div class="space-y-2 lg:pr-0">
                <div>
                    <h2 class="text-[11px] font-semibold text-slate-900">{{ __('Choose your subscription') }}</h2>
                    <p class="mt-0.5 text-[9px] text-slate-500 sm:text-[10px]">{{ __('Pick a plan and how long you want to subscribe.') }}</p>
                </div>
                <div class="max-h-[min(32vh,15rem)] space-y-1 overflow-y-auto pr-0.5">
                    @forelse($plans ?? [] as $plan)
                        <label class="flex cursor-pointer gap-1.5 rounded-md border border-slate-200 bg-slate-50/80 p-1.5 shadow-sm transition hover:border-sky-400 has-[:checked]:border-sky-500 has-[:checked]:bg-white has-[:checked]:ring-1 has-[:checked]:ring-sky-500">
                            <input
                                type="radio"
                                name="plan_id"
                                value="{{ $plan->id }}"
                                class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-600 focus:ring-sky-500"
                                @checked((string) old('plan_id') === (string) $plan->id)
                                @if($loop->first) required @endif
                            />
                            <span class="min-w-0 flex-1">
                                <span class="block text-[11px] font-semibold leading-tight text-slate-900">{{ $plan->name }}</span>
                                @if($plan->description)
                                    <span class="mt-0.5 block text-[9px] leading-snug text-slate-600 line-clamp-1 sm:text-[10px]">{{ $plan->description }}</span>
                                @endif
                                <span class="mt-0.5 block text-[9px] font-medium text-sky-700 sm:text-[10px]">
                                    {{ __(':amount / month', ['amount' => '₱'.number_format((float) $plan->price_monthly, 2)]) }}
                                    @if((float) $plan->price_monthly <= 0)
                                        <span class="text-emerald-700">({{ __('no payment step') }})</span>
                                    @endif
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-[11px] text-amber-900">
                            {{ __('No subscription plans are available right now. Please contact the platform administrator.') }}
                        </p>
                    @endforelse
                </div>
                @error('plan_id')
                    <span class="text-[10px] text-red-500">{{ $message }}</span>
                @enderror

                @php($termTypeOld = old('subscription_term_type', 'months') === 'days' ? 'days' : 'months')
                <div class="space-y-2 border-t border-slate-100 pt-2" x-data="{ term: @js($termTypeOld) }">
                    <div>
                        <p class="block text-[11px] font-semibold text-slate-900">{{ __('Subscription length') }}</p>
                        <p class="mt-0.5 text-[9px] text-slate-600 sm:text-[10px]">
                            {{ __('Choose months (each month = :days days after approval) or enter an exact number of days. Custom days are billed pro-rated from the monthly rate.', ['days' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3 text-[11px]">
                        <label class="inline-flex cursor-pointer items-center gap-1.5">
                            <input
                                type="radio"
                                name="subscription_term_type"
                                value="months"
                                class="h-3.5 w-3.5 text-sky-600 focus:ring-sky-500"
                                x-model="term"
                                @checked($termTypeOld === 'months')
                            />
                            <span>{{ __('By months') }}</span>
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-1.5">
                            <input
                                type="radio"
                                name="subscription_term_type"
                                value="days"
                                class="h-3.5 w-3.5 text-sky-600 focus:ring-sky-500"
                                x-model="term"
                                @checked($termTypeOld === 'days')
                            />
                            <span>{{ __('Custom days') }}</span>
                        </label>
                    </div>
                    @error('subscription_term_type')
                        <span class="text-[10px] text-red-500">{{ $message }}</span>
                    @enderror
                    <div x-show="term === 'months'" x-cloak class="space-y-0.5">
                        <label for="subscription_months" class="block text-[10px] font-medium text-slate-700">{{ __('Months') }}</label>
                        <select
                            id="subscription_months"
                            name="subscription_months"
                            class="mt-0.5 block h-9 w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-[13px] leading-none text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                            :disabled="term !== 'months'"
                            :required="term === 'months'"
                        >
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected((string) old('subscription_months', '1') === (string) $m)>
                                    {{ $m }} {{ $m === 1 ? __('month') : __('months') }}
                                </option>
                            @endforeach
                        </select>
                        @error('subscription_months')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div x-show="term === 'days'" x-cloak class="space-y-0.5">
                        <label for="subscription_days" class="block text-[10px] font-medium text-slate-700">{{ __('Number of days') }}</label>
                        <input
                            id="subscription_days"
                            type="number"
                            name="subscription_days"
                            min="1"
                            max="{{ \App\Models\TenantRegistrationRequest::MAX_SUBSCRIPTION_DAYS }}"
                            value="{{ old('subscription_days', '31') }}"
                            class="mt-0.5 block h-9 w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-[13px] text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                            :disabled="term !== 'days'"
                            :required="term === 'days'"
                        />
                        <p class="text-[9px] text-slate-500 sm:text-[10px]">
                            {{ __('Max :max days. Total = monthly rate × days ÷ :per month.', ['max' => \App\Models\TenantRegistrationRequest::MAX_SUBSCRIPTION_DAYS, 'per' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}
                        </p>
                        @error('subscription_days')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Right: resort & admin account --}}
            <div class="space-y-2 lg:border-l lg:border-slate-100 lg:pl-4">
                <div>
                    <h2 class="text-[11px] font-semibold text-slate-900">{{ __('Resort & admin account') }}</h2>
                    <p class="mt-0.5 text-[9px] text-slate-500 sm:text-[10px]">{{ __('Details for your property and the first administrator login.') }}</p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <div class="space-y-0 sm:col-span-2">
                        <label for="tenant_name" class="block text-[11px] font-semibold text-slate-900">
                            Resort name
                        </label>
                        <input
                            id="tenant_name"
                            type="text"
                            name="tenant_name"
                            value="{{ old('tenant_name') }}"
                            required
                            autofocus
                            placeholder="Azure Haven Resort"
                            {{ \App\Support\InputHtmlAttributes::title(255) }}
                            class="mt-0.5 block h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-[13px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                        @error('tenant_name')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-0 sm:col-span-2">
                        <label for="primary_domain" class="block text-[11px] font-semibold text-slate-900">
                            Preferred site name
                        </label>
                        <input
                            id="primary_domain"
                            type="text"
                            name="primary_domain"
                            value="{{ old('primary_domain') }}"
                            placeholder="jeddsresort"
                            required
                            {{ \App\Support\InputHtmlAttributes::primaryDomain() }}
                            class="mt-0.5 block h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-[13px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                        @error('primary_domain')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                        @php($tenantSuffix = trim((string) config('tenancy.tenant_host_suffix', 'localhost')))
                        <p class="mt-0.5 text-[9px] text-slate-500 sm:text-[10px]">
                            @if($tenantSuffix !== '')
                                {{ __('Only the name you want (letters, numbers, hyphens). We add :suffix automatically.', ['suffix' => '.'.$tenantSuffix]) }}
                            @else
                                {{ __('Enter the full hostname visitors will use (must include a dot).') }}
                            @endif
                        </p>
                    </div>

                    <div class="space-y-0">
                        <label for="name" class="block text-[11px] font-semibold text-slate-900">
                            Admin name
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Serenity Manager"
                            {{ \App\Support\InputHtmlAttributes::personName() }}
                            class="mt-0.5 block h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-[13px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                        @error('name')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-0">
                        <label for="email" class="block text-[11px] font-semibold text-slate-900">
                            Work email
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="you@resort.com"
                            {{ \App\Support\InputHtmlAttributes::email() }}
                            class="mt-0.5 block h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-[13px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                        @error('email')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-0">
                        <label for="tenant_register_password" class="block text-[11px] font-semibold text-slate-900">
                            Password
                        </label>
                        <div class="relative mt-0.5">
                            <input
                                id="tenant_register_password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                class="block h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 pr-8 text-[13px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-1 my-0.5 inline-flex items-center rounded px-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                                onclick="
                                    const input = document.getElementById('tenant_register_password');
                                    if (!input) return;
                                    input.type = input.type === 'password' ? 'text' : 'password';
                                "
                                aria-label="Toggle password visibility"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                                    <circle cx="12" cy="12" r="3.2" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-0">
                        <label for="tenant_register_password_confirmation" class="block text-[11px] font-semibold text-slate-900">
                            Confirm password
                        </label>
                        <div class="relative mt-0.5">
                            <input
                                id="tenant_register_password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                class="block h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 pr-8 text-[13px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-1 my-0.5 inline-flex items-center rounded px-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                                onclick="
                                    const input = document.getElementById('tenant_register_password_confirmation');
                                    if (!input) return;
                                    input.type = input.type === 'password' ? 'text' : 'password';
                                "
                                aria-label="Toggle confirm password visibility"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                                    <circle cx="12" cy="12" r="3.2" />
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <span class="text-[10px] text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-1 border-t border-slate-100 pt-2">
            <p class="text-[9px] leading-snug text-slate-600 sm:text-[10px]">
                {{ __('After you submit, you can pay with GCash, Maya, or bank transfer using a reference number. A superadmin will verify everything before your resort goes live.') }}
            </p>
            <p class="text-[9px] leading-snug text-slate-600 sm:text-[10px]">
                @if(($tenantSuffix ?? '') !== '')
                    {{ __('Guests and staff open your site at a URL like') }}
                    <span class="font-mono text-slate-800">http://name.{{ $tenantSuffix }}</span>
                    {{ __('(same port as this app when using artisan serve).') }}
                @else
                    {{ __('Guests and staff use the full hostname you enter in the browser address bar.') }}
                @endif
            </p>
        </div>

        <div class="flex flex-col gap-1.5 pt-0.5 sm:flex-row sm:items-center sm:justify-between">
            <div class="order-2 text-center sm:order-1 sm:text-left">
                <p class="text-[9px] text-slate-600 sm:text-[10px]">
                    Already managing a resort with DJs Resort?
                </p>
                <a
                    href="{{ central_route('tenant.select.login') }}"
                    class="mt-0 inline-flex text-[10px] font-medium text-sky-700 underline decoration-sky-300 underline-offset-2 hover:text-sky-900 sm:text-[11px]"
                >
                    {{ __('Back to tenant login') }}
                </a>
            </div>
            <x-busy-submit
                class="order-1 inline-flex h-9 w-full shrink-0 items-center justify-center rounded-md bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 px-3 text-[13px] font-semibold text-white shadow-sm transition hover:brightness-110 sm:order-2 sm:w-auto sm:min-w-[11rem]"
                busy-text="{{ __('Submitting…') }}"
            >
                {{ __('Continue — payment & review') }}
            </x-busy-submit>
        </div>
    </x-form-with-busy>
</x-tenant::guest-layout>
