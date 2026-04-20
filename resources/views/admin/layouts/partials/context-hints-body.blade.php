@php
    $rn = request()->route()?->getName() ?? '';
@endphp
@switch($rn)
    @case('admin.dashboard')
        <p class="mt-1 text-sm text-gray-700">{{ __('Monitor tenants, payments, and signups from here. Use search to open any admin screen quickly.') }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50">{{ __('All tenants') }}</a>
            <a href="{{ route('admin.payments') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50">{{ __('Payments') }}</a>
            <a href="{{ route('admin.tenant-registrations.index') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50">{{ __('Signups') }}</a>
        </div>
        @break
    @case('admin.tenants.index')
        <p class="mt-1 text-sm text-gray-700">{{ __('Open a tenant to manage domains, plans, and status. Inactive tenants cannot reach their staff portal.') }}</p>
        @break
    @case('admin.tenant-registrations.index')
        <p class="mt-1 text-sm text-gray-700">{{ __('Approve signups to provision a resort database, or reject with a clear reason for the applicant.') }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50">{{ __('Tenants') }}</a>
        </div>
        @break
    @case('admin.maintenance')
        <p class="mt-1 text-sm text-gray-700">{{ __('Track incidents and internal tasks. Update ticket status as work progresses.') }}</p>
        @break
    @case('admin.subscriptions.index')
        <p class="mt-1 text-sm text-gray-700">{{ __('Plans control room limits and feature flags (e.g. activity logs). Save after edits.') }}</p>
        @break
    @case('admin.payments')
        <p class="mt-1 text-sm text-gray-700">{{ __('Track subscription and upgrade requests. Match references to bank or wallet transfers.') }}</p>
        @break
    @case('admin.reports')
        <p class="mt-1 text-sm text-gray-700">{{ __('Export or summarize platform metrics. Use filters before exporting large lists.') }}</p>
        @break
    @case('admin.settings')
        <p class="mt-1 text-sm text-gray-700">{{ __('Platform-wide options affect all tenants. Document changes for your team.') }}</p>
        @break
    @case('admin.profile.edit')
        <p class="mt-1 text-sm text-gray-700">{{ __('Superadmin accounts are powerful—use MFA where possible and unique passwords.') }}</p>
        @break
    @default
        @if(str_starts_with($rn, 'admin.tenants.'))
            <p class="mt-1 text-sm text-gray-700">{{ __('Save changes after editing plan, domains, or status. Deactivate to block staff login while keeping data.') }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50">{{ __('All tenants') }}</a>
            </div>
        @else
            <p class="mt-1 text-sm text-gray-700">{{ __('Collapse the sidebar on wide screens for more table space. Notifications aggregate tenant billing and signup events.') }}</p>
        @endif
@endswitch
