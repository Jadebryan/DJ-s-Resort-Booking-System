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

        <!-- Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="overflow-x-hidden text-gray-900 antialiased font-sans">
        <x-auth-backdrop inner-class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 min-w-0 px-4">
            <div>
                <a href="/">
                    <x-tenant-user::application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full min-w-0 sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </x-auth-backdrop>
        @livewireScripts
    </body>
</html>
