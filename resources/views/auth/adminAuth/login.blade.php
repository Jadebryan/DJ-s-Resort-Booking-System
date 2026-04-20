@php
    $appName = config('app.name', 'DJs Resort');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Superadmin') }} · {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden font-sans text-slate-900 antialiased">
    <x-auth-backdrop>
        <div class="w-full max-w-[360px] min-w-0">
            <div class="mb-3 flex justify-center">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-400 via-cyan-400 to-emerald-400 text-slate-950 shadow-md shadow-sky-400/30">
                        <span class="text-sm font-bold tracking-tight">DJ</span>
                    </div>
                    <div class="flex flex-col leading-tight text-left">
                        <span class="text-sm font-semibold tracking-tight text-slate-900">{{ $appName }}</span>
                        <span class="text-[11px] text-slate-500">Superadmin</span>
                    </div>
                </div>
            </div>

            <div class="w-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white px-5 py-5 shadow-lg shadow-slate-200/60 sm:px-6 sm:py-6">
                <div class="mb-3 text-center">
                    <h1 class="text-base font-semibold tracking-tight text-slate-900 sm:text-lg">Sign in</h1>
                    <p class="mt-1 text-[11px] text-slate-600 sm:text-xs">Tenants, billing, and platform settings.</p>
                </div>

                <form method="POST" action="{{ route('admin.login') }}" class="space-y-3">
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
                            {{ \App\Support\InputHtmlAttributes::email() }}
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                        <div x-data="{ showPassword: false }" class="relative">
                            <input
                                id="password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 pr-12 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                            />
                            <button
                                type="button"
                                x-on:click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-2 my-1 inline-flex items-center rounded-lg px-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                                x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z" />
                                    <circle cx="12" cy="12" r="3.2" />
                                </svg>
                                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M3 3l18 18" />
                                    <path d="M10.6 10.6A3 3 0 0 0 13.4 13.4" />
                                    <path d="M9.9 5.2A9.8 9.8 0 0 1 12 5c6.5 0 9.5 7 9.5 7a17.7 17.7 0 0 1-3.1 4.2" />
                                    <path d="M6.2 6.2A17.8 17.8 0 0 0 2.5 12s3 7 9.5 7a9.7 9.7 0 0 0 4.2-.9" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500"
                                @checked(old('remember'))
                            />
                            <span>{{ __('Remember this device') }}</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                    >
                        {{ __('Continue to dashboard') }}
                    </button>

                    <p class="mt-3 text-center">
                        <a href="{{ route('admin.password.request') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">{{ __('Forgot password?') }}</a>
                    </p>
                </form>

                <p class="mt-5 text-center text-[11px] text-slate-400">
                    {{ __('Restricted access. All sign-ins may be logged.') }}
                </p>
            </div>
        </div>
    </x-auth-backdrop>
    @include('components.toast-container')
</body>
</html>
