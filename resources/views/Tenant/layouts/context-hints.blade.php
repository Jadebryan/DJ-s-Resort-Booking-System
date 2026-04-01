@php
    $rn = request()->route()?->getName() ?? '';
    $isAdmin = auth('tenant')->check() && auth('tenant')->user()->role === 'admin';
@endphp
<div class="mb-5 rounded-xl border border-teal-100 bg-gradient-to-br from-teal-50/90 to-white px-4 py-3 shadow-sm sm:px-5 sm:py-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-teal-800">{{ __('Shortcuts & tips') }}</p>
            @switch($rn)
                @case('tenant.dashboard')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Use the search box to jump to any staff screen. Notifications show recent booking and billing activity.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('bookings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Open bookings') }}</a>
                        <a href="{{ tenant_url('rooms') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Manage rooms') }}</a>
                        <a href="{{ tenant_url('/') }}#rooms" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Guest landing') }} ↗</a>
                    </div>
                    @break
                @case('tenant.rooms.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Add rooms here; guests book from your public “Book” page. Set clear photos and nightly rates.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('/') }}#rooms" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Preview landing') }} ↗</a>
                        <a href="{{ tenant_url('reports') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Room revenue (reports)') }}</a>
                    </div>
                    @break
                @case('tenant.bookings.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Confirm or cancel from the list; open payment proof when guests upload. Use the calendar for a month view.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('bookings/calendar') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Booking calendar') }}</a>
                        <a href="{{ tenant_url('reports') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Reports & export') }}</a>
                    </div>
                    @break
                @case('tenant.bookings.calendar')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Each day shows overlapping stays by status. Switch months to plan housekeeping and check-ins.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('bookings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Back to list') }}</a>
                    </div>
                    @break
                @case('tenant.reports.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Download CSV or PDF for accounting; analytics breaks revenue down by time if your plan includes it.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('reports/export/csv') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Download CSV') }}</a>
                        <a href="{{ tenant_url('reports/export/pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Print / PDF') }}</a>
                        <a href="{{ tenant_url('bookings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Live bookings') }}</a>
                    </div>
                    @break
                @case('tenant.reports.analytics')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Compare months and recent days to spot seasonality. Figures use confirmed bookings only.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('reports') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Main reports') }}</a>
                    </div>
                    @break
                @case('tenant.branding.edit')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Changes apply to your public landing page. Save, then preview how guests see your resort.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('/') }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Open public site') }} ↗</a>
                        <a href="{{ tenant_url('/') }}#rooms" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Public landing') }} ↗</a>
                    </div>
                    @break
                @case('tenant.staff.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Staff can manage rooms and bookings; only owners/admins can change branding, domains, and staff.') }}</p>
                    @if($isAdmin)
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ tenant_url('settings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Resort settings') }}</a>
                        </div>
                    @endif
                    @break
                @case('tenant.domains.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Point DNS for custom hostnames to this app’s server. Set one primary domain for links and emails.') }}</p>
                    @break
                @case('tenant.payment.portal')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Renew to extend your subscription; upgrades send a request to platform admin with payment details.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('settings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Plan & dates (settings)') }}</a>
                    </div>
                    @break
                @case('tenant.settings.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Check for tenant-safe database updates after platform releases. App name controls the tab title and staff header.') }}</p>
                    @break
                @case('tenant.activity.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Audit trail for this resort. Use it to trace who changed bookings, rooms, or branding.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('bookings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Bookings') }}</a>
                    </div>
                    @break
                @case('tenant.profile.edit')
                    <p class="mt-1 text-sm text-gray-700">{{ __('Keep your email current for password resets. Use a strong password for staff access.') }}</p>
                    @break
                @case('tenant.users.index')
                    <p class="mt-1 text-sm text-gray-700">{{ __('These accounts are separate from staff: they sign in under “Guest login” to book and manage their own reservations.') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ tenant_url('/') }}#rooms" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Public landing') }} ↗</a>
                        @if($isAdmin && tenant_rbac_ready())
                            <a href="{{ tenant_url('rbac') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Access control') }}</a>
                        @endif
                    </div>
                    @break
                @default
                    @if(str_starts_with($rn, 'tenant.rooms.'))
                        <p class="mt-1 text-sm text-gray-700">{{ __('After saving, guests see updates on the book page. Images help conversion.') }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ tenant_url('rooms') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('All rooms') }}</a>
                        </div>
                    @elseif(str_starts_with($rn, 'tenant.staff.'))
                        <p class="mt-1 text-sm text-gray-700">{{ __('Owners have full access; staff cannot manage other staff accounts or billing.') }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ tenant_url('staff') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Staff list') }}</a>
                        </div>
                    @else
                        <p class="mt-1 text-sm text-gray-700">{{ __('Tip: collapse the sidebar (desktop) for more workspace. Search in the header jumps across staff pages.') }}</p>
                    @endif
            @endswitch
        </div>
    </div>
</div>
