@php
    $appName = config('app.name', 'DJs Resort');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Forgot password') }} · {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden font-sans text-slate-900 antialiased">
    <x-auth-backdrop>
        <div class="w-full max-w-md min-w-0">
            <div class="mb-6 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-600">{{ __('Superadmin') }}</p>
                <h1 class="mt-1 text-lg font-semibold text-slate-900 sm:text-xl">{{ __('Reset password') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ __('Enter your account email. We’ll send a 6-digit code.') }}</p>
            </div>

            <div class="w-full overflow-hidden rounded-3xl border border-slate-200/80 bg-white px-8 py-9 shadow-xl sm:px-10 sm:py-10">
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                    >
                        {{ __('Send code') }}
                    </button>
                    <p class="text-center">
                        <a href="{{ route('admin.login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('Back to sign in') }}</a>
                    </p>
                </form>
            </div>
        </div>
    </x-auth-backdrop>
    @include('components.toast-container')
</body>
</html>
