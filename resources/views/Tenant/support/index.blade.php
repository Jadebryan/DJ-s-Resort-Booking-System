<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Support') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Report issues and request help from the platform admin.') }}</p>
        </div>
    </x-slot>

    @php
        $statuses = [
            \App\Models\MaintenanceTicket::STATUS_OPEN => ['label' => __('Open')],
            \App\Models\MaintenanceTicket::STATUS_IN_PROGRESS => ['label' => __('In progress')],
            \App\Models\MaintenanceTicket::STATUS_RESOLVED => ['label' => __('Resolved')],
        ];
    @endphp

    <div class="w-full min-w-0 max-w-5xl space-y-4"
         x-data="{
            createTicketOpen: @js((session('openSupportTicketModal') === true) || ($errors->any() && old('_from') === 'tenant_support_ticket')),
            ticketFilter: '',
         }"
         @keydown.escape.window="createTicketOpen = false">

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 sm:px-5">
                <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between lg:gap-x-4 lg:gap-y-3">
                    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-3">
                        <div class="w-full min-w-0 sm:min-w-[min(100%,18rem)] sm:flex-1">
                            <label for="support-index-search" class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Search') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                                </span>
                                <input id="support-index-search" type="search" x-model="ticketFilter" autocomplete="off"
                                       placeholder="{{ __('Title, description…') }}"
                                       class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="flex items-end gap-3 text-xs text-gray-500">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                <span class="font-medium text-gray-700">{{ __('Open') }}:</span>
                                <span class="tabular-nums">{{ $ticketCounts[\App\Models\MaintenanceTicket::STATUS_OPEN] ?? 0 }}</span>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                <span class="font-medium text-gray-700">{{ __('In progress') }}:</span>
                                <span class="tabular-nums">{{ $ticketCounts[\App\Models\MaintenanceTicket::STATUS_IN_PROGRESS] ?? 0 }}</span>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                <span class="font-medium text-gray-700">{{ __('Resolved') }}:</span>
                                <span class="tabular-nums">{{ $ticketCounts[\App\Models\MaintenanceTicket::STATUS_RESOLVED] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col gap-1 sm:ml-auto">
                        <span class="mb-1 block text-xs font-medium leading-tight text-gray-600">{{ __('Actions') }}</span>
                        <div class="flex h-10 items-center">
                            <button type="button"
                                    @click="createTicketOpen = true"
                                    class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                {{ __('New ticket') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                @if(($tickets ?? collect())->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center text-sm text-gray-600">
                        <p>{{ __('No support tickets yet.') }}</p>
                        <button type="button" @click="createTicketOpen = true" class="mt-3 font-medium text-teal-700 hover:text-teal-800 hover:underline">
                            {{ __('Create your first ticket') }}
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-gray-200/80 bg-white shadow-sm">
                        <table class="min-w-[720px] w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Title') }}</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 md:table-cell">{{ __('Priority') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 lg:table-cell">{{ __('Updated') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($tickets as $t)
                                    @php
                                        $blob = strtolower(implode(' ', array_filter([
                                            $t->title,
                                            $t->description,
                                            $t->priority,
                                            $t->status,
                                        ], fn ($v) => $v !== null && $v !== '')));
                                    @endphp
                                    <tr class="hover:bg-gray-50/60" data-ticket-search="{{ e($blob) }}"
                                        x-show="!(ticketFilter || '').trim() || ($el.dataset.ticketSearch || '').includes((ticketFilter || '').toLowerCase().trim())">
                                        <td class="px-4 py-3 align-top">
                                            <p class="font-medium text-gray-900">{{ $t->title }}</p>
                                            @if($t->description)
                                                <p class="mt-1 text-xs text-gray-600 line-clamp-2">{{ $t->description }}</p>
                                            @endif
                                        </td>
                                        <td class="hidden px-4 py-3 align-top md:table-cell">
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                                {{ \App\Models\MaintenanceTicket::priorityLabels()[$t->priority] ?? $t->priority }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                                {{ $statuses[$t->status]['label'] ?? $t->status }}
                                            </span>
                                        </td>
                                        <td class="hidden px-4 py-3 align-top text-xs text-gray-500 lg:table-cell">
                                            {{ $t->updated_at?->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

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
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Create support ticket') }}</h2>
                    <button type="button" @click="createTicketOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="{{ __('Close') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <form method="POST" action="{{ route('tenant.support.tickets.store') }}" class="space-y-4 text-sm">
                        @csrf
                        <input type="hidden" name="_from" value="tenant_support_ticket">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="ts-title">{{ __('Title') }}</label>
                            <input id="ts-title" type="text" name="title" value="{{ old('title') }}" required
                                   {{ \App\Support\InputHtmlAttributes::title(255) }}
                                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="ts-priority">{{ __('Priority') }}</label>
                            <select id="ts-priority" name="priority" required
                                    class="h-10 w-full rounded-lg border border-gray-300 px-3 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @foreach(\App\Models\MaintenanceTicket::priorityLabels() as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('priority', 'medium') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('priority') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1" for="ts-desc">{{ __('Description') }}</label>
                            <textarea id="ts-desc" name="description" rows="4"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                      placeholder="{{ __('Describe the issue and steps to reproduce (if any).') }}">{{ old('description') }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" @click="createTicketOpen = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                                {{ __('Submit ticket') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-tenant::app-layout>

