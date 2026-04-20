@php
    $tenantCtx = request()->attributes->get('tenant');
    $tenantLabel = $tenantCtx instanceof \App\Models\Tenant ? $tenantCtx->appDisplayName() : config('app.name', 'Resort');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? __('Access restricted') }} — {{ $tenantLabel }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-lg items-center justify-center p-6 min-w-0">
        <section class="w-full min-w-0 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm" role="alert">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-800">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9-9a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">{{ $title ?? __('Access restricted') }}</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $message }}</p>

            <p class="mt-4 text-xs text-slate-500">
                {{ __('Signed in as :name', ['name' => auth('tenant')->user()?->name ?? __('Staff')]) }}
                @if($tenantLabel)
                    <span class="text-slate-400">·</span> {{ $tenantLabel }}
                @endif
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ tenant_url('/profile') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                    {{ __('Account profile') }}
                </a>
                <form method="POST" action="{{ tenant_url('/logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto">
                        {{ __('Log out') }}
                    </button>
                </form>
            </div>

            <p class="mt-6 text-xs text-slate-500">
                {{ __('Need this access? Ask the resort owner to update your staff role or permission set under Staff or Access control.') }}
            </p>
        </section>
    </main>
</body>
</html>
