<x-mail::message>
{{ __('Hello :name,', ['name' => $registrationRequest->admin_name ?: __('there')]) }}

# {{ __('Your resort application is approved') }}

{{ __('We are pleased to confirm that **:resort** has been reviewed and activated on :platform. Your staff workspace is ready—you can sign in to configure rooms, rates, bookings, and your guest-facing experience.', ['resort' => $tenant->tenant_name, 'platform' => config('app.name')]) }}

<x-mail::panel>
**{{ __('Your account at a glance') }}**  

**{{ __('Resort') }}:** {{ $tenant->tenant_name }}  
@if($tenant->primaryDomain())
**{{ __('Primary domain') }}:** {{ $tenant->primaryDomain()->domain }}  
@endif
@if($tenant->plan)
**{{ __('Plan') }}:** {{ $tenant->plan->name }}  
@endif
**{{ __('Staff sign-in URL') }}:**  
{{ $loginUrl }}
</x-mail::panel>

## {{ __('What to do next') }}

@foreach([
    __('Sign in using the **email address and password** you chose during registration.'),
    __('Complete your resort profile, add rooms, and review branding so guests see a polished booking experience.'),
    __('For security, consider changing your password after your first successful sign-in.'),
] as $item)
• {{ $item }}  
@endforeach

<x-mail::button :url="$loginUrl" color="primary">
{{ __('Sign in to your dashboard') }}
</x-mail::button>

<p class="sub">
{{ __('If the button above does not work, copy and paste the sign-in URL from the summary box into your browser.') }}
</p>

---

{{ __('Thank you for choosing :platform. We are glad to have you with us.', ['platform' => config('app.name')]) }}

{{ __('Kind regards,') }}<br>
**{{ config('app.name') }}**
</x-mail::message>
