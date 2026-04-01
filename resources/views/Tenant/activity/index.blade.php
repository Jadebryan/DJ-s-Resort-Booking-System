<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Activity Log') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Recent actions by staff and owners on this resort.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/80 p-5 min-w-0">
                <p class="text-sm text-gray-500">{{ __('Sorted newest first.') }}</p>
            </div>
            @if($logs->isEmpty())
                <div class="p-6 text-gray-600">No activity yet.</div>
            @else
                <div class="min-w-0 overflow-x-auto">
                    <table class="min-w-[720px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">When</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Actor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($logs as $log)
                                <tr class="transition hover:bg-gray-50/50">
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        @if($log->actor_type === 'guest')
                                            <span class="text-gray-600">{{ __('Guest') }}</span>
                                            @if($log->regularUser)
                                                <span class="block text-xs font-normal text-gray-500">{{ $log->regularUser->name }}</span>
                                            @endif
                                        @else
                                            {{ $log->user?->name ?? ($log->actor_type === 'system' ? __('System') : '—') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->action }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->description ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200/80 p-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-tenant::app-layout>
