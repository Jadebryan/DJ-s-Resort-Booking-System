<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'DJs Resort'))</title>
    @yield('meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Ensure Alpine `x-cloak` works even when Vite CSS isn't loaded. */
        [x-cloak] { display: none !important; }
    </style>

    @php
        // If Vite is not running hot and no build assets exist (common in local demos where only `php artisan serve` runs),
        // Alpine-powered UI like the landing carousel won't work. Provide a minimal CDN fallback for Alpine.
        $viteHot = class_exists(\Illuminate\Support\Facades\Vite::class) && \Illuminate\Support\Facades\Vite::isRunningHot();
        $viteBuilt = file_exists(public_path('build/manifest.json'));
        $needsAlpineFallback = ! $viteHot && ! $viteBuilt;
    @endphp
    @if($needsAlpineFallback)
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif

    <script>
        // Runtime safety net: if Vite "hot" file exists but the dev server isn't running,
        // the module bundle won't load and Alpine won't initialize. Ensure Alpine exists anyway.
        (function () {
            function ensureAlpine() {
                if (window.Alpine) return;
                if (document.querySelector('script[data-alpine-fallback]')) return;
                var s = document.createElement('script');
                s.defer = true;
                s.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
                s.setAttribute('data-alpine-fallback', '1');
                document.head.appendChild(s);
            }

            // Try after the page scripts had a chance to load.
            window.addEventListener('load', function () {
                setTimeout(ensureAlpine, 50);
            });
        })();
    </script>

    {{-- Booking now requires login; no global modal fallback needed. --}}

    @stack('styles')
    @livewireStyles
</head>
<body class="overflow-x-hidden scroll-smooth font-landing bg-gradient-to-b from-slate-50 via-slate-50 to-slate-100 text-slate-900 antialiased">
    @include('components.toast-container')
    @yield('body')

    @livewireScripts
</body>
</html>

