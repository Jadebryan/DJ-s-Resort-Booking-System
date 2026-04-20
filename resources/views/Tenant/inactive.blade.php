<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Resort unavailable') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-6 min-w-0">
        <section class="w-full min-w-0 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 text-teal-800">
                !
            </div>

            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Resort temporarily unavailable') }}</h1>
            <p class="mt-3 text-sm text-slate-600">
                <span class="font-medium">{{ $tenant->tenant_name }}</span>
                {{ __('cannot be reached on the web right now (the resort may be suspended or your subscription may have ended).') }}
            </p>

            <div class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">
                {{ __('Host:') }} {{ $host }}
            </div>

            @php
                $subscriptionExpired = $tenant->subscriptionIsExpired();
            @endphp

            @if($subscriptionExpired)
                <p class="mt-5 text-sm text-slate-600">
                    {{ __('Your subscription end date has passed. You can submit a renewal request with payment details—platform admin will review it and label it as a renewal when it matches your current plan.') }}
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ tenant_url('payment') }}#subscription-renew"
                       class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                        {{ __('Renew subscription') }}
                    </a>
                    <a href="{{ tenant_url('login') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        {{ __('Staff sign in') }}
                    </a>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    {{ __('Sign in with an account that has billing access, then complete the renewal form on the payment page.') }}
                </p>
            @else
                <p class="mt-5 text-sm text-slate-600">
                    {{ __('If you are the resort owner, contact platform support or your super admin to reactivate this resort.') }}
                </p>
                <div class="mt-6">
                    <a href="{{ config('app.url') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-800">
                        {{ __('Back to main website') }}
                    </a>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
