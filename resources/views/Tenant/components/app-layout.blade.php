@props(['header' => null])

@include('Tenant.layouts.app', ['header' => $header, 'slot' => $slot, 'contentAttributes' => $attributes])
