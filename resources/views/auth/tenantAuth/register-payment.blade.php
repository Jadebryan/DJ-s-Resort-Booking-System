<x-tenant::guest-layout container-class="max-w-xl" :compact="true" :dense="true">
    <div class="grid gap-3 md:grid-cols-2 md:gap-4 md:items-start">
        <div class="text-center md:text-left">
            <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-slate-600">
                {{ __('Almost there') }}
            </p>
            <h1 class="mt-0.5 text-[15px] font-semibold leading-snug text-slate-900">
                {{ __('Complete payment') }}
            </h1>
            <p class="mt-0.5 text-[10px] text-slate-600 sm:text-[11px]">
                {{ __('Plan') }}: <strong>{{ $registration->plan?->name }}</strong>
            </p>

            <div class="mt-2 rounded-md border border-slate-200 bg-slate-50/90 px-2.5 py-2 text-left text-[10px] text-slate-800 sm:text-[11px]">
                <p class="text-[8px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Subscription total') }}</p>
                <dl class="mt-1 space-y-px">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-600">{{ __('Monthly rate') }}</dt>
                        <dd class="font-medium tabular-nums text-slate-900">₱{{ number_format((float) ($registration->plan?->price_monthly ?? 0), 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-600">{{ $registration->usesCustomSubscriptionDays() ? __('Length') : __('Months') }}</dt>
                        <dd class="font-medium tabular-nums text-slate-900">
                            @if($registration->usesCustomSubscriptionDays())
                                {{ $registration->subscriptionLengthDays() }} {{ __('days') }}
                            @else
                                {{ $registration->subscriptionMonths() }}
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-1 text-[11px] font-semibold text-slate-900 sm:text-xs">
                        <dt>{{ __('Total due') }}</dt>
                        <dd class="tabular-nums">₱{{ number_format($registration->amountDue(), 2) }}</dd>
                    </div>
                </dl>
            </div>

            <p class="mt-1.5 break-words text-[9px] leading-snug text-slate-500 sm:text-[10px]">
                {{ $registration->tenant_name }} — {{ $registration->primary_domain }}
            </p>
        </div>

        <div class="rounded-md border border-emerald-200/90 bg-emerald-50/60 p-2.5 shadow-sm sm:p-3">
            <h2 class="text-[10px] font-semibold leading-snug text-emerald-900 sm:text-[11px]">
                {{ __('Philippines e-wallet & bank (manual verification)') }}
            </h2>
            <p class="mt-0.5 text-[9px] leading-relaxed text-emerald-900/85 sm:text-[10px]">
                {{ __('Pay via GCash, Maya, or bank transfer, then enter your reference number and upload a screenshot of your receipt. Our team will verify before approving your resort.') }}
            </p>
            <x-form-with-busy method="POST" action="{{ route('tenant.register.payment.manual', ['registration' => $registration->token]) }}" enctype="multipart/form-data" class="mt-2 space-y-1.5" :overlay="true" busy-message="{{ __('Uploading payment details…') }}">
                @csrf
                <div>
                    <label for="payment_provider" class="block text-[10px] font-medium text-slate-800">{{ __('Method') }}</label>
                    <select id="payment_provider" name="payment_provider" required
                            class="mt-0.5 block w-full min-h-[2.125rem] rounded-md border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 sm:text-xs">
                        <option value="">{{ __('Select…') }}</option>
                        <option value="gcash" @selected(old('payment_provider') === 'gcash')>GCash</option>
                        <option value="maya" @selected(old('payment_provider') === 'maya')>Maya</option>
                        <option value="bank_transfer" @selected(old('payment_provider') === 'bank_transfer')>{{ __('Bank transfer') }}</option>
                        <option value="other" @selected(old('payment_provider') === 'other')>{{ __('Other') }}</option>
                    </select>
                    @error('payment_provider')<p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="payment_reference" class="block text-[10px] font-medium text-slate-800">{{ __('Reference / transaction ID') }}</label>
                    <input id="payment_reference" name="payment_reference" value="{{ old('payment_reference') }}" required
                           {{ \App\Support\InputHtmlAttributes::paymentReference(255) }}
                           class="mt-0.5 block w-full min-h-[2.125rem] rounded-md border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 sm:text-xs"
                           placeholder="e.g. 0917… or bank ref #"/>
                    @error('payment_reference')<p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="payment_notes" class="block text-[10px] font-medium text-slate-800">{{ __('Notes (optional)') }}</label>
                    <textarea id="payment_notes" name="payment_notes" rows="2"
                              {{ \App\Support\InputHtmlAttributes::textarea(1500) }}
                              class="mt-0.5 block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 sm:text-xs"
                              placeholder="{{ __('Sender name, time of payment, etc.') }}">{{ old('payment_notes') }}</textarea>
                    @error('payment_notes')<p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="payment_proof" class="block text-[10px] font-medium text-slate-800">{{ __('Payment proof (screenshot)') }}</label>
                    <p class="mt-0.5 text-[9px] text-emerald-900/80 leading-snug sm:text-[10px]">{{ __('Upload a clear photo or screenshot of your receipt or transfer confirmation. JPG or PNG, max 1.9MB.') }}</p>
                    <input id="payment_proof" name="payment_proof" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required
                           class="mt-0.5 block w-full text-[10px] text-slate-700 file:mr-2 file:rounded-md file:border-0 file:bg-white file:px-2 file:py-1.5 file:text-[10px] file:font-medium file:text-emerald-800 hover:file:bg-emerald-100/80"/>
                    @error('payment_proof')<p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-busy-submit class="min-h-[2.125rem] w-full rounded-md bg-emerald-600 px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 sm:text-xs" busy-text="{{ __('Submitting…') }}">
                    {{ __('Submit for verification') }}
                </x-busy-submit>
            </x-form-with-busy>
        </div>
    </div>
</x-tenant::guest-layout>
