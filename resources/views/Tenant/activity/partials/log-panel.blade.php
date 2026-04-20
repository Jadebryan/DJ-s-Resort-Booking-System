{{--
    $logs — iterable (paginator or collection)
    $showPagination — bool, default false
    $previewFooter — optional string when not paginating
--}}
@php
    $showPagination = $showPagination ?? false;
@endphp
<div class="rounded-xl border border-gray-200/80 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-200/80 p-5 min-w-0">
        <p class="text-sm text-gray-500">{{ __('Sorted newest first.') }}</p>
    </div>
    @if($logs->isEmpty())
        <div class="p-6 text-gray-600">{{ __('No activity yet.') }}</div>
    @else
        <div class="min-w-0 overflow-x-auto">
            <table class="min-w-[720px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('When') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('Actor') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('Action') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('Details') }}</th>
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
        @if($showPagination && method_exists($logs, 'hasPages') && $logs->hasPages())
            <div class="border-t border-gray-200/80 p-4">
                {{ $logs->links() }}
            </div>
        @elseif(! empty($previewFooter))
            <div class="border-t border-gray-200/80 p-4 text-sm text-gray-500">
                {{ $previewFooter }}
            </div>
        @endif
    @endif
</div>
