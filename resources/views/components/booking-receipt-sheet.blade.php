@props([
    'tenant',
    'booking',
    'nights',
    'printedAt',
])
@php
    /** @var \App\Models\Tenant $tenant */
    /** @var \App\Models\Booking $booking */
    /** @var int $nights */
    /** @var \Carbon\CarbonInterface $printedAt */
    $siteName = $tenant->appDisplayName();
    $guestLabel = $booking->guest_name ?? $booking->user?->name ?? '—';
    $guestEmail = $booking->guest_email ?? $booking->user?->email;
    $payable = (float) $booking->amount_payable;
    $paid = (float) ($booking->amount_paid ?? 0);
    $rate = (float) ($booking->room?->price_per_night ?? 0);
    $lineFromRate = $rate > 0 ? round($nights * $rate, 2) : null;
    $paymentType = (string) ($booking->payment_type ?? '');
    $isFullPayment = $booking->is_fully_paid && ($paymentType === 'full' || $paid + 0.009 >= $payable);
@endphp
<div role="document">
    <p class="center bold" style="font-size:13px; letter-spacing:0.02em;">{{ $siteName }}</p>
    <p class="center bold mt-sm">{{ __('PAYMENT RECEIPT') }}</p>
    <p class="center small mt-sm">{{ $isFullPayment ? __('Full payment received') : __('Payment record') }}</p>

    <hr class="rule">

    <div class="row"><span>{{ __('Booking') }} #</span><span>{{ $booking->id }}</span></div>
    <div class="row"><span>{{ __('Issued') }}</span><span>{{ $printedAt->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</span></div>
    <div class="row"><span>{{ __('Booking status') }}</span><span>{{ ucfirst((string) $booking->status) }}</span></div>

    <hr class="rule">

    <p class="bold">{{ __('Guest') }}</p>
    <p class="pre-wrap mt-sm">{{ $guestLabel }}</p>
    @if($guestEmail)
        <p class="small mt-sm pre-wrap">{{ $guestEmail }}</p>
    @endif
    @if($booking->guest_phone)
        <p class="small mt-sm">{{ $booking->guest_phone }}</p>
    @endif

    <hr class="rule">

    <p class="bold">{{ __('Stay') }}</p>
    <div class="row mt-sm"><span>{{ __('Room') }}</span><span>{{ $booking->room?->name ?? '—' }}</span></div>
    <div class="row mt-sm"><span>{{ __('Check-in') }}</span><span>{{ $booking->check_in?->timezone(config('app.timezone'))->format('M j, Y') }}</span></div>
    <div class="row mt-sm"><span>{{ __('Check-out') }}</span><span>{{ $booking->check_out?->timezone(config('app.timezone'))->format('M j, Y') }}</span></div>
    <div class="row mt-sm"><span>{{ __('Nights') }}</span><span>{{ $nights }}</span></div>
    @if($rate > 0)
        <div class="row mt-sm"><span>{{ __('Rate / night') }}</span><span>₱{{ number_format($rate, 2) }}</span></div>
    @endif
    @if($lineFromRate !== null && $lineFromRate > 0)
        <div class="row mt-sm"><span>{{ __('Room subtotal') }}</span><span>₱{{ number_format($lineFromRate, 2) }}</span></div>
    @endif

    <hr class="rule rule-thick">

    <div class="row bold"><span>{{ __('Amount due (stay)') }}</span><span>₱{{ number_format($payable, 2) }}</span></div>
    <div class="row mt-sm bold"><span>{{ __('Amount paid') }}</span><span>₱{{ number_format($paid, 2) }}</span></div>
    @if($booking->is_fully_paid)
        <div class="row mt-sm"><span>{{ __('Balance') }}</span><span>₱0.00</span></div>
    @endif

    <hr class="rule">

    <p class="bold">{{ __('Payment details') }}</p>
    @if($paymentType !== '')
        <div class="row mt-sm"><span>{{ __('Payment type') }}</span><span>{{ $paymentType === 'full' ? __('Full') : __('Partial') }}</span></div>
    @endif
    @if($booking->payer_full_name)
        <div class="row mt-sm"><span>{{ __('Payer') }}</span><span class="pre-wrap" style="max-width:55%; text-align:right;">{{ $booking->payer_full_name }}</span></div>
    @endif
    @if($booking->payer_gcash_no)
        <div class="row mt-sm"><span>{{ __('Method') }}</span><span class="pre-wrap" style="max-width:55%; text-align:right;">{{ $booking->payer_gcash_no }}</span></div>
    @endif
    @if($booking->payer_ref_no)
        <div class="row mt-sm"><span>{{ __('Reference') }}</span><span class="pre-wrap" style="max-width:55%; text-align:right;">{{ $booking->payer_ref_no }}</span></div>
    @endif

    <hr class="rule">

    <p class="center small mt">{{ __('Thank you for your stay.') }}</p>
    <p class="center small mt-sm">{{ __('Present this receipt at check-in (printed or on your phone).') }}</p>
    <p class="center small mt-sm">{{ __('Keep this receipt for your records.') }}</p>
</div>
