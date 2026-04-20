<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Settings') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Resort info, app name, database, and system updates.') }}</p>
        </div>
    </x-slot>

    @php
        $usageMb = $dbInfo['usage_bytes'] !== null ? round($dbInfo['usage_bytes'] / 1048576, 2) : null;
        $hostDisplay = $dbInfo['host'] ? $dbInfo['host'] . ($dbInfo['port'] ? ':' . $dbInfo['port'] : '') : '—';
        $suFeedbackUpdateAvailable = __('A newer platform version is available. Your resort is on version [[CUR]]. The latest is [[LAT]]. Tenant admins can use “Apply update” to run database migrations for this resort.');
        $suFeedbackUpToDate = __('You are on the latest version ([[VER]]). No database migration is required right now.');
        $latestRelease = $latestReleaseDetails ?? null;
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-6 text-left">
            <section class="rounded-xl border border-slate-200 bg-slate-50/80 p-6 text-left shadow-sm" id="system-updates-panel" data-check-url="{{ tenant_url('api/tenant/check-update') }}" data-apply-url="{{ tenant_url('api/tenant/apply-update') }}">
                <div class="flex flex-col gap-4 items-start text-left">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('System updates') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Schema migrations for your resort database. Code is updated on our servers; you apply tenant-safe migrations when ready.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="btn-check-updates" class="inline-flex min-h-[2.25rem] items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 disabled:opacity-60">
                            <span id="btn-check-updates-label">{{ __('Check for updates') }}</span>
                        </button>
                        @if(auth('tenant')->user()->role === 'admin')
                            <button type="button" id="btn-apply-update" class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                                {{ __('Apply update') }}
                            </button>
                        @endif
                    </div>
                </div>
                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2 text-left">
                        <dt class="text-xs font-medium text-slate-500">{{ __('Your version') }}</dt>
                        <dd class="mt-0.5 font-mono text-slate-900" id="su-current">{{ $tenantSchemaVersion ?? '1.0.0' }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2 text-left">
                        <dt class="text-xs font-medium text-slate-500">{{ __('Latest version') }}</dt>
                        <dd class="mt-0.5 font-mono text-slate-900" id="su-latest">{{ $systemLatestVersion ?? '1.0.0' }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2 text-left">
                        <dt class="text-xs font-medium text-slate-500">{{ __('Status') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900" id="su-status">
                            @if(version_compare($tenantSchemaVersion ?? '1.0.0', $systemLatestVersion ?? '1.0.0', '<'))
                                <span class="text-amber-700">{{ __('Update available') }}</span>
                            @else
                                <span class="text-teal-700">{{ __('Up to date') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                <div id="su-feedback" class="mt-4 hidden rounded-lg border px-4 py-3 text-left text-sm font-medium leading-relaxed" role="status" aria-live="polite"></div>
                <p class="mt-2 hidden text-left text-xs text-red-600" id="su-error" role="alert"></p>
                <p class="mt-2 hidden text-left text-xs text-slate-600" id="su-notice" role="status"></p>

                @if(is_array($latestRelease) && ($latestRelease['source'] ?? '') === 'github' && ((string) ($latestRelease['body'] ?? '') !== '' || !empty($latestRelease['assets'] ?? [])))
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @if((string) ($latestRelease['body'] ?? '') !== '')
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Release notes') }}</p>
                                        <p class="mt-0.5 text-sm font-semibold text-slate-900 truncate">
                                            {{ $latestRelease['release_name'] ?? ($latestRelease['tag_name'] ?? __('Latest')) }}
                                        </p>
                                    </div>
                                    @if(!empty($latestRelease['html_url']))
                                        <a href="{{ $latestRelease['html_url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="shrink-0 inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                            {{ __('View') }}
                                        </a>
                                    @endif
                                </div>
                                <div class="mt-2 text-xs leading-relaxed text-slate-700 whitespace-pre-wrap">{{ $latestRelease['body'] }}</div>
                            </div>
                        @endif

                        @if(!empty($latestRelease['assets'] ?? []))
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Downloads') }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ __('Release files') }}</p>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach(($latestRelease['assets'] ?? []) as $asset)
                                        <li class="flex items-center justify-between gap-3 rounded-md border border-slate-100 bg-slate-50/60 px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-semibold text-slate-800">{{ $asset['name'] ?? __('Asset') }}</p>
                                                <p class="mt-0.5 text-[11px] text-slate-500">
                                                    @php($bytes = (int) ($asset['size'] ?? 0))
                                                    @if($bytes > 0)
                                                        {{ round($bytes / 1048576, 2) }} MB
                                                    @else
                                                        —
                                                    @endif
                                                    @if(!empty($asset['content_type']))
                                                        · {{ $asset['content_type'] }}
                                                    @endif
                                                </p>
                                            </div>
                                            @if(!empty($asset['download_url']))
                                                <a href="{{ $asset['download_url'] }}" target="_blank" rel="noopener noreferrer"
                                                   class="shrink-0 inline-flex items-center rounded-md bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-teal-700">
                                                    {{ __('Download') }}
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-[11px] text-slate-500">{{ __('Tip: For private repos, you may need to be signed in to GitHub to download assets in the browser.') }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <script>
            (function () {
                var panel = document.getElementById('system-updates-panel');
                if (!panel) return;
                var checkUrl = panel.dataset.checkUrl;
                var applyUrl = panel.dataset.applyUrl;
                var btnCheck = document.getElementById('btn-check-updates');
                var btnApply = document.getElementById('btn-apply-update');
                var elCurrent = document.getElementById('su-current');
                var elLatest = document.getElementById('su-latest');
                var elStatus = document.getElementById('su-status');
                var elError = document.getElementById('su-error');
                var elNotice = document.getElementById('su-notice');
                var elFeedback = document.getElementById('su-feedback');
                var btnCheckLabel = document.getElementById('btn-check-updates-label');
                var tokenEl = document.querySelector('meta[name="csrf-token"]');
                var token = tokenEl ? tokenEl.getAttribute('content') : '';
                var labelCheckDefault = btnCheckLabel ? btnCheckLabel.textContent : '';

                function showError(msg) {
                    elError.textContent = msg || '';
                    elError.classList.toggle('hidden', !msg);
                }
                function showNotice(msg) {
                    elNotice.textContent = msg || '';
                    elNotice.classList.toggle('hidden', !msg);
                }

                function hideFeedback() {
                    if (!elFeedback) return;
                    elFeedback.textContent = '';
                    elFeedback.className = 'mt-4 hidden rounded-lg border px-4 py-3 text-left text-sm font-medium leading-relaxed';
                }

                function showCheckFeedback(data) {
                    if (!elFeedback) return;
                    hideFeedback();
                    elFeedback.classList.remove('hidden');
                    if (data.update_available) {
                        elFeedback.classList.add('border-amber-300', 'bg-amber-50', 'text-amber-950');
                        elFeedback.textContent = @json($suFeedbackUpdateAvailable).replace('[[CUR]]', data.current_version).replace('[[LAT]]', data.latest_version);
                    } else {
                        elFeedback.classList.add('border-teal-200', 'bg-teal-50', 'text-teal-950');
                        elFeedback.textContent = @json($suFeedbackUpToDate).replace('[[VER]]', data.current_version);
                    }
                }

                function setApplyEnabled(on) {
                    if (btnApply) btnApply.disabled = !on;
                }

                function applyStatusFromPayload(data) {
                    elCurrent.textContent = data.current_version;
                    elLatest.textContent = data.latest_version;
                    if (data.update_available) {
                        elStatus.innerHTML = '<span class="text-amber-700">{{ __('Update available') }}</span>';
                        setApplyEnabled(true);
                    } else {
                        elStatus.innerHTML = '<span class="text-teal-700">{{ __('Up to date') }}</span>';
                        setApplyEnabled(false);
                    }
                }

                if (btnCheck) {
                    btnCheck.addEventListener('click', function () {
                        showError('');
                        hideFeedback();
                        showNotice('');
                        if (btnCheckLabel) btnCheckLabel.textContent = @json(__('Checking…'));
                        btnCheck.disabled = true;
                        fetch(checkUrl, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        }).then(function (r) {
                            return r.json().then(function (j) {
                                if (!r.ok) throw new Error(j.message || r.statusText);
                                return j;
                            });
                        }).then(function (data) {
                            applyStatusFromPayload(data);
                            showCheckFeedback(data);
                        }).catch(function (e) {
                            hideFeedback();
                            showError(e.message || @json(__('Could not check for updates.')));
                        }).finally(function () {
                            btnCheck.disabled = false;
                            if (btnCheckLabel) btnCheckLabel.textContent = labelCheckDefault;
                        });
                    });
                }

                if (btnApply) {
                    btnApply.addEventListener('click', function () {
                        showError('');
                        showNotice('');
                        btnApply.disabled = true;
                        fetch(applyUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: '{}'
                        }).then(function (r) {
                            return r.json().then(function (j) {
                                if (r.status === 403) throw new Error(j.message || 'Forbidden');
                                if (r.status >= 400 && r.status !== 202) throw new Error(j.message || r.statusText);
                                return { status: r.status, body: j };
                            });
                        }).then(function (res) {
                            if (res.status === 202) {
                                hideFeedback();
                                showNotice(res.body.message || @json(__('Update queued. Refresh this page in a moment.')));
                                setApplyEnabled(false);
                                return;
                            }
                            applyStatusFromPayload(res.body);
                            showNotice('');
                            showCheckFeedback(res.body);
                        }).catch(function (e) {
                            showError(e.message || @json(__('Apply update failed.')));
                        }).finally(function () {
                            if (!btnApply) return;
                            if (elCurrent.textContent.trim() !== elLatest.textContent.trim()) {
                                btnApply.disabled = false;
                            }
                        });
                    });
                }

                @if(auth('tenant')->user()->role === 'admin' && version_compare($tenantSchemaVersion ?? '1.0.0', $systemLatestVersion ?? '1.0.0', '<'))
                setApplyEnabled(true);
                @endif
            })();
            </script>

            <div class="rounded-xl border border-gray-200/80 bg-white p-6 text-left shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Resort & subscription') }}</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Resort name') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $tenant->tenant_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Slug') }}</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ $tenant->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Primary domain') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $primaryDomain?->domain ?? __('Not set') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Plan') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $tenant->plan?->name ?? __('None') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Subscription ends') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->timezone(config('app.timezone'))->format('M j, Y') : __('Not set') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Account status') }}</dt>
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tenant->is_active ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $tenant->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-6 text-left shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Application') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('How the platform presents time, language, and version info for this resort.') }}</p>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('App name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-600">
                            {{ __('Shown in the staff dashboard, browser tab, and public site title. Defaults to your resort name if left blank.') }}
                        </dd>
                        @if(auth('tenant')->user()?->role === 'admin')
                            <form method="POST" action="{{ route('tenant.settings.update') }}" class="mt-3 space-y-2">
                                @csrf
                                @method('PATCH')
                                <label for="app_display_name" class="sr-only">{{ __('App name') }}</label>
                                <input id="app_display_name" type="text" name="app_display_name" maxlength="120"
                                       value="{{ old('app_display_name', $tenant->app_display_name ?? '') }}"
                                       placeholder="{{ $tenant->tenant_name }}"
                                       class="w-full max-w-md rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <p class="text-xs text-gray-500">{{ __('Leave empty to use resort name: :name', ['name' => $tenant->tenant_name]) }}</p>
                                <button type="submit" class="rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                                    {{ __('Save app name') }}
                                </button>
                            </form>
                        @else
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $tenant->appDisplayName() }}</p>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Central app URL') }}</dt>
                        <dd class="mt-1 break-all text-sm text-gray-900">{{ $appUrl }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Timezone') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appTimezone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Locale') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appLocale }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Server time (preview)') }}</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ $nowDisplay }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('PHP') }}</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ $phpVersion }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Framework') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">Laravel {{ $laravelVersion }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-6 text-left shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Assigned database') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Your resort data is stored in its own database. Size is an estimate of user tables (MySQL/MariaDB/PostgreSQL) or file size (SQLite).') }}
                </p>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Database name (on record)') }}</dt>
                        <dd class="mt-1 break-all font-mono text-sm font-medium text-gray-900">{{ $tenant->database_name ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Active connection database') }}</dt>
                        <dd class="mt-1 break-all font-mono text-sm text-gray-900">{{ $dbInfo['database'] !== '' ? $dbInfo['database'] : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Driver') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $dbInfo['driver'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Host') }}</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ $hostDisplay }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Approx. usage') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-teal-800">
                            @if($usageMb !== null)
                                {{ number_format($usageMb, 2) }} {{ __('MB') }}
                                @if($dbInfo['usage_bytes'] !== null)
                                    <span class="font-normal text-gray-500">({{ number_format($dbInfo['usage_bytes']) }} {{ __('bytes') }})</span>
                                @endif
                            @else
                                <span class="font-normal text-gray-500">{{ __('Unavailable for this driver or environment') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Tables (approx.)') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $dbInfo['table_count'] !== null ? $dbInfo['table_count'] : '—' }}</dd>
                    </div>
                </dl>
                @if($tenant->database_name && $dbInfo['database'] && $tenant->database_name !== $dbInfo['database'])
                    <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        {{ __('The name on file does not match the active connection. If this persists, contact platform support.') }}
                    </p>
                @endif
            </div>
    </div>
</x-tenant::app-layout>
