<x-tenant::app-layout>
    <x-slot name="header">
        <div class="leading-tight min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 sm:text-xl">{{ __('Payment Portal') }}</h1>
            <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Current plan, renewals, and upgrade requests.') }}</p>
        </div>
    </x-slot>

    @php
        $recommendedPlanId = null;
        $latestIsRenewal = $latestUpgradeRequest && (int) $latestUpgradeRequest->current_plan_id === (int) $latestUpgradeRequest->requested_plan_id;
        $pendingIsRenewal = $pendingUpgradeRequest && (int) $pendingUpgradeRequest->current_plan_id === (int) $pendingUpgradeRequest->requested_plan_id;
        if ($plan && $otherPlans->isNotEmpty()) {
            $recommended = $otherPlans
                ->filter(fn ($p) => (float) $p->price_monthly > (float) $plan->price_monthly)
                ->sortBy('price_monthly')
                ->first();
            $recommendedPlanId = $recommended?->id;
        }
    @endphp

    <div class="w-full min-w-0 max-w-7xl space-y-6"
         x-data="{
            upgradeModalOpen: false,
            requestKind: 'upgrade',
            selectedPlanId: '',
            selectedPlanName: '',
            selectedPlanPrice: '',
            selectedMonths: 1,
            quoteLoading: false,
            quoteError: '',
            quote: null,
            quoteUrl: @js(tenant_url('payment/upgrade-quote')),
            openUpgradeModal(dataset) {
                this.requestKind = 'upgrade';
                this.selectedPlanId = dataset.planId;
                this.selectedPlanName = dataset.planName;
                this.selectedPlanPrice = dataset.planPrice;
                this.selectedMonths = 1;
                this.quote = null;
                this.quoteError = '';
                this.upgradeModalOpen = true;
                this.$nextTick(() => this.fetchQuote());
            },
            openRenewModal(dataset) {
                this.requestKind = 'renewal';
                this.selectedPlanId = dataset.planId;
                this.selectedPlanName = dataset.planName;
                this.selectedPlanPrice = dataset.planPrice;
                this.selectedMonths = 1;
                this.quote = null;
                this.quoteError = '';
                this.upgradeModalOpen = true;
                this.$nextTick(() => this.fetchQuote());
            },
            async fetchQuote() {
                if (!this.selectedPlanId) return;
                this.quoteLoading = true;
                this.quoteError = '';
                this.quote = null;
                try {
                    const u = new URL(this.quoteUrl, window.location.href);
                    u.searchParams.set('plan_id', this.selectedPlanId);
                    u.searchParams.set('months', this.selectedMonths);
                    const res = await fetch(u.toString(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(data.message || 'Could not load quote');
                    this.quote = data;
                } catch (e) {
                    this.quote = null;
                    this.quoteError = e.message || 'Could not load quote';
                } finally {
                    this.quoteLoading = false;
                }
            },
            fmtMoney(n) {
                const x = Number(n);
                if (Number.isNaN(x)) return '—';
                return '₱' + Math.round(x).toLocaleString();
            }
         }"
         @keydown.escape.window="upgradeModalOpen = false">
        <div class="rounded-xl border border-gray-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Current subscription</h2>
            @if($plan)
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs text-gray-500">Current plan</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $plan->name }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs text-gray-500">Monthly price</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            @if($amount > 0)
                                ₱{{ number_format($amount, 0) }}/month
                            @else
                                Free
                            @endif
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs text-gray-500">Subscription ends</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $subscriptionEndsAt ? $subscriptionEndsAt->format('M d, Y') : 'Not set' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs text-gray-500">Remaining</p>
                        <p class="mt-1 text-sm font-semibold {{ ($daysRemaining !== null && $daysRemaining <= 7) ? 'text-amber-600' : 'text-gray-900' }}">
                            @if($daysRemaining === null)
                                N/A
                            @elseif($daysRemaining <= 0)
                                Expired
                            @else
                                {{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="button"
                       data-plan-id="{{ $plan?->id }}"
                       data-plan-name="{{ $plan?->name }}"
                       data-plan-price="₱{{ number_format((float) $amount, 0) }}/month"
                       @click="openRenewModal($event.currentTarget.dataset)"
                       @disabled($pendingUpgradeRequest !== null || ! $plan || $amount <= 0)
                       class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm transition {{ ($pendingUpgradeRequest !== null || ! $plan || $amount <= 0) ? 'cursor-not-allowed bg-gray-400' : 'bg-teal-600 hover:bg-teal-700' }}">
                        Renew subscription
                    </button>
                    <span class="text-xs text-gray-500">Request a plan upgrade below — unused subscription time is credited toward your upgrade.</span>
                </div>

                <p class="mt-3 text-sm text-gray-500">Use the button above to continue your renewal through payment request submission.</p>
            @else
                <p class="mt-2 text-gray-500">No subscription plan assigned. Contact the platform admin.</p>
            @endif
        </div>

        <div id="plans" class="rounded-xl border border-gray-200/80 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-gray-800">Other available plans</h2>
                <span class="text-xs text-gray-500">Compare and upgrade anytime</span>
            </div>

            @if($latestUpgradeRequest && $latestUpgradeRequest->status === 'rejected')
                <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800">
                    Your {{ $latestIsRenewal ? 'renewal' : 'upgrade' }} request for <strong>{{ $latestUpgradeRequest->requestedPlan?->name ?? 'the selected plan' }}</strong> was rejected.
                    @if($latestUpgradeRequest->review_notes)
                        <div class="mt-1 text-rose-900">
                            Reason: <strong>{{ $latestUpgradeRequest->review_notes }}</strong>
                        </div>
                    @endif
                </div>
            @elseif($latestUpgradeRequest && $latestUpgradeRequest->status === 'approved')
                <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                    Your latest {{ $latestIsRenewal ? 'renewal' : 'upgrade' }} request was approved for <strong>{{ $latestUpgradeRequest->requestedPlan?->name ?? 'the selected plan' }}</strong>.
                    @if($latestUpgradeRequest->review_notes)
                        <div class="mt-1 text-emerald-900">
                            Note: <strong>{{ $latestUpgradeRequest->review_notes }}</strong>
                        </div>
                    @endif
                </div>
            @elseif($pendingUpgradeRequest)
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    You already have a pending {{ $pendingIsRenewal ? 'renewal' : 'upgrade' }} request for <strong>{{ $pendingUpgradeRequest->requestedPlan?->name ?? 'a new plan' }}</strong>
                    @if($pendingUpgradeRequest->proration_amount_due !== null)
                        (quoted amount due: <strong>₱{{ number_format((float) $pendingUpgradeRequest->proration_amount_due, 0) }}</strong>)
                    @endif
                    — superadmin review is in progress.
                </div>
            @endif

            @if($otherPlans->isEmpty())
                <p class="mt-3 text-sm text-gray-500">No other active plans are available right now.</p>
            @else
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($otherPlans as $candidate)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $candidate->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        ₱{{ number_format((float) $candidate->price_monthly, 0) }}/month
                                    </p>
                                </div>
                                @if($recommendedPlanId === $candidate->id)
                                    <span class="rounded-full bg-indigo-50 px-2 py-1 text-[11px] font-medium text-indigo-700">Recommended</span>
                                @elseif($candidate->max_rooms === null)
                                    <span class="rounded-full bg-teal-50 px-2 py-1 text-[11px] font-medium text-teal-700">Unlimited rooms</span>
                                @endif
                            </div>

                            @if($candidate->description)
                                <p class="mt-3 text-xs text-gray-600">{{ $candidate->description }}</p>
                            @endif

                            <div class="mt-3 text-xs text-gray-600">
                                Room limit:
                                <span class="font-medium text-gray-800">
                                    {{ $candidate->max_rooms === null ? 'Unlimited' : $candidate->max_rooms }}
                                </span>
                            </div>

                            @if(is_array($candidate->features) && count($candidate->features) > 0)
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach(array_slice($candidate->features, 0, 6) as $feature)
                                        <span class="rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[11px] text-gray-700">
                                            {{ \Illuminate\Support\Str::of($feature)->replace('_', ' ')->title() }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <button type="button"
                                    data-plan-id="{{ $candidate->id }}"
                                    data-plan-name="{{ $candidate->name }}"
                                    data-plan-price="₱{{ number_format((float) $candidate->price_monthly, 0) }}/month"
                                    @click="openUpgradeModal($event.currentTarget.dataset)"
                                    @disabled($pendingUpgradeRequest !== null)
                                    class="mt-4 inline-flex w-full items-center justify-center rounded-lg px-3 py-2 text-xs font-semibold text-white shadow-sm transition
                                           {{ $pendingUpgradeRequest ? 'cursor-not-allowed bg-gray-400' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                                {{ $pendingUpgradeRequest ? 'Request pending' : 'Request upgrade' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Upgrade request modal --}}
        <div x-show="upgradeModalOpen" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div @click="upgradeModalOpen = false" class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>
            <div x-show="upgradeModalOpen" @click.stop
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-gray-900" x-text="requestKind === 'renewal' ? 'Renew subscription' : 'Request plan upgrade'"></h2>
                    <button type="button" @click="upgradeModalOpen = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <form method="POST" action="{{ tenant_url('payment/upgrade-request') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="requested_plan_id" :value="selectedPlanId">

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <p class="text-xs text-gray-500" x-text="requestKind === 'renewal' ? 'Renewing plan' : 'Selected plan'"></p>
                            <p class="text-sm font-semibold text-gray-900" x-text="selectedPlanName"></p>
                            <p class="text-xs text-gray-600" x-text="selectedPlanPrice"></p>
                        </div>

                        <div>
                            <label for="requested_months" class="block text-sm font-medium text-gray-700">Subscription months</label>
                            <select id="requested_months" name="requested_months" x-model.number="selectedMonths" @change="fetchQuote()"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected((int) old('requested_months', 1) === $m)>{{ $m }} month{{ $m === 1 ? '' : 's' }}</option>
                                @endforeach
                            </select>
                            @error('requested_months') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-lg border border-indigo-100 bg-indigo-50/80 p-3 text-xs text-gray-800 space-y-2">
                            <p class="font-semibold text-indigo-900" x-text="requestKind === 'renewal' ? 'Renewal estimate (advance payment)' : 'Upgrade estimate ({{ $billingDaysPerMonth }}-day billing months)'"></p>
                            <template x-if="quoteLoading">
                                <p class="text-gray-600">Calculating…</p>
                            </template>
                            <p x-show="quoteError && !quoteLoading" class="text-red-600" x-text="quoteError"></p>
                            <template x-if="quote && !quoteLoading">
                                <dl class="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                                    <div class="flex justify-between gap-2 sm:col-span-2"><dt class="text-gray-600">Days left on current plan</dt><dd class="font-medium text-right" x-text="quote.days_remaining"></dd></div>
                                    <div class="flex justify-between gap-2"><dt class="text-gray-600">Credit from unused time</dt><dd class="font-medium text-right" x-text="fmtMoney(quote.credit_amount)"></dd></div>
                                    <div class="flex justify-between gap-2"><dt class="text-gray-600">New plan × months</dt><dd class="font-medium text-right" x-text="fmtMoney(quote.new_term_total)"></dd></div>
                                    <div class="flex justify-between gap-2 sm:col-span-2 border-t border-indigo-100 pt-2 mt-1"><dt class="text-gray-800 font-semibold">Amount to pay now</dt><dd class="font-semibold text-right text-indigo-900" x-text="fmtMoney(quote.amount_due)"></dd></div>
                                    <div class="flex justify-between gap-2"><dt class="text-gray-600">Days purchased ({{ $billingDaysPerMonth }} × months)</dt><dd class="font-medium text-right"><span x-text="quote.base_days"></span> days</dd></div>
                                    <div class="flex justify-between gap-2"><dt class="text-gray-600">Extra days (credit rollover)</dt><dd class="font-medium text-right"><span x-text="quote.rollover_days"></span> days</dd></div>
                                    <div class="flex justify-between gap-2 sm:col-span-2"><dt class="text-gray-600">New subscription length</dt><dd class="font-medium text-right"><span x-text="quote.total_days"></span> days after approval</dd></div>
                                    <div class="flex justify-between gap-2 sm:col-span-2 text-gray-600"><dt>Estimated new end date</dt><dd class="font-medium text-right text-gray-800" x-text="quote.new_subscription_end ? new Date(quote.new_subscription_end).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) : '—'"></dd></div>
                                </dl>
                            </template>
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment method</label>
                            <input id="payment_method" name="payment_method" type="text" value="{{ old('payment_method') }}"
                                   placeholder="GCash, Maya, Bank Transfer, etc."
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900">
                            @error('payment_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700">Payment reference (optional)</label>
                            <input id="payment_reference" name="payment_reference" type="text" value="{{ old('payment_reference') }}"
                                   placeholder="Reference number / transaction ID"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900">
                            @error('payment_reference') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="payment_proof" class="block text-sm font-medium text-gray-700">Payment proof (optional)</label>
                            <input id="payment_proof" name="payment_proof" type="file" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700">
                            <p class="mt-1 text-xs text-gray-500">Image only, max 1.9MB</p>
                            @error('payment_proof') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700">Additional details</label>
                            <textarea id="payment_notes" name="payment_notes" rows="3"
                                      class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900"
                                      placeholder="Anything superadmin should check before approving">{{ old('payment_notes') }}</textarea>
                            @error('payment_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="upgradeModalOpen = false"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                <span x-text="requestKind === 'renewal' ? 'Send renewal request' : 'Send request'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-tenant::app-layout>
