<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">Tenants</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">Manage resorts, plans, domains, and subscription health.</p>
        </div>
    </x-slot>

    @php
        $plansForModal = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-3"
         x-data="{
            createTenantOpen: @json($errors->any() && old('_from') === 'create'),
            editTenantOpen: false,
            editTenant: null,
            deleteTenantModalOpen: false,
            pendingDelete: { name: '', action: '' },
            plans: @js($plansForModal),
            tenantFilter: '',
            openEditTenant(data) {
                this.editTenant = data;
                this.editTenantOpen = true;
            },
            openDeleteTenant(name, action) {
                this.pendingDelete = { name, action };
                this.deleteTenantModalOpen = true;
            },
            confirmDeleteTenant() {
                this.$refs.deleteTenantForm.submit();
            }
         }"
         @keydown.escape.window="createTenantOpen = false; editTenantOpen = false; deleteTenantModalOpen = false"
         @admin-tenants-open-create.window="createTenantOpen = true">
        <div class="w-full min-w-0">
            @if($tenants->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                    <p class="text-sm">No tenants yet.</p>
                    <button type="button" @click="createTenantOpen = true" class="mt-3 inline-flex items-center gap-1 rounded-full bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create your first tenant
                    </button>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 px-4 py-3 sm:px-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3 min-w-0 flex-1">
                            <p class="text-xs text-gray-500 shrink-0">{{ $tenants->count() }} {{ __('tenant(s)') }}</p>
                            <div class="relative w-full sm:max-w-xs">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                                </span>
                                <label for="tenant-table-search" class="sr-only">{{ __('Search tenants') }}</label>
                                <input id="tenant-table-search" type="search" x-model="tenantFilter" autocomplete="off"
                                       placeholder="{{ __('Search name, email, domain, plan…') }}"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                        <button type="button"
                                @click="window.dispatchEvent(new CustomEvent('admin-tenants-open-create'))"
                                class="inline-flex shrink-0 items-center justify-center gap-1 rounded-lg bg-indigo-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-indigo-700 sm:py-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Add Tenant') }}
                        </button>
                    </div>
                    <div class="w-full min-w-0 overflow-hidden">
                        <table class="w-full min-w-0 table-fixed divide-y divide-gray-200 text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="min-w-0 px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-3 sm:py-2.5 sm:text-xs sm:tracking-wider">{{ __('Tenant') }}</th>
                                    <th class="hidden min-w-0 px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 md:table-cell sm:px-3 sm:text-xs sm:tracking-wider">{{ __('Plan') }}</th>
                                    <th class="hidden min-w-0 px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 md:table-cell sm:px-3 sm:text-xs sm:tracking-wider">{{ __('Days left') }}</th>
                                    <th class="hidden min-w-0 px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 lg:table-cell sm:px-3 sm:text-xs sm:tracking-wider">{{ __('Contact') }}</th>
                                    <th class="hidden min-w-0 px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 lg:table-cell sm:px-3 sm:text-xs sm:tracking-wider">{{ __('Primary domain') }}</th>
                                    <th class="min-w-0 px-2 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:px-3 sm:text-xs sm:tracking-wider">{{ __('Status') }}</th>
                                    <th class="w-[5.25rem] px-1 py-2 text-right text-[10px] font-semibold uppercase tracking-wide text-gray-500 sm:w-[5.75rem] sm:px-2 sm:text-xs sm:tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($tenants as $tenant)
                                    @php
                                        $tenantSearchBlob = strtolower(implode(' ', array_filter([
                                            $tenant->tenant_name,
                                            $tenant->email,
                                            $tenant->primaryDomain()?->domain,
                                            $tenant->plan?->name,
                                        ], fn ($v) => $v !== null && $v !== '')));
                                        $domain = $tenant->primaryDomain()?->domain;
                                    @endphp
                                    <tr class="hover:bg-gray-50/60"
                                        data-tenant-search="{{ e($tenantSearchBlob) }}"
                                        x-show="!(tenantFilter || '').trim() || ($el.dataset.tenantSearch || '').includes((tenantFilter || '').toLowerCase().trim())">
                                        <td class="max-w-0 px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-[10px] font-semibold text-indigo-700 sm:h-9 sm:w-9 sm:text-xs">
                                                    {{ strtoupper(substr($tenant->tenant_name, 0, 2)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-xs font-medium text-gray-900 sm:text-sm" title="{{ $tenant->tenant_name }}">{{ $tenant->tenant_name }}</p>
                                                    <p class="truncate text-[11px] text-gray-500 md:hidden" title="{{ $tenant->email }}">{{ $tenant->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden max-w-0 px-2 py-2 align-top md:table-cell sm:px-3 sm:py-2.5">
                                            @if($tenant->plan)
                                                <span class="inline-flex max-w-full items-center truncate rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700" title="{{ $tenant->plan->name }}">{{ $tenant->plan->name }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">{{ __('No plan') }}</span>
                                            @endif
                                        </td>
                                        <td class="hidden max-w-0 px-2 py-2 align-top md:table-cell sm:px-3 sm:py-2.5">
                                            @if($tenant->subscription_ends_at)
                                                @php
                                                    $daysRemaining = now()->startOfDay()->diffInDays($tenant->subscription_ends_at->copy()->startOfDay(), false);
                                                @endphp
                                                @if($daysRemaining >= 0)
                                                    <span class="block truncate text-xs text-gray-700 sm:text-sm" title="{{ $daysRemaining }} {{ trans_choice('day|days', $daysRemaining) }}">{{ $daysRemaining }} {{ trans_choice('day|days', $daysRemaining) }}</span>
                                                @else
                                                    @php
                                                        $expiredLabel = __('Expired :n ago', ['n' => trans_choice(':count day|:count days', abs($daysRemaining), ['count' => abs($daysRemaining)])]);
                                                    @endphp
                                                    <span class="block truncate text-[11px] text-red-600 sm:text-xs" title="{{ $expiredLabel }}">{{ $expiredLabel }}</span>
                                                @endif
                                            @elseif($tenant->plan_id)
                                                <span class="block truncate text-[11px] text-gray-500 sm:text-xs" title="{{ __('Subscription not set') }}">{{ __('Subscription not set') }}</span>
                                            @else
                                                <span class="block truncate text-[11px] text-gray-400 sm:text-xs" title="{{ __('No subscription') }}">{{ __('No subscription') }}</span>
                                            @endif
                                        </td>
                                        <td class="hidden max-w-0 px-2 py-2 align-top lg:table-cell sm:px-3 sm:py-2.5">
                                            <span class="block truncate text-xs text-gray-700 sm:text-sm" title="{{ $tenant->email }}">{{ $tenant->email }}</span>
                                        </td>
                                        <td class="hidden max-w-0 px-2 py-2 align-top lg:table-cell sm:px-3 sm:py-2.5">
                                            @if($domain)
                                                <a href="{{ absolute_url_for_tenant_host($domain, '/') }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="block truncate text-[11px] font-medium text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 rounded-sm"
                                                   title="{{ __('Open :host (new tab)', ['host' => $domain]) }}">{{ $domain }}</a>
                                            @else
                                                <span class="text-xs text-gray-400">{{ __('No domain') }}</span>
                                            @endif
                                        </td>
                                        <td class="max-w-0 px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                            @if($tenant->is_active)
                                                <span class="inline-flex max-w-full items-center truncate rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 sm:px-2 sm:text-[11px]" title="{{ __('Active') }}">{{ __('Active') }}</span>
                                            @else
                                                <span class="inline-flex max-w-full items-center truncate rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 sm:px-2 sm:text-[11px]" title="{{ __('Inactive') }}">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="max-w-0 w-[5.25rem] px-1 py-2 align-top text-right sm:w-[5.75rem] sm:px-2 sm:py-2.5">
                                            <div class="flex min-w-0 items-center justify-end gap-0.5">
                                                <button type="button"
                                                        @click="openEditTenant({
                                                            id: {{ $tenant->id }},
                                                            tenant_name: '{{ addslashes($tenant->tenant_name) }}',
                                                            primary_domain: '{{ addslashes($tenant->primaryDomain()?->domain ?? $tenant->domains->first()?->domain ?? '') }}',
                                                            email: '{{ addslashes($tenant->email) }}',
                                                            plan_id: {{ $tenant->plan_id ?? 'null' }},
                                                            subscription_months: {{ (int) ($tenant->subscription_months ?? 1) }}
                                                        })"
                                                        class="inline-flex shrink-0 items-center justify-center rounded border border-gray-200 bg-white p-1 text-gray-600 hover:border-indigo-200 hover:text-indigo-700"
                                                        title="{{ __('Edit tenant') }}" aria-label="{{ __('Edit tenant') }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.232-6.232a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-.828.5l-4 1a1 1 0 01-1.212-1.212l1-4a2 2 0 01.5-.828z"/></svg>
                                                </button>
                                                <button type="button"
                                                        @click="openDeleteTenant(@js($tenant->tenant_name), @js(route('admin.tenants.destroy', $tenant)))"
                                                        class="inline-flex shrink-0 items-center justify-center rounded border border-red-100 bg-white p-1 text-red-500 hover:bg-red-50"
                                                        title="{{ __('Delete tenant') }}" aria-label="{{ __('Delete tenant') }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h2a2 2 0 012 2v2"/></svg>
                                                </button>
                                                <form method="POST" action="{{ $tenant->is_active ? route('admin.tenants.deactivate', $tenant) : route('admin.tenants.activate', $tenant) }}" class="inline shrink-0">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center justify-center rounded border p-1 {{ $tenant->is_active ? 'border-amber-100 text-amber-600 hover:bg-amber-50' : 'border-emerald-100 text-emerald-600 hover:bg-emerald-50' }}"
                                                            title="{{ $tenant->is_active ? __('Suspend tenant — site unavailable for visitors') : __('Resume tenant — site available for visitors again') }}"
                                                            aria-label="{{ $tenant->is_active ? __('Suspend tenant') : __('Resume tenant') }}">
                                                        @if($tenant->is_active)
                                                            {{-- Pause: suspend (not delete) --}}
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/></svg>
                                                        @else
                                                            {{-- Play: resume --}}
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347c-.75.412-1.667-.13-1.667-.986V5.653z"/></svg>
                                                        @endif
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    {{-- Create tenant modal --}}
    <div x-show="createTenantOpen" x-cloak
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div @click="createTenantOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
        <div x-show="createTenantOpen" @click.stop
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative w-full max-w-xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-900">Create tenant</h2>
                <button type="button" @click="createTenantOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-5">
                <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-4 text-sm">
                    @csrf
                    <input type="hidden" name="_from" value="create">
                    <div>
                        <x-admin::input-label for="tenant_name" :value="__('Tenant name')" />
                        <x-admin::text-input id="tenant_name" name="tenant_name" type="text" class="mt-1 block w-full"
                                             :value="old('tenant_name')" required />
                        <x-admin::input-error :messages="$errors->get('tenant_name')" class="mt-1" />
                    </div>
                    <div>
                        <x-admin::input-label for="primary_domain" :value="__('Preferred site name')" />
                        <x-admin::text-input id="primary_domain" name="primary_domain" type="text" class="mt-1 block w-full"
                                             :value="old('primary_domain')" placeholder="myresort" required />
                        @php($sfxModal = trim((string) config('tenancy.tenant_host_suffix', 'localhost')))
                        <p class="mt-1 text-[11px] text-gray-500">
                            @if($sfxModal !== '')
                                {{ __('Short name only; :suffix is added automatically.', ['suffix' => '.'.$sfxModal]) }}
                            @else
                                {{ __('Full hostname guests use in the browser.') }}
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-admin::input-label for="password" :value="__('Password')" />
                            <x-admin::text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                            <x-admin::input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>
                        <div>
                            <x-admin::input-label for="password_confirmation" :value="__('Confirm password')" />
                            <x-admin::text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        </div>
                    </div>
                    <div>
                        <x-admin::input-label for="plan_id" :value="__('Plan (optional)')" />
                        <select id="plan_id" name="plan_id" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                            <option value="">No plan</option>
                            @foreach(\App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get() as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                        <x-admin::input-error :messages="$errors->get('plan_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-admin::input-label for="subscription_months_create" :value="__('Subscription length')" />
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            {{ __('Applies when a plan is selected. Each month adds :days days to subscription end.', ['days' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}
                        </p>
                        <select id="subscription_months_create" name="subscription_months"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected((string) old('subscription_months', '1') === (string) $m)>
                                    {{ $m }} {{ $m === 1 ? __('month') : __('months') }}
                                </option>
                            @endforeach
                        </select>
                        <x-admin::input-error :messages="$errors->get('subscription_months')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="createTenantOpen = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                            Create tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit tenant modal --}}
    <div x-show="editTenantOpen && editTenant" x-cloak
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div @click="editTenantOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
        <div x-show="editTenantOpen && editTenant" @click.stop
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative w-full max-w-xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-900">Edit tenant</h2>
                <button type="button" @click="editTenantOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-5">
                <form method="POST"
                      :action="editTenant ? '{{ url('admin/tenants') }}/' + editTenant.id : '#'"
                      class="space-y-4 text-sm">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-admin::input-label for="edit_tenant_name" :value="__('Tenant name')" />
                        <x-admin::text-input id="edit_tenant_name" name="tenant_name" type="text" class="mt-1 block w-full"
                                             x-model="editTenant.tenant_name" required />
                    </div>
                    <div>
                        <x-admin::input-label for="edit_primary_domain" :value="__('Preferred site name')" />
                        <x-admin::text-input id="edit_primary_domain" name="primary_domain" type="text" class="mt-1 block w-full"
                                             x-model="editTenant.primary_domain" required />
                        @php($sfxEdit = trim((string) config('tenancy.tenant_host_suffix', 'localhost')))
                        <p class="mt-1 text-[11px] text-gray-500">
                            @if($sfxEdit !== '')
                                {{ __('Short name; :suffix is added for the live hostname.', ['suffix' => '.'.$sfxEdit]) }}
                            @else
                                {{ __('Must match what visitors type in the browser.') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <x-admin::input-label for="edit_email" :value="__('Admin email')" />
                        <x-admin::text-input id="edit_email" name="email" type="email" class="mt-1 block w-full"
                                             x-model="editTenant.email" required />
                    </div>
                    <div>
                        <x-admin::input-label for="edit_plan_id" :value="__('Plan (optional)')" />
                        <select id="edit_plan_id" name="plan_id"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                            <option value="">No plan</option>
                            <template x-for="plan in plans" :key="plan.id">
                                <option :value="plan.id" x-text="plan.name"
                                        :selected="editTenant && Number(editTenant.plan_id) === Number(plan.id)"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <x-admin::input-label for="edit_subscription_months" :value="__('Subscription length')" />
                        <p class="mt-1 text-[11px] text-gray-500">{{ __('If you change the plan or term, subscription end is recalculated from today (:days days per month).', ['days' => \App\Models\TenantRegistrationRequest::BILLING_DAYS_PER_MONTH]) }}</p>
                        <select id="edit_subscription_months" name="subscription_months"
                                x-model="editTenant.subscription_months"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}">{{ $m }} {{ $m === 1 ? __('month') : __('months') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-admin::input-label for="edit_password" :value="__('New password (optional)')" />
                            <x-admin::text-input id="edit_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        </div>
                        <div>
                            <x-admin::input-label for="edit_password_confirmation" :value="__('Confirm password')" />
                            <x-admin::text-input id="edit_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-2">
                        <button type="button" @click="editTenantOpen = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form x-ref="deleteTenantForm" method="POST" :action="pendingDelete.action" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- Delete tenant confirmation --}}
    <div x-show="deleteTenantModalOpen" x-cloak
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true"
         aria-labelledby="delete-tenant-title">
        <div @click="deleteTenantModalOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
        <div x-show="deleteTenantModalOpen" @click.stop
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 id="delete-tenant-title" class="text-sm font-semibold text-gray-900">{{ __('Delete tenant') }}</h2>
            </div>
            <div class="px-6 py-5 space-y-3 text-sm text-gray-600">
                <p>{{ __('This will permanently delete the tenant and its database. This cannot be undone.') }}</p>
                <p class="rounded-lg bg-red-50 border border-red-100 px-3 py-2 text-red-800">
                    <span class="font-medium text-red-900">{{ __('Tenant:') }}</span>
                    <span class="block mt-0.5 font-semibold text-red-950" x-text="pendingDelete.name"></span>
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50/80 px-6 py-4">
                <button type="button" @click="deleteTenantModalOpen = false"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('Cancel') }}
                </button>
                <button type="button" @click="confirmDeleteTenant()"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    {{ __('Delete tenant') }}
                </button>
            </div>
        </div>
    </div>

    </div>
</x-admin::app-layout>
