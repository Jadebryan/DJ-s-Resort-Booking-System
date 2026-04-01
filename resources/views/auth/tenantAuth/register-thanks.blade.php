@php
    use App\Models\TenantRegistrationRequest;
    $isApproved = $registration->status === TenantRegistrationRequest::STATUS_APPROVED;
    $isRejected = $registration->status === TenantRegistrationRequest::STATUS_REJECTED;
    $isPendingReview = $registration->status === TenantRegistrationRequest::STATUS_PENDING_REVIEW;
    $statusPollUrl = route('tenant.register.submitted.status', $registration->token);
    if ($isApproved) {
        $signInUrl = $signInUrl ?? absolute_url_for_tenant_host($registration->primary_domain, '/login');
    }
@endphp
<x-tenant::guest-layout container-class="max-w-xl" :compact="true" :dense="true">
    @if($isApproved)
        <div class="space-y-3 text-center">
            <div>
                <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-emerald-700">
                    {{ __('Approved') }}
                </p>
                <h1 class="mt-0.5 text-[15px] font-semibold leading-snug text-slate-900">
                    {{ __('Your resort is ready') }}
                </h1>
                <p class="mt-1 text-[10px] leading-relaxed text-slate-600 sm:text-[11px]">
                    {{ __('Sign in on your resort’s domain using the email and password you chose at registration.') }}
                </p>
            </div>

            <div class="rounded-md border border-emerald-200/90 bg-emerald-50/60 p-2.5 text-left text-[10px] text-slate-700 shadow-sm sm:p-3 sm:text-[11px]">
                <dl class="space-y-2">
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Resort') }}</dt>
                        <dd class="mt-0.5 min-w-0 break-words">{{ $registration->tenant_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Sign-in') }}</dt>
                        <dd class="mt-0.5 min-w-0 break-all">
                            <a href="{{ $signInUrl }}" class="font-mono text-[10px] font-medium text-sky-600 underline decoration-sky-300 underline-offset-2 hover:text-sky-800 sm:text-[11px]">
                                {{ $signInUrl }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Host') }}</dt>
                        <dd class="mt-0.5 min-w-0">
                            <a href="{{ $signInUrl }}" class="break-all font-mono text-[10px] font-medium text-sky-600 underline decoration-sky-300 underline-offset-2 hover:text-sky-800 sm:text-[11px]">
                                {{ $registration->primary_domain }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Status') }}</dt>
                        <dd class="mt-0.5">{{ __('Approved') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-3 flex flex-col items-center gap-2">
            <a href="{{ $signInUrl }}"
               class="inline-flex min-h-[2.125rem] w-full max-w-xs items-center justify-center rounded-md bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm transition hover:brightness-110 sm:text-xs">
                {{ __('Open staff sign-in') }}
            </a>
            <a href="{{ route('landing') }}" class="text-[11px] font-medium text-sky-600 hover:text-sky-800 sm:text-xs">{{ __('Back to home') }}</a>
        </div>
    @elseif($isRejected)
        <div class="space-y-3 text-center">
            <div>
                <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-red-700">
                    {{ __('Application update') }}
                </p>
                <h1 class="mt-0.5 text-[15px] font-semibold leading-snug text-slate-900">
                    {{ __('Thank you') }}
                </h1>
                <p class="mt-1 text-[10px] leading-relaxed text-slate-600 sm:text-[11px]">
                    {{ __('This application was not approved. If you have questions, contact support.') }}
                </p>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-2.5 text-left text-[10px] text-slate-700 shadow-sm sm:p-3 sm:text-[11px]">
                <dl class="space-y-2">
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Resort') }}</dt>
                        <dd class="mt-0.5 min-w-0 break-words">{{ $registration->tenant_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Hostname') }}</dt>
                        <dd class="mt-0.5 font-mono text-slate-600 break-all">{{ $registration->primary_domain }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('Status') }}</dt>
                        <dd class="mt-0.5">{{ __('Rejected') }}</dd>
                    </div>
                </dl>
                @if(filled($registration->rejection_reason))
                    <p class="mt-2 border-t border-slate-100 pt-2 text-[10px] leading-relaxed text-slate-600 sm:text-[11px]">{{ $registration->rejection_reason }}</p>
                @endif
            </div>
        </div>

        <div class="mt-3 text-center">
            <a href="{{ route('landing') }}" class="text-[11px] font-medium text-sky-600 hover:text-sky-800 sm:text-xs">{{ __('Back to home') }}</a>
        </div>
    @else
        <div
            x-data="{
                timer: null,
                pollUrl: @js($statusPollUrl),
                poll() {
                    fetch(this.pollUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then((r) => (r.ok ? r.json() : Promise.reject()))
                        .then((data) => {
                            if (data.status === 'approved' && data.sign_in_url) {
                                window.location.href = data.sign_in_url;
                            }
                            if (data.status === 'rejected') {
                                window.location.reload();
                            }
                        })
                        .catch(() => {});
                },
                init() {
                    this.poll();
                    this.timer = setInterval(() => this.poll(), 12000);
                },
            }"
        >
            <div class="space-y-3 text-center">
                <div>
                    <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-slate-600">
                        {{ __('Application received') }}
                    </p>
                    <h1 class="mt-0.5 text-[15px] font-semibold leading-snug text-slate-900">
                        {{ __('Thank you') }}
                    </h1>
                    <p class="mt-1 text-[10px] leading-relaxed text-slate-600 sm:text-[11px]">
                        {{ __('We emailed you a confirmation. Our team will review your subscription and payment, then email you again when your resort is approved.') }}
                    </p>
                    @if($isPendingReview)
                        <p class="mt-1.5 text-[9px] leading-relaxed text-slate-500 sm:text-[10px]">
                            {{ __('This page will open your sign-in link automatically when your application is approved.') }}
                        </p>
                    @endif
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-2.5 text-left text-[10px] text-slate-700 shadow-sm sm:p-3 sm:text-[11px]">
                    <dl class="space-y-2">
                        <div>
                            <dt class="font-semibold text-slate-900">{{ __('Resort') }}</dt>
                            <dd class="mt-0.5 min-w-0 break-words">{{ $registration->tenant_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-900">{{ __('Hostname') }}</dt>
                            <dd class="mt-0.5 font-mono text-slate-600 break-all">{{ $registration->primary_domain }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-900">{{ __('Status') }}</dt>
                            <dd class="mt-0.5 min-w-0">
                                @if($registration->status === TenantRegistrationRequest::STATUS_PENDING_REVIEW)
                                    {{ __('Waiting for superadmin approval') }}
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-3 text-center">
                <a href="{{ route('landing') }}" class="text-[11px] font-medium text-sky-600 hover:text-sky-800 sm:text-xs">{{ __('Back to home') }}</a>
            </div>
        </div>
    @endif
</x-tenant::guest-layout>
