<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Custom domains') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Hostnames that open this resort for staff and guests.') }}</p>
        </div>
    </x-slot>

    <div class="w-full min-w-0 max-w-7xl space-y-6">

        <div class="rounded-xl border border-gray-200/80 bg-white p-6 shadow-sm overflow-hidden">
            @php
                $siteHost = $domains->firstWhere('is_primary', true) ?? $domains->first();
            @endphp
            <p class="mb-4 text-gray-600">
                Add hostnames that should open this resort (e.g. <strong>www.myresort.com</strong> or <strong>stay.myresort.localhost</strong>).
                @if($siteHost)
                    Primary site: <code class="rounded bg-gray-100 px-1.5 py-0.5 text-sm text-gray-800">//{{ $siteHost->domain }}</code>
                @else
                    Add at least one domain so guests can reach you.
                @endif
            </p>
            <form method="POST" action="{{ tenant_url('domains') }}" class="mb-6 flex flex-wrap gap-2">
                @csrf
                <input type="text" name="domain" value="{{ old('domain') }}" placeholder="www.myresort.com or resort1"
                       class="min-w-[200px] flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"/>
                <button type="submit" class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-teal-700">
                    Add domain
                </button>
            </form>
            @error('domain')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if($domains->isEmpty())
                <p class="text-sm text-gray-500">No custom domains yet. Add one above.</p>
            @else
                <div class="min-w-0 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-[560px] w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ __('Domain') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ __('Primary') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($domains as $d)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $d->domain }}</td>
                                    <td class="px-3 py-2">{{ $d->is_primary ? __('Yes') : '—' }}</td>
                                    <td class="px-3 py-2 text-right space-x-2">
                                        @if(!$d->is_primary)
                                            <form action="{{ tenant_url('domains/' . $d->id . '/primary') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700 transition hover:bg-teal-100">{{ __('Set primary') }}</button>
                                            </form>
                                        @endif
                                        <x-confirm-form-button
                                            class="inline-block"
                                            :action="tenant_url('domains/' . $d->id)"
                                            method="DELETE"
                                            :title="__('Remove domain')"
                                            :message="__('Remove this domain? Visitors will no longer reach your resort on this hostname.')"
                                            :confirm-label="__('Remove')">
                                            <button type="button" @click="open = true" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50">{{ __('Remove') }}</button>
                                        </x-confirm-form-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-tenant::app-layout>
