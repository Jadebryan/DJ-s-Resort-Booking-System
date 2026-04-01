<x-mail::message>
{{ __('Hello :name,', ['name' => $registrationRequest->admin_name ?: __('there')]) }}

# {{ __('Update on your resort application') }}

{{ __('Thank you for your interest in :platform. After careful review, we are unable to approve the application for **:resort** at this time.', ['platform' => config('app.name'), 'resort' => $registrationRequest->tenant_name]) }}

@if($registrationRequest->rejection_reason)
<x-mail::panel>
**{{ __('Message from our team') }}**

{{ $registrationRequest->rejection_reason }}
</x-mail::panel>
@endif

{{ __('We understand this may be disappointing. If you believe this decision was made in error, or if you would like to discuss requirements for a future application, please reply to this email or contact our support team using the details on our website.') }}

<p class="sub">
{{ __('We wish you the best with your hospitality plans.') }}
</p>

---

{{ __('Kind regards,') }}<br>
**{{ config('app.name') }}**
</x-mail::message>
