@php
    $siteName = $tenant->appDisplayName();
    $guestLabel = $booking->guest_name ?? $booking->user?->name ?? '—';
    $guestEmail = $booking->guest_email ?? $booking->user?->email;
    $payable = (float) $booking->amount_payable;
    $paid = (float) ($booking->amount_paid ?? 0);
    $rate = (float) ($booking->room?->price_per_night ?? 0);
    $lineFromRate = $rate > 0 ? round($nights * $rate, 2) : null;
    $paymentType = (string) ($booking->payment_type ?? '');
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.45; color: #111827; max-width: 480px;">
    <tr>
        <td style="padding: 8px 0; text-align: center; font-weight: 700; font-size: 16px;">{{ $siteName }}</td>
    </tr>
    <tr>
        <td style="padding: 4px 0; text-align: center; font-weight: 700;">{{ __('PAYMENT RECEIPT') }}</td>
    </tr>
    <tr>
        <td style="padding: 4px 0 12px; text-align: center; font-size: 12px; color: #4b5563;">{{ __('Full payment received') }}</td>
    </tr>
    <tr><td style="border-top: 1px dashed #9ca3af; padding: 8px 0;"></td></tr>
    <tr>
        <td>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" style="font-size: 13px;">
                <tr><td>{{ __('Booking') }} #</td><td align="right" style="font-weight: 600;">{{ $booking->id }}</td></tr>
                <tr><td>{{ __('Issued') }}</td><td align="right">{{ $printedAt->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</td></tr>
                <tr><td>{{ __('Booking status') }}</td><td align="right">{{ ucfirst((string) $booking->status) }}</td></tr>
            </table>
        </td>
    </tr>
    <tr><td style="border-top: 1px dashed #9ca3af; padding: 8px 0;"></td></tr>
    <tr><td style="font-weight: 700; padding-top: 4px;">{{ __('Guest') }}</td></tr>
    <tr><td style="padding-top: 4px;">{{ $guestLabel }}</td></tr>
    @if($guestEmail)
        <tr><td style="font-size: 12px; color: #4b5563; padding-top: 4px;">{{ $guestEmail }}</td></tr>
    @endif
    @if($booking->guest_phone)
        <tr><td style="font-size: 12px; color: #4b5563;">{{ $booking->guest_phone }}</td></tr>
    @endif
    <tr><td style="border-top: 1px dashed #9ca3af; padding: 8px 0;"></td></tr>
    <tr><td style="font-weight: 700;">{{ __('Stay') }}</td></tr>
    <tr>
        <td>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" style="font-size: 13px;">
                <tr><td>{{ __('Room') }}</td><td align="right">{{ $booking->room?->name ?? '—' }}</td></tr>
                <tr><td>{{ __('Check-in') }}</td><td align="right">{{ $booking->check_in?->timezone(config('app.timezone'))->format('M j, Y') }}</td></tr>
                <tr><td>{{ __('Check-out') }}</td><td align="right">{{ $booking->check_out?->timezone(config('app.timezone'))->format('M j, Y') }}</td></tr>
                <tr><td>{{ __('Nights') }}</td><td align="right">{{ $nights }}</td></tr>
                @if($rate > 0)
                    <tr><td>{{ __('Rate / night') }}</td><td align="right">₱{{ number_format($rate, 2) }}</td></tr>
                @endif
                @if($lineFromRate !== null && $lineFromRate > 0)
                    <tr><td>{{ __('Room subtotal') }}</td><td align="right">₱{{ number_format($lineFromRate, 2) }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
    <tr><td style="border-top: 2px solid #111827; padding: 8px 0;"></td></tr>
    <tr>
        <td>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" style="font-size: 14px; font-weight: 700;">
                <tr><td>{{ __('Amount due (stay)') }}</td><td align="right">₱{{ number_format($payable, 2) }}</td></tr>
                <tr><td>{{ __('Amount paid') }}</td><td align="right">₱{{ number_format($paid, 2) }}</td></tr>
                <tr><td>{{ __('Balance') }}</td><td align="right">₱0.00</td></tr>
            </table>
        </td>
    </tr>
    <tr><td style="border-top: 1px dashed #9ca3af; padding: 8px 0;"></td></tr>
    <tr><td style="font-weight: 700;">{{ __('Payment details') }}</td></tr>
    <tr>
        <td>
            <table width="100%" cellpadding="4" cellspacing="0" border="0" style="font-size: 13px;">
                @if($paymentType !== '')
                    <tr><td>{{ __('Payment type') }}</td><td align="right">{{ $paymentType === 'full' ? __('Full') : __('Partial') }}</td></tr>
                @endif
                @if($booking->payer_full_name)
                    <tr><td>{{ __('Payer') }}</td><td align="right" style="word-break: break-word;">{{ $booking->payer_full_name }}</td></tr>
                @endif
                @if($booking->payer_gcash_no)
                    <tr><td>{{ __('Method') }}</td><td align="right" style="word-break: break-word;">{{ $booking->payer_gcash_no }}</td></tr>
                @endif
                @if($booking->payer_ref_no)
                    <tr><td>{{ __('Reference') }}</td><td align="right" style="word-break: break-word;">{{ $booking->payer_ref_no }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
    <tr><td style="border-top: 1px dashed #9ca3af; padding: 12px 0 4px; text-align: center; font-size: 12px; color: #4b5563;">{{ __('Thank you for your stay.') }}</td></tr>
    <tr><td style="text-align: center; font-size: 12px; color: #b45309; font-weight: 600; padding-top: 8px;">{{ __('Present this receipt at check-in (printed or on your phone).') }}</td></tr>
</table>
