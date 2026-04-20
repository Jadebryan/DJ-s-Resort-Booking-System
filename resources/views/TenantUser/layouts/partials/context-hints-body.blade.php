@php
    $rn = request()->route()?->getName() ?? '';
@endphp
@switch($rn)
    @case('tenant.user.dashboard')
        <p class="mt-1 text-sm text-gray-700">{{ __('Browse available rooms and book dates. Pending bookings may need payment proof before the resort confirms.') }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ tenant_url('/') }}#rooms" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Browse & book') }}</a>
            <a href="{{ tenant_url('user/bookings') }}" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('My bookings') }}</a>
        </div>
        @break
    @case('tenant.user.bookings.index')
        <p class="mt-1 text-sm text-gray-700">{{ __('Upload payment proof when asked; you’ll get notifications when status changes. Cancelled stays no longer block the room.') }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ tenant_url('/') }}#rooms" class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 shadow-sm ring-1 ring-teal-200 hover:bg-teal-50">{{ __('Book another stay') }}</a>
        </div>
        @break
    @case('tenant.user.profile.edit')
        <p class="mt-1 text-sm text-gray-700">{{ __('Your email is used for booking updates. Change password if you sign in from shared devices.') }}</p>
        @break
    @default
        <p class="mt-1 text-sm text-gray-700">{{ __('Need a new reservation? Open Browse & book from the dashboard.') }}</p>
@endswitch
