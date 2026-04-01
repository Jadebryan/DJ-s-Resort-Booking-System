<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('System settings') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Central configuration for plans, features, and integrations.') }}</p>
        </div>
    </x-slot>

    @php
        $s = $platformSettings;
        $flagOn = collect([
            $s->feature_booking_calendar_beta,
            $s->feature_multi_currency,
        ])->filter()->count();
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1 text-left">
        <section class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white/90 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ __('Default plan') }}</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $s->defaultPlan?->name ?? __('Not set') }}
                </p>
                <p class="mt-1 text-xs text-gray-500">{{ __('Used when a signup or manual tenant create leaves plan empty.') }}</p>
            </div>
            <div class="rounded-xl border border-sky-100 bg-sky-50/50 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-sky-800/90">{{ __('Notifications') }}</p>
                <p class="mt-1 text-sm font-semibold text-sky-950">
                    {{ $s->send_system_emails ? __('System emails on') : __('System emails off') }}
                </p>
                <p class="mt-1 text-xs text-sky-900/70">
                    {{ $s->send_sms_alerts ? __('SMS alerts enabled') : __('SMS alerts off') }}
                    · {{ __('Stored preference; wire mailers to respect flags.') }}
                </p>
            </div>
            <div class="rounded-xl border border-violet-100 bg-violet-50/50 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-violet-800/90">{{ __('Feature flags') }}</p>
                <p class="mt-1 text-sm font-semibold text-violet-950">{{ $flagOn === 0 ? __('None on') : __(':count feature flags on', ['count' => $flagOn]) }}</p>
                <p class="mt-1 text-xs text-violet-900/70">{{ __('Use PlatformSetting::featureEnabled() in code.') }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Platform configuration') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Timezone applies on the next request after save. Queue workers may need a restart.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Plans') }}</a>
                    <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Tenants') }}</a>
                    <button type="submit" form="admin-settings-form"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 whitespace-nowrap">
                        {{ __('Save settings') }}
                    </button>
                </div>
            </div>

            <form id="admin-settings-form" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 text-sm p-4 sm:p-5">
                @csrf
                <div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="default_plan_id">{{ __('Default tenant plan') }}</label>
                            <select id="default_plan_id" name="default_plan_id"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 @error('default_plan_id') border-red-500 @enderror">
                                <option value="">{{ __('None — require explicit plan') }}</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('default_plan_id', $s->default_plan_id) == $plan->id)>
                                        {{ $plan->name }}@if(! $plan->is_active) {{ __('(inactive)') }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <x-admin::input-error :messages="$errors->get('default_plan_id')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="timezone">{{ __('Application time zone') }}</label>
                            <select id="timezone" name="timezone"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 @error('timezone') border-red-500 @enderror">
                                @foreach($timezoneOptions as $tzOpt)
                                    <option value="{{ $tzOpt }}" @selected(old('timezone', $s->timezone) === $tzOpt)>{{ $tzOpt }}</option>
                                @endforeach
                            </select>
                            <x-admin::input-error :messages="$errors->get('timezone')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-4">
                            <p class="text-xs font-semibold text-gray-700 uppercase">{{ __('Email & SMS') }}</p>
                            <input type="hidden" name="send_system_emails" value="0">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                                <input type="checkbox" name="send_system_emails" value="1"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       @checked(old('send_system_emails', $s->send_system_emails ? '1' : '0') === '1')>
                                {{ __('Send system emails') }}
                            </label>
                            <input type="hidden" name="send_sms_alerts" value="0">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                                <input type="checkbox" name="send_sms_alerts" value="1"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       @checked(old('send_sms_alerts', $s->send_sms_alerts ? '1' : '0') === '1')>
                                {{ __('Send SMS alerts') }}
                            </label>
                        </div>
                        <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/50 p-4">
                            <p class="text-xs font-semibold text-gray-700 uppercase">{{ __('Feature flags') }}</p>
                            <input type="hidden" name="feature_booking_calendar_beta" value="0">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                                <input type="checkbox" name="feature_booking_calendar_beta" value="1"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       @checked(old('feature_booking_calendar_beta', $s->feature_booking_calendar_beta ? '1' : '0') === '1')>
                                {{ __('Enable booking calendar (beta)') }}
                            </label>
                            <input type="hidden" name="feature_multi_currency" value="0">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                                <input type="checkbox" name="feature_multi_currency" value="1"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       @checked(old('feature_multi_currency', $s->feature_multi_currency ? '1' : '0') === '1')>
                                {{ __('Enable multi-currency prices') }}
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</x-admin::app-layout>
