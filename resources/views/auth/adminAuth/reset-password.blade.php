@php
    $appName = config('app.name', 'DJs Resort');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('New password') }} · {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden font-sans text-slate-900 antialiased">
    <x-auth-backdrop>
        <div class="w-full max-w-[360px] min-w-0">
            <div class="mb-3 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-600">{{ __('Superadmin') }}</p>
                <h1 class="mt-1 text-base font-semibold text-slate-900 sm:text-lg">{{ __('Choose a new password') }}</h1>
                <p class="mt-1 text-[11px] text-slate-600 sm:text-xs">{{ __('Account: :email', ['email' => $email]) }}</p>
            </div>

            <div class="w-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white px-5 py-5 shadow-lg sm:px-6 sm:py-6">
                <form method="POST" action="{{ route('admin.password.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('New password') }}</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Confirm password') }}</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                    >
                        {{ __('Save password') }}
                    </button>
                </form>
            </div>
        </div>
    </x-auth-backdrop>
    @include('components.toast-container')
</body>
</html>
