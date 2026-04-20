@extends('layouts.public')

@section('title', 'DJs Resort · Effortless Resort Booking Platform')

@section('body')
<!-- NAVBAR — floating translucent bar -->
<div class="sticky top-0 z-40 pt-3 sm:pt-4 px-4 sm:px-6 lg:px-8 pointer-events-none">
    <header class="pointer-events-auto max-w-7xl mx-auto rounded-2xl border border-white/50 bg-white/40 backdrop-blur-xl shadow-lg shadow-slate-900/[0.07] ring-1 ring-slate-900/[0.04]">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-sky-400 via-cyan-400 to-emerald-400 flex items-center justify-center shadow-lg shadow-sky-500/40">
                    <span class="text-slate-950 font-bold text-lg">DJ</span>
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="font-display text-sm font-semibold tracking-tight text-slate-900">DJs Resort</span>
                    <span class="text-[11px] text-slate-500 tracking-tight">Multi‑tenant booking SaaS</span>
                </div>
            </div>

            <div class="hidden md:flex items-center gap-8 text-sm">
                <a href="#features" class="text-slate-500 hover:text-slate-900 transition">Features</a>
                <a href="#how-it-works" class="text-slate-500 hover:text-slate-900 transition">How it works</a>
                <a href="#pricing" class="text-slate-500 hover:text-slate-900 transition">Pricing</a>
            </div>

            <div class="flex items-center gap-3">
                <!-- Tenant Login -->
                <a href="{{ route('tenant.select.login') }}"
                   class="hidden sm:inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition">
                    Tenant Login
                </a>
                <!-- Tenant Register -->
                <a href="{{ route('tenant.select.register') }}"
                   class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 px-4 py-1.5 text-xs font-semibold text-white shadow-lg shadow-sky-400/40 hover:brightness-110 transition">
                    Start free as a resort
                </a>
            </div>
            </div>
        </div>
    </header>
</div>

<!-- HERO -->
<section class="relative overflow-hidden bg-gradient-to-b from-slate-50 via-slate-50 to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pb-24 relative">
        <div class="grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] gap-10 lg:gap-14 items-start">
            <!-- Left content -->
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-[11px] text-slate-500 mb-5 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Multi‑tenant SaaS for resorts
                    <span class="text-slate-400">·</span>
                    <span class="text-slate-600">Admin · Tenant · Guest</span>
                </div>

                <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl xl:text-[3.25rem] xl:leading-[1.08]">
                    Run every resort
                    <span class="mt-1 block bg-gradient-to-r from-cyan-500 via-sky-500 to-emerald-500 bg-clip-text font-medium text-transparent">
                        from one serene dashboard.
                    </span>
                </h1>

                <p class="mt-5 text-sm sm:text-base text-slate-600 max-w-xl">
                    DJs Resort is a fully hosted booking platform for independent resorts. 
                    Onboard new properties in minutes, manage availability, and let guests book 
                    online with zero spreadsheets.
                </p>

                <!-- Stats -->
                <div class="mt-6 flex flex-wrap gap-5 text-xs text-slate-500">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">Multi‑tenant ready</span>
                        <span class="text-slate-500">Each resort gets its own space</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">Role‑based access</span>
                        <span class="text-slate-500">Admin · Tenant · Regular users</span>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('tenant.select.register') }}"
                       class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 px-5 py-2.5 text-sm font-semibold text-white shadow-xl shadow-sky-400/40 hover:brightness-110 transition">
                        Get started as a resort
                    </a>
                    <a href="{{ route('tenants.index') }}"
                       class="inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition">
                        Browse demo tenants
                    </a>
                </div>

                <p class="mt-3 text-[11px] text-slate-500">
                    No credit card required · Built with Laravel & MySQL · Optimized for resort workflows
                </p>
            </div>

            <!-- Right visual -->
            <div class="relative">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-slate-900">
                    <img src="{{ asset('images/background.jpg') }}"
                         alt="Resort lagoon"
                         class="h-72 w-full object-cover">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/40 to-transparent px-6 pb-5 pt-16 flex flex-col justify-end">
                        <div class="text-xs text-slate-200 flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-emerald-300 border border-emerald-400/40">
                                Azure Haven Resort
                            </span>
                            <span class="text-slate-300">Bora Bora · French Polynesia</span>
                        </div>
                        <p class="text-sm text-slate-100">
                            Ocean‑view villas, infinity pools, and private decks designed for the most relaxed guests.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-900">Dates</span>
                            <span class="text-[10px] text-emerald-500">Live</span>
                        </div>
                        <p>Choose check‑in & check‑out for any resort tenant.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm">
                        <span class="font-semibold text-slate-900 block mb-1">Guests</span>
                        <p>Rooms, guests, and preferences in a single, simple flow.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm">
                        <span class="font-semibold text-slate-900 block mb-1">Tenants</span>
                        <p>Switch between resorts instantly with tenant routing.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section id="features" class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                Built for resort owners, guests, and teams.
            </h2>
            <p class="mt-3 text-sm sm:text-base text-slate-500">
                DJs Resort ships with opinionated flows for every role in your ecosystem. 
                Out of the box: admin back office, resort tenant spaces, and guest‑facing booking.
            </p>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <!-- Admin -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="inline-flex items-center justify-center rounded-full bg-cyan-500/10 text-cyan-300 text-xs px-2 py-0.5 border border-cyan-500/40">
                    Admin
                </div>
                <h3 class="mt-3 text-base font-semibold text-slate-900">Platform owner dashboard</h3>
                <p class="mt-2 text-xs text-slate-500">
                    Onboard new resorts, manage tenants, and keep a bird’s‑eye view of occupancy across properties.
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-slate-600">
                    <li>• Create & manage resort tenants</li>
                    <li>• View platform‑wide bookings & metrics</li>
                    <li>• Control access for admins and staff</li>
                </ul>
            </div>

            <!-- Tenant -->
            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 ring-1 ring-sky-200 shadow-xl shadow-sky-100/80">
                <div class="inline-flex items-center justify-center rounded-full bg-sky-500/10 text-sky-300 text-xs px-2 py-0.5 border border-sky-500/40">
                    Tenant
                </div>
                <h3 class="mt-3 text-base font-semibold text-slate-900">Resort‑level control center</h3>
                <p class="mt-2 text-xs text-slate-600">
                    Every resort gets its own login, branding, and tools for managing inventory, pricing, and guests.
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-slate-700">
                    <li>• Configure cottages, rooms, amenities, and packages</li>
                    <li>• Track live availability and upcoming stays</li>
                    <li>• Invite staff with role‑based permissions</li>
                </ul>
            </div>

            <!-- Guests -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="inline-flex items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300 text-xs px-2 py-0.5 border border-emerald-500/40">
                    Guests
                </div>
                <h3 class="mt-3 text-base font-semibold text-slate-900">Delightful guest experience</h3>
                <p class="mt-2 text-xs text-slate-500">
                    Make it effortless for guests to explore resorts, choose dates, and confirm bookings online.
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-slate-600">
                    <li>• Clean tenant‑specific landing pages</li>
                    <li>• Transparent pricing & availability</li>
                    <li>• Booking history and confirmation emails</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how-it-works" class="py-16 sm:py-20 bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                From signup to first booking in three steps.
            </h2>
            <p class="mt-3 text-sm sm:text-base text-slate-600">
                The flow is already wired into your Laravel app. You bring the resorts; DJs Resort handles the rest.
            </p>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3 text-xs text-slate-600">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-col gap-2">
                <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-[11px] font-semibold text-white">
                    1
                </div>
                <h3 class="text-sm font-semibold text-slate-900">Create a tenant</h3>
                <p class="text-slate-600">
                    Register a resort, choose its hostname, and let the platform provision its database and dashboard.
                </p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 flex flex-col gap-2">
                <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-sky-600 text-[11px] font-semibold text-white">
                    2
                </div>
                <h3 class="text-sm font-semibold text-slate-900">Configure inventory</h3>
                <p class="text-slate-600">
                    Inside the tenant dashboard, set up rooms, cottages, rates, and seasonal rules tailored to your resort.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-col gap-2">
                <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-[11px] font-semibold text-white">
                    3
                </div>
                <h3 class="text-sm font-semibold text-slate-900">Share the link</h3>
                <p class="text-slate-600">
                    Each resort is served on its <span class="text-slate-200">own custom domain</span> (no shared path prefix).
                    The platform routes them automatically.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- PRICING / CTA -->
