@props([
    'containerClass' => 'max-w-2xl',
    'compact' => false,
    'dense' => false,
])
@php
    $authInnerClass = \Illuminate\Support\Arr::toCssClasses([
        'min-h-screen flex items-center justify-center px-3 min-w-0 sm:px-4',
        'py-4 sm:py-5' => $dense && $compact,
        'py-5 sm:py-7' => $compact && ! $dense,
        'py-8 sm:py-10' => ! $compact,
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @php
                $__tg = request()->attributes->get('tenant');
            @endphp
            {{ $__tg instanceof \App\Models\Tenant ? $__tg->appDisplayName() : config('app.name', 'Laravel') }}
        </title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden font-sans text-slate-900 antialiased">
        <x-auth-backdrop :inner-class="$authInnerClass">
            <div class="w-full min-w-0 {{ $containerClass }}">
                <div @class([
                    'flex justify-center',
                    'mb-2' => $dense && $compact,
                    'mb-3' => $compact && ! $dense,
                    'mb-6' => ! $compact,
                ])>
                    <a href="/">
                        <div @class(['flex items-center', $dense && $compact ? 'gap-1.5' : 'gap-2'])>
                            <div @class([
                                'shrink-0 rounded-lg bg-gradient-to-tr from-sky-400 via-cyan-400 to-emerald-400 flex items-center justify-center shadow-md shadow-sky-400/30',
                                'h-9 w-9' => $dense && $compact,
                                'h-10 w-10 rounded-xl' => $compact && ! $dense,
                                'h-[50px] w-[50px] rounded-xl shadow-lg shadow-sky-400/40' => ! $compact,
                            ])>
                                <span @class([
                                    'text-slate-950 font-bold',
                                    'text-xs' => $dense && $compact,
                                    'text-sm' => $compact && ! $dense,
                                    'text-base' => ! $compact,
                                ])>DJ</span>
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span @class([
                                    'font-semibold tracking-tight text-slate-900',
                                    'text-[11px]' => $dense && $compact,
                                    'text-xs' => $compact && ! $dense,
                                    'text-sm' => ! $compact,
                                ])>DJs Resort</span>
                                <span @class([
                                    'text-slate-500 tracking-tight',
                                    'text-[9px]' => $dense && $compact,
                                    'text-[10px] sm:text-[11px]' => ! ($dense && $compact),
                                ])>Tenant portal</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div @class([
                    'w-full min-w-0 overflow-hidden bg-white shadow-slate-200/80',
                    'rounded-xl px-4 py-4 shadow-md sm:px-5 sm:py-5' => $dense && $compact,
                    'rounded-2xl px-5 py-5 shadow-lg sm:px-6 sm:py-6' => $compact && ! $dense,
                    'rounded-3xl px-10 py-10 shadow-xl sm:px-12 sm:py-12' => ! $compact,
                ])>
                    {{ $slot }}
                </div>
            </div>
        </x-auth-backdrop>
        @include('components.toast-container')
    </body>
</html>
