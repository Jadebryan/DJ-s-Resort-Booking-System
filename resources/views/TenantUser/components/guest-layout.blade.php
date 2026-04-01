@props([
    'containerClass' => 'max-w-2xl',
    'compact' => false,
])
@php
    $authInnerClass = \Illuminate\Support\Arr::toCssClasses([
        'min-h-screen flex items-center justify-center px-4 min-w-0',
        'py-5 sm:py-7' => $compact,
        'py-8 sm:py-10' => ! $compact,
    ]);
    $tenant = current_tenant();
    $siteName = $tenant instanceof \App\Models\Tenant ? $tenant->appDisplayName() : config('app.name', 'Resort');
    $primary = $tenant?->primary_color ?? '#0ea5e9';
    $secondary = $tenant?->secondary_color ?? '#0369a1';
    $logoUrl = ($tenant && $tenant->logo_path) ? asset('storage/' . $tenant->logo_path) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? $siteName }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden font-sans text-slate-900 antialiased">
        <x-auth-backdrop :inner-class="$authInnerClass">
            <div class="w-full min-w-0 {{ $containerClass }}">
                <div @class(['flex justify-center', $compact ? 'mb-3' : 'mb-6'])>
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" @class([
                                'w-auto object-contain',
                                'h-10 max-w-[40px]' => $compact,
                                'h-[50px] max-w-[50px]' => ! $compact,
                            ])>
                            <div class="flex flex-col leading-tight">
                                <span @class([
                                    'font-semibold tracking-tight text-slate-900',
                                    'text-xs' => $compact,
                                    'text-sm' => ! $compact,
                                ])>{{ $siteName }}</span>
                                <span class="text-[10px] text-slate-500 tracking-tight sm:text-[11px]">Guest portal</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <div @class([
                                    'rounded-xl flex items-center justify-center shadow-md text-slate-950 font-bold',
                                    'h-10 w-10 text-sm' => $compact,
                                    'h-[50px] w-[50px] text-base shadow-lg rounded-xl' => ! $compact,
                                ])
                                     style="background: linear-gradient(135deg, {{ $primary }}40, {{ $secondary }}60);">
                                    {{ strtoupper(substr($siteName, 0, 2)) }}
                                </div>
                                <div class="flex flex-col leading-tight">
                                    <span @class([
                                        'font-semibold tracking-tight text-slate-900',
                                        'text-xs' => $compact,
                                        'text-sm' => ! $compact,
                                    ])>{{ $siteName }}</span>
                                    <span class="text-[10px] text-slate-500 tracking-tight sm:text-[11px]">Guest portal</span>
                                </div>
                            </div>
                        @endif
                    </a>
                </div>

                <div @class([
                    'w-full min-w-0 overflow-hidden bg-white shadow-slate-200/80',
                    'rounded-2xl px-5 py-5 shadow-lg sm:px-6 sm:py-6' => $compact,
                    'rounded-3xl px-10 py-10 shadow-xl sm:px-12 sm:py-12' => ! $compact,
                ])>
                    {{ $slot }}
                </div>
            </div>
        </x-auth-backdrop>
        @include('components.toast-container')
    </body>
</html>