<section id="pricing" class="py-16 sm:py-20 bg-white border-t border-slate-200/80">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
            Start with a single resort. Scale to many.
        </h2>
        <p class="mt-3 text-sm sm:text-base text-slate-600 max-w-2xl mx-auto">
            Use the built‑in tenant management to experiment with a few properties and grow into a full resort network.
        </p>

        <div class="mt-8 inline-flex flex-col sm:flex-row gap-5 justify-center items-center">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 px-8 py-6 text-left max-w-xs w-full">
                <h3 class="text-sm font-semibold text-slate-900">For resort owners</h3>
                <p class="mt-2 text-xs text-slate-600">Use the tenant flow that is already wired into this application.</p>
                <ul class="mt-3 space-y-1.5 text-xs text-slate-600">
                    <li>• Tenant login & registration pages</li>
                    <li>• Per‑tenant dashboards & profiles</li>
                    <li>• Separate tenant and user auth guards</li>
                </ul>
            </div>

            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-8 py-6 text-left max-w-xs w-full shadow-lg shadow-emerald-100/60">
                <h3 class="text-sm font-semibold text-slate-900">Ready in your codebase</h3>
                <p class="mt-2 text-xs text-slate-600">
                    Everything you see here is a frontend for the Laravel multi‑tenant logic you already have.
                </p>
                <ul class="mt-3 space-y-1.5 text-xs text-slate-700">
                    <li>• Powered by Laravel, MySQL, Tailwind CSS</li>
                    <li>• Domain-based tenant routing</li>
                    <li>• Easy to extend with your own branding</li>
                </ul>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('tenant.select.register') }}"
               class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 px-6 py-2.5 text-sm font-semibold text-white shadow-xl shadow-sky-400/40 hover:brightness-110 transition">
                Create a resort tenant
            </a>
            <a href="{{ route('tenant.select.login') }}"
               class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-2 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition">
                Already have a resort? Log in
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="border-t border-slate-200 bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
        <p>© {{ now()->year }} DJs Resort System. All rights reserved.</p>
        <p class="text-slate-500">
            Built with <span class="text-slate-700">Laravel</span> · <span class="text-slate-700">MySQL</span> · <span class="text-slate-700">Tailwind CSS</span>
        </p>
    </div>
</footer>

@endsection
