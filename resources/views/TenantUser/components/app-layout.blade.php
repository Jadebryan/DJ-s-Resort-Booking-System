@props(['header' => null])

@include('TenantUser.layouts.app', ['header' => $header, 'slot' => $slot, 'contentAttributes' => $attributes])
