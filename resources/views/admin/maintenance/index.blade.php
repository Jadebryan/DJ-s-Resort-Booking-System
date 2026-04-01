<x-admin::app-layout>
    <x-slot name="header">
        <div class="leading-tight">
            <h1 class="text-lg font-semibold text-gray-800">{{ __('Maintenance') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('A unified queue for system and tenant maintenance work.') }}</p>
        </div>
    </x-slot>

    @php
        $statuses = [
            \App\Models\MaintenanceTicket::STATUS_OPEN => ['label' => __('Open'), 'tone' => 'amber'],
            \App\Models\MaintenanceTicket::STATUS_IN_PROGRESS => ['label' => __('In progress'), 'tone' => 'sky'],
            \App\Models\MaintenanceTicket::STATUS_RESOLVED => ['label' => __('Resolved'), 'tone' => 'emerald'],
        ];
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-5 -mt-1 text-left"
         x-data="{ createTicketOpen: @json($errors->any() && old('_from') === 'maintenance_ticket') }"
         @keydown.escape.window="createTicketOpen = false">
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-amber-800/90">{{ __('Open') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-950">{{ $ticketCounts[\App\Models\MaintenanceTicket::STATUS_OPEN] }}</p>
                <p class="mt-1 text-xs text-amber-900/70">{{ __('Awaiting triage') }}</p>
            </div>
            <div class="rounded-xl border border-sky-100 bg-sky-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-sky-800/90">{{ __('In progress') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-sky-950">{{ $ticketCounts[\App\Models\MaintenanceTicket::STATUS_IN_PROGRESS] }}</p>
                <p class="mt-1 text-xs text-sky-900/70">{{ __('Being worked') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-800/90">{{ __('Resolved') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-950">{{ $ticketCounts[\App\Models\MaintenanceTicket::STATUS_RESOLVED] }}</p>
                <p class="mt-1 text-xs text-emerald-900/70">{{ __('Closed tickets') }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-3 sm:flex-row sm:items-start sm:justify-between sm:px-5 sm:py-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Maintenance board') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Tickets are stored in the database. Change status from the card menu.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Tenants') }}</a>
                    <button type="button"
                            @click="createTicketOpen = true"
                            class="inline-flex items-center justify-center gap-1 rounded-lg bg-indigo-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-indigo-700 whitespace-nowrap">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('New ticket') }}
                    </button>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 min-w-0 gap-4 md:grid-cols-3 text-sm">
                    @foreach($statuses as $statusKey => $meta)
                        <div class="rounded-lg border border-gray-200 bg-gray-50/80 min-w-0 flex flex-col max-h-[min(70vh,520px)]">
                            <div class="flex items-center justify-between border-b border-gray-200 px-3 py-2.5 shrink-0">
                                <span class="text-xs font-semibold text-gray-700 uppercase">{{ $meta['label'] }}</span>
                                <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[11px] text-gray-700">{{ $ticketCounts[$statusKey] }}</span>
                            </div>
                            <div class="p-2 space-y-2 overflow-y-auto flex-1 min-h-[120px]">
                                @forelse($tickets->where('status', $statusKey) as $ticket)
                                    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                        <p class="text-sm font-semibold text-gray-900 leading-snug">{{ $ticket->title }}</p>
                                        <p class="mt-1 text-[11px] text-gray-500">
                                            {{ \App\Models\MaintenanceTicket::priorityLabels()[$ticket->priority] ?? $ticket->priority }}
                                            @if($ticket->related_tenant)
                                                · <span class="text-gray-600">{{ $ticket->related_tenant }}</span>
                                            @endif
                                        </p>
                                        @if($ticket->description)
                                            <p class="mt-2 text-xs text-gray-600 whitespace-pre-wrap break-words max-h-24 overflow-y-auto">{{ $ticket->description }}</p>
                                        @endif
                                        <form method="POST" action="{{ route('admin.maintenance.tickets.update', $ticket) }}" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <label class="sr-only" for="status-{{ $ticket->id }}">{{ __('Status') }}</label>
                                            <select id="status-{{ $ticket->id }}" name="status" onchange="this.form.requestSubmit()"
                                                    class="w-full rounded-md border border-gray-200 bg-gray-50 py-1.5 px-2 text-[11px] text-gray-800">
                                                @foreach($statuses as $sk => $m)
                                                    <option value="{{ $sk }}" @selected($ticket->status === $sk)>{{ $m['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        <p class="mt-1.5 text-[10px] text-gray-400">{{ $ticket->updated_at?->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <p class="p-2 text-xs text-gray-500">{{ __('No tickets in this column.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- New ticket modal --}}
        <div x-show="createTicketOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="createTicketOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
            <div x-show="createTicketOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Create maintenance ticket') }}</h2>
                    <button type="button" @click="createTicketOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="{{ __('Close') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <form method="POST" action="{{ route('admin.maintenance.tickets.store') }}" class="space-y-4 text-sm">
                        @csrf
                        <input type="hidden" name="_from" value="maintenance_ticket">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="mt-title">{{ __('Title') }}</label>
                            <input id="mt-title" type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 @error('title') border-red-500 @enderror"
                                   placeholder="{{ __('e.g. Tenant 2: Database backup failure') }}">
                            <x-admin::input-error :messages="$errors->get('title')" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1" for="mt-priority">{{ __('Priority') }}</label>
                                <select id="mt-priority" name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900">
                                    @foreach(\App\Models\MaintenanceTicket::priorityLabels() as $val => $label)
                                        <option value="{{ $val }}" @selected(old('priority', 'medium') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-1" for="mt-status">{{ __('Status') }}</label>
                                <select id="mt-status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900">
                                    @foreach($statuses as $sk => $m)
                                        <option value="{{ $sk }}" @selected(old('status', \App\Models\MaintenanceTicket::STATUS_OPEN) === $sk)>{{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="mt-related">{{ __('Related tenant (optional)') }}</label>
                            <input id="mt-related" type="text" name="related_tenant" value="{{ old('related_tenant') }}"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                   placeholder="{{ __('Tenant name or ID') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="mt-desc">{{ __('Description') }}</label>
                            <textarea id="mt-desc" name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900" placeholder="{{ __('What needs to be done?') }}">{{ old('description') }}</textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="createTicketOpen = false"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                {{ __('Save ticket') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin::app-layout>
