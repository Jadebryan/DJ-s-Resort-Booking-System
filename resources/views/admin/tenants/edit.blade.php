<x-admin::app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tenants.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Tenants</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Tenant') }}: {{ $tenant->tenant_name }}
            </h2>
        </div>
    </x-slot>

    <div class="min-w-0 max-w-full py-12"
         x-data="{
            deleteTenantModalOpen: false,
            confirmDeleteTenant() { this.$refs.deleteTenantForm.submit(); }
         }"
         @keydown.escape.window="deleteTenantModalOpen = false">
        <div class="mx-auto min-w-0 max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-admin::input-label for="tenant_name" :value="__('Resort / Tenant Name')" />
                        <x-admin::text-input id="tenant_name" name="tenant_name" type="text" class="mt-1 block w-full"
                                             :value="old('tenant_name', $tenant->tenant_name)" required constraint="title" />
                        <x-admin::input-error :messages="$errors->get('tenant_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="primary_domain" :value="__('Preferred site name')" />
                        <x-admin::text-input id="primary_domain" name="primary_domain" type="text" class="mt-1 block w-full"
                                             :value="old('primary_domain', $tenant->primaryDomain()?->domain ?? $tenant->domains->first()?->domain)" required constraint="primaryDomain" />
                        @php($sfx = trim((string) config('tenancy.tenant_host_suffix', 'localhost')))
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if($sfx !== '')
                                {{ __('Enter a short name; the system adds :suffix for the real browser address.', ['suffix' => '.'.$sfx]) }}
                            @else
                                {{ __('Full hostname visitors use; updating this changes the primary mapped domain.') }}
                            @endif
                        </p>
                        <x-admin::input-error :messages="$errors->get('primary_domain')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="email" :value="__('Admin email')" />
                        <x-admin::text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                             :value="old('email', $tenant->email)" required constraint="email" />
                        <x-admin::input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="plan_id" :value="__('Plan')" />
                        <select id="plan_id" name="plan_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— No plan —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id', $tenant->plan_id) == $plan->id ? 'selected' : '' }}>
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
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('If you change the plan or term, subscription end is recalculated from today (:days days per month).', ['days' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}</p>
                        <select id="subscription_months" name="subscription_months"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (string) old('subscription_months', (string) ($tenant->subscription_months ?? 1)) === (string) $m ? 'selected' : '' }}>
                                    {{ $m }} {{ $m === 1 ? __('month') : __('months') }}
                                </option>
                            @endforeach
                        </select>
                        <x-admin::input-error :messages="$errors->get('subscription_months')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label :value="__('Tenant status')" />
                        <div class="mt-1 flex items-center gap-3">
                            @if($tenant->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">Inactive</span>
                            @endif

                            <form method="POST" action="{{ $tenant->is_active ? route('admin.tenants.deactivate', $tenant) : route('admin.tenants.activate', $tenant) }}">
                                @csrf
                                <button type="submit"
                                        title="{{ $tenant->is_active ? __('Visitors will see the resort as unavailable until you resume.') : __('The resort site will be reachable on its domain again.') }}"
                                        class="inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-xs font-medium {{ $tenant->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                    @if($tenant->is_active)
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/></svg>
                                        {{ __('Suspend tenant') }}
                                    @else
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347c-.75.412-1.667-.13-1.667-.986V5.653z"/></svg>
                                        {{ __('Resume tenant') }}
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>

                    <div>
                        <x-admin::input-label for="password" :value="__('New password (leave blank to keep current)')" />
                        <x-admin::text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-admin::input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <x-admin::input-label for="password_confirmation" :value="__('Confirm new password')" />
                        <x-admin::text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <x-admin::primary-button>{{ __('Update Tenant') }}</x-admin::primary-button>
                            <a href="{{ route('admin.tenants.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                        </div>
                        <button type="button"
                                @click="deleteTenantModalOpen = true"
                                class="text-red-600 dark:text-red-400 hover:underline text-sm">
                            {{ __('Delete tenant') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mt-8">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Domains</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Attach domains so visitors can reach this tenant. Use subdomain (e.g. <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">resort1</code>) or full domain (e.g. <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">www.myresort.com</code>).</p>
                <form method="POST" action="{{ route('admin.tenants.domains.store', $tenant) }}" class="flex flex-wrap gap-2 mb-4">
                    @csrf
                    <input type="text" name="domain" value="{{ old('domain') }}" placeholder="resort1 or www.myresort.com"
                           class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-xs"/>
                    <x-admin::primary-button type="submit">Add domain</x-admin::primary-button>
                </form>
                @error('domain')
                    <p class="text-sm text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
                @enderror
                @if($tenant->domains->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No domains yet. Set the primary hostname above or add domains here.</p>
                @else
                    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="w-[48%] px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Domain') }}</th>
                                <th class="w-[14%] px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Primary') }}</th>
                                <th class="w-[38%] px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($tenant->domains as $d)
                                <tr>
                                    <td class="max-w-0 px-3 py-2 font-medium text-gray-900 dark:text-gray-100">
                                        <span class="block truncate" title="{{ $d->domain }}">{{ $d->domain }}</span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $d->is_primary ? 'Yes' : '—' }}</td>
                                    <td class="space-x-2 px-3 py-2 text-right whitespace-nowrap">
                                        @if(!$d->is_primary)
                                            <form action="{{ route('admin.tenants.domains.primary', [$tenant, $d]) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">Set primary</button>
                                            </form>
                                        @endif
                                        <x-confirm-form-button
                                            class="inline-block align-middle"
                                            :action="route('admin.tenants.domains.destroy', [$tenant, $d])"
                                            method="DELETE"
                                            :title="__('Remove domain')"
                                            :message="__('Remove this domain from the tenant?')"
                                            :confirm-label="__('Remove')">
                                            <button type="button" @click="open = true" class="text-red-600 dark:text-red-400 hover:underline text-xs">{{ __('Remove') }}</button>
                                        </x-confirm-form-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <form x-ref="deleteTenantForm" method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <div x-show="deleteTenantModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="deleteTenantModalOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
            <div x-show="deleteTenantModalOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Delete tenant') }}</h2>
                </div>
                <div class="px-6 py-5 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <p>{{ __('This will permanently delete the tenant and its database. This cannot be undone.') }}</p>
                    <p class="rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/60 px-3 py-2 text-red-800 dark:text-red-200">
                        <span class="font-medium text-red-900 dark:text-red-100">{{ __('Tenant:') }}</span>
                        <span class="block mt-0.5 font-semibold">{{ $tenant->tenant_name }}</span>
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 px-6 py-4">
                    <button type="button" @click="deleteTenantModalOpen = false"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" @click="confirmDeleteTenant()"
                            class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        {{ __('Delete tenant') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin::app-layout>
