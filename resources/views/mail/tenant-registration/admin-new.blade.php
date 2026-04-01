<x-mail::message>
# {{ __('New resort signup') }}

**{{ __('Resort') }}:** {{ $registrationRequest->tenant_name }}  
**{{ __('Hostname') }}:** {{ $registrationRequest->primary_domain }}  
**{{ __('Admin') }}:** {{ $registrationRequest->admin_name }} ({{ $registrationRequest->admin_email }})  
**{{ __('Plan') }}:** {{ $registrationRequest->plan?->name ?? '—' }}

@if($registrationRequest->payment_provider)
**{{ __('Payment method') }}:** {{ $registrationRequest->payment_provider }}  
@if($registrationRequest->payment_reference)
**{{ __('Reference') }}:** {{ $registrationRequest->payment_reference }}
@endif
@endif

<x-mail::button :url="route('admin.tenant-registrations.index')">
{{ __('Review in admin') }}
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
