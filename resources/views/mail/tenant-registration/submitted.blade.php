<x-mail::message>
{{ __('Hello :name,', ['name' => $registrationRequest->admin_name ?: __('there')]) }}

# {{ __('We received your resort application') }}

{{ __('Thank you for registering with :platform. This message confirms that we have safely received your application and payment details for **:resort**. No further action is required from you at this step.', ['platform' => config('app.name'), 'resort' => $registrationRequest->tenant_name]) }}

<x-mail::panel>
**{{ __('Application summary') }}**  

**{{ __('Resort name') }}:** {{ $registrationRequest->tenant_name }}  
**{{ __('Requested hostname') }}:** {{ $registrationRequest->primary_domain }}  
**{{ __('Plan') }}:** {{ $registrationRequest->plan?->name ?? '—' }}  
**{{ __('Administrator email') }}:** {{ $registrationRequest->admin_email }}
</x-mail::panel>

## {{ __('What happens next') }}

@foreach([
    __('Our team will verify your information and payment against our records.'),
    __('When everything is confirmed, you will receive a **separate email** with your staff sign-in link and activation details.'),
    __('If we need anything else from you, we will contact you at the email address above.'),
] as $item)
• {{ $item }}  
@endforeach

<p class="sub">
{{ __('If you did not submit this application, you can ignore this email or contact us so we can investigate.') }}
</p>

---

{{ __('We appreciate your interest in :platform.', ['platform' => config('app.name')]) }}

{{ __('Kind regards,') }}<br>
**{{ config('app.name') }}**
</x-mail::message>
