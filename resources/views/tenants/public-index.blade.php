@extends('layouts.public')

@section('title', 'Browse Resorts · DJs Resort')

@section('body')
<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('landing') }}" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-sky-400 via-cyan-400 to-emerald-400 flex items-center justify-center shadow-lg shadow-sky-500/40">
                    <span class="text-slate-950 font-bold text-lg">DJ</span>
                </div>
                <span class="font-display text-sm font-semibold text-slate-900">DJs Resort</span>
            </a>
            <a href="{{ route('landing') }}" class="text-sm text-slate-500 hover:text-slate-900">← Back to home</a>
        </div>
    </div>
</header>

<section class="min-h-screen border-t border-slate-200/60 bg-white/70 py-12 backdrop-blur-sm">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h1 class="font-display mb-2 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Browse resorts</h1>
        <p class="mb-8 text-slate-600">Click a resort to visit their site and book.</p>

        @if($tenants->isEmpty())
            <p class="text-slate-500">No resorts yet. <a href="{{ route('tenant.select.register') }}" class="text-sky-600 hover:underline">Be the first to register</a>.</p>
        @else
            <ul class="space-y-3">
                @foreach($tenants as $tenant)
                    @php
                        $visit = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->first();
                    @endphp
                    <li>
                        @if($visit)
                            <a href="{{ absolute_url_for_tenant_host($visit->domain, '/') }}"
                               class="block rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-200/60 transition hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-lg">
                                <span class="font-display font-semibold text-slate-900">{{ $tenant->tenant_name }}</span>
                                @if($tenant->plan)
                                    <span class="ml-2 text-xs text-slate-500">({{ $tenant->plan->name }})</span>
                                @endif
                                <span class="block text-sm text-slate-500 mt-0.5">{{ $visit->domain }} · Visit →</span>
                            </a>
                        @else
                            <div class="block rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-500">
                                <span class="font-semibold text-slate-700">{{ $tenant->tenant_name }}</span>
                                <span class="block text-sm mt-0.5">No domain mapped yet</span>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endsection
