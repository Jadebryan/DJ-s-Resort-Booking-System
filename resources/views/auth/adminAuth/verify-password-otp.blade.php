@php
    $appName = config('app.name', 'DJs Resort');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Verify code') }} · {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden font-sans text-slate-900 antialiased">
    <x-auth-backdrop>
        <div class="w-full max-w-md min-w-0">
            <div class="mb-6 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-600">{{ __('Superadmin') }}</p>
                <h1 class="mt-1 text-lg font-semibold text-slate-900 sm:text-xl">{{ __('Enter the 6-digit code') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ __('We sent a code to :email.', ['email' => $email]) }}</p>
            </div>

            <div class="w-full overflow-hidden rounded-3xl border border-slate-200/80 bg-white px-8 py-9 shadow-xl sm:px-10 sm:py-10">
                @if (session('status'))
                    <p class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm text-emerald-800" role="status">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.otp.verify') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="otp" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Verification code') }}</label>
                        <input
                            id="otp"
                            type="text"
                            name="otp"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            required
                            autofocus
                            placeholder="000000"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-xl font-semibold tracking-[0.35em] text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                    >
                        {{ __('Verify and continue') }}
                    </button>
                    <div class="space-y-2 text-center text-sm">
                        <form method="POST" action="{{ route('admin.password.otp.resend') }}" class="inline">
                            @csrf
                            <button type="submit" class="font-medium text-sky-700 hover:text-sky-900">{{ __('Resend code') }}</button>
                        </form>
                        <p>
                            <a href="{{ route('admin.password.request') }}" class="font-medium text-slate-600 hover:text-slate-900">{{ __('Start over') }}</a>
                            <span class="text-slate-400"> · </span>
                            <a href="{{ route('admin.login') }}" class="font-medium text-slate-600 hover:text-slate-900">{{ __('Sign in') }}</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </x-auth-backdrop>
    @include('components.toast-container')
</body>
</html>
