@php
    $fmtRes = static fn (string $r) => strtoupper(str_replace('_', ' ', $r));
@endphp
<x-tenant::app-layout>
    <script>
        window.rbacEditor = function (cfg) {
            return {
                roles: cfg.roles || [],
                staffDefs: cfg.staffDefs || {},
                customerDefs: cfg.customerDefs || {},
                modalOpen: false,
                activeRole: null,
                matrix: {},
                original: {},
                search: '',
                init() {
                    if (cfg.openEditId) {
                        this.openEdit(Number(cfg.openEditId));
                    }
                },
                get defs() {
                    if (!this.activeRole) return {};
                    return this.activeRole.kind === 'customer' ? this.customerDefs : this.staffDefs;
                },
                get resourceCards() {
                    const q = (this.search || '').toLowerCase().trim();
                    const out = [];
                    for (const [key, actions] of Object.entries(this.defs)) {
                        const label = String(key).replaceAll('_', ' ').toUpperCase();
                        const visible = !q || label.toLowerCase().includes(q) || String(key).toLowerCase().includes(q);
                        out.push({ key, label, actions, visible });
                    }
                    return out;
                },
                openEdit(id) {
                    const role = this.roles.find(r => Number(r.id) === Number(id));
                    if (!role) return;
                    this.activeRole = role;
                    this.matrix = JSON.parse(JSON.stringify(role.permissions || {}));
                    for (const key of Object.keys(this.defs)) {
                        if (!Array.isArray(this.matrix[key])) this.matrix[key] = [];
                    }
                    this.original = JSON.parse(JSON.stringify(this.matrix));
                    this.search = '';
                    this.modalOpen = true;
                },
                toggle(res, act) {
                    const cur = Array.isArray(this.matrix[res]) ? [...this.matrix[res]] : [];
                    const i = cur.indexOf(act);
                    if (i >= 0) cur.splice(i, 1); else cur.push(act);
                    this.matrix[res] = cur;
                },
                disableResource(res) {
                    this.matrix[res] = [];
                },
                disableAll() {
                    for (const key of Object.keys(this.defs)) this.matrix[key] = [];
                },
                resetFromOriginal() {
                    this.matrix = JSON.parse(JSON.stringify(this.original));
                },
                syncHiddenInputs() {
                    const box = document.getElementById('rbac-hidden-fields');
                    if (!box) return;
                    box.innerHTML = '';
                    for (const [res, acts] of Object.entries(this.matrix)) {
                        if (!Array.isArray(acts)) continue;
                        for (const act of acts) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'permissions[' + res + '][]';
                            input.value = act;
                            box.appendChild(input);
                        }
                    }
                },
            };
        };
    </script>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Role-based access control') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Centralize staff and guest portal permissions. Use Edit to adjust a role, then assign roles on Staff & Guests pages.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-8"
         x-data="window.rbacEditor({
            roles: @js($rolesForJs ?? []),
            staffDefs: @js($staffDefs ?? []),
            customerDefs: @js($customerDefs ?? []),
            openEditId: @js(session('openEditRoleId')),
         })">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ __('Staff roles') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $staffRoles->count() }}</p>
                </div>
                <div class="rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ __('Guest roles') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $customerRoles->count() }}</p>
                </div>
                <div class="rounded-xl border border-gray-200/80 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ __('Resources') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $staffResourceCount + $customerResourceCount }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('tenant.rbac.initialize') }}" class="shrink-0">
                @csrf
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 sm:w-auto">
                    {{ __('Initialize default roles') }}
                </button>
            </form>
        </div>

        <section class="space-y-3">
            <div class="flex items-end justify-between gap-2 border-b border-gray-200 pb-2">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">{{ __('Staff permission sets') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('Applies to team members signed in as Staff (not Owner).') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Role') }}</th>
                            <th class="px-4 py-3">{{ __('Description') }}</th>
                            <th class="px-4 py-3">{{ __('Permissions') }}</th>
                            <th class="px-4 py-3">{{ __('Last updated') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($staffRoles as $r)
                            @php
                                $permCount = collect($r->permissions ?? [])->flatten()->count();
                            @endphp
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $r->name }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $r->description ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="text-xs text-gray-500">{{ $permCount }} {{ __('actions') }}</span>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($r->permissions ?? [] as $res => $acts)
                                            @if (is_array($acts) && count($acts))
                                                <span class="inline-block max-w-full rounded-md bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700" title="{{ implode(', ', $acts) }}">
                                                    <span class="font-semibold">{{ $fmtRes((string) $res) }}</span>:
                                                    {{ implode(', ', $acts) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $r->updated_at?->format('M j, Y g:i A') ?? '—' }}
                                    @if ($r->updatedByStaff)
                                        <div class="text-gray-500">{{ $r->updatedByStaff->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button"
                                            @click="openEdit({{ $r->id }})"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-teal-700 shadow-sm hover:bg-teal-50">
                                        {{ __('Edit') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-600">
                                    {{ __('No staff roles yet. Click “Initialize default roles” to create Manager and Reception presets.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex items-end justify-between gap-2 border-b border-gray-200 pb-2">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">{{ __('Guest portal roles') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('Controls what signed-in guests can do in My bookings and profile.') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Role') }}</th>
                            <th class="px-4 py-3">{{ __('Description') }}</th>
                            <th class="px-4 py-3">{{ __('Permissions') }}</th>
                            <th class="px-4 py-3">{{ __('Last updated') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($customerRoles as $r)
                            @php
                                $permCount = collect($r->permissions ?? [])->flatten()->count();
                            @endphp
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $r->name }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $r->description ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="text-xs text-gray-500">{{ $permCount }} {{ __('actions') }}</span>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($r->permissions ?? [] as $res => $acts)
                                            @if (is_array($acts) && count($acts))
                                                <span class="inline-block max-w-full rounded-md bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700">
                                                    <span class="font-semibold">{{ $fmtRes((string) $res) }}</span>:
                                                    {{ implode(', ', $acts) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $r->updated_at?->format('M j, Y g:i A') ?? '—' }}
                                    @if ($r->updatedByStaff)
                                        <div class="text-gray-500">{{ $r->updatedByStaff->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button"
                                            @click="openEdit({{ $r->id }})"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-teal-700 shadow-sm hover:bg-teal-50">
                                        {{ __('Edit') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-600">
                                    {{ __('No guest roles yet. Initialize defaults to add Standard and Limited guest presets.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Edit modal --}}
        <div x-show="modalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-8"
             x-transition.opacity>
            <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm" @click="modalOpen = false"></div>
            <div class="relative mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900" x-text="activeRole ? ('{{ __('Editing') }} ' + activeRole.name) : ''"></h2>
                        <p class="text-xs text-gray-500 mt-1" x-show="activeRole && activeRole.updated_at">
                            <span x-text="activeRole && activeRole.updated_by ? (activeRole.updated_by + ' · ') : ''"></span>
                            <span x-text="activeRole ? new Date(activeRole.updated_at).toLocaleString() : ''"></span>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">{{ __('Toggle permissions per resource, then save.') }}</p>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100" @click="modalOpen = false" aria-label="{{ __('Close') }}">✕</button>
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <input type="search" x-model="search" placeholder="{{ __('Search resources…') }}"
                           class="min-w-[12rem] flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    <button type="button" @click="resetFromOriginal()"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('Reset') }}
                    </button>
                    <button type="button" @click="disableAll()"
                            class="rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white hover:bg-teal-700">
                        {{ __('Disable all') }}
                    </button>
                </div>

                <form :action="activeRole ? activeRole.update_url : '#'" method="POST" class="space-y-6" @submit="syncHiddenInputs()">
                    @csrf
                    @method('PATCH')
                    <div id="rbac-hidden-fields"></div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 max-h-[55vh] overflow-y-auto pr-1">
                        <template x-for="card in resourceCards" :key="card.key">
                            <div class="rounded-xl border border-gray-200 p-4 shadow-sm" x-show="card.visible">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900" x-text="card.label"></h3>
                                        <p class="text-[11px] text-gray-500 mt-0.5"
                                           x-text="(matrix[card.key] || []).length + ' / ' + card.actions.length + ' {{ __('actions') }}'"></p>
                                    </div>
                                    <button type="button" @click="disableResource(card.key)"
                                            class="shrink-0 text-[11px] font-medium text-teal-700 hover:underline">{{ __('Off') }}</button>
                                </div>
                                <ul class="mt-3 space-y-2">
                                    <template x-for="act in card.actions" :key="card.key + ':' + act">
                                        <li class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-2 py-1.5">
                                            <span class="text-sm text-gray-800 capitalize" x-text="act.replaceAll('_', ' ')"></span>
                                            <button type="button"
                                                    @click="toggle(card.key, act)"
                                                    :class="(matrix[card.key] || []).includes(act) ? 'bg-teal-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                <span :class="(matrix[card.key] || []).includes(act) ? 'translate-x-5' : 'translate-x-1'"
                                                      class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition translate-y-1"></span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                        <button type="button" @click="modalOpen = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</button>
                        <button type="submit"
                                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">{{ __('Save permissions') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-tenant::app-layout>
