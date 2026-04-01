<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Unavailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-6 min-w-0">
        <section class="w-full min-w-0 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 text-teal-800">
                !
            </div>

            <h1 class="text-2xl font-semibold text-slate-900">Tenant temporarily unavailable</h1>
            <p class="mt-3 text-sm text-slate-600">
                <span class="font-medium">{{ $tenant->tenant_name }}</span>
                is currently inactive, so this tenant portal cannot be accessed right now.
            </p>

            <div class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">
                Host: {{ $host }}
            </div>

            <p class="mt-5 text-sm text-slate-600">
                If you are the resort owner, contact platform support or your super admin to reactivate this tenant.
            </p>

            <div class="mt-6">
                <a href="{{ config('app.url') }}" class="inline-flex items-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-800">
                    Back to main website
                </a>
            </div>
        </section>
    </main>
</body>
</html>

