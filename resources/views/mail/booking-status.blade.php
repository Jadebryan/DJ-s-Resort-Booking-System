@php
    /** @var \App\Models\Booking $booking */
    /** @var string $status */
    /** @var \App\Models\Tenant|null $tenant */
    /** @var string|null $guestReceiptUrl */
    /** @var \Carbon\CarbonInterface|null $printedAt */
    $room = $booking->room?->name ?? __('Room');
    $checkIn = $booking->check_in?->timezone(config('app.timezone'))->format('M j, Y');
    $checkOut = $booking->check_out?->timezone(config('app.timezone'))->format('M j, Y');
    $siteName = $tenant?->appDisplayName() ?? $room;
    $printedAt = $printedAt ?? now()->timezone(config('app.timezone'));
    $nights = ($booking->check_in && $booking->check_out)
        ? max(1, (int) $booking->check_in->diffInDays($booking->check_out))
        : 1;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
</head>
<body style="margin:0;padding:0;background:#f3f4f6;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f3f4f6;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:520px;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;">

@if($status === 'confirmed')
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 18px; font-weight: 700; color: #111827; padding-bottom: 8px;">
                        {{ __('Booking confirmed') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151; padding-bottom: 16px;">
                        {{ __('Your reservation at :name has been confirmed.', ['name' => $siteName]) }}
                    </td>
                </tr>
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151; padding-bottom: 8px;">
                        <strong>{{ __('Room') }}:</strong> {{ $room }}<br>
                        <strong>{{ __('Check-in') }}:</strong> {{ $checkIn }}<br>
                        <strong>{{ __('Check-out') }}:</strong> {{ $checkOut }}
                    </td>
                </tr>

    @if($booking->is_fully_paid && $tenant)
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #b45309; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                        <strong>{{ __('Important — bring your receipt') }}</strong><br>
                        {{ __('Please present this receipt (printed or on your phone) at check-in. The resort may ask to verify your payment.') }}
                    </td>
                </tr>
                <tr><td style="height:16px;"></td></tr>
                <tr>
                    <td style="background:#f9fafb;border-radius:8px;padding:16px;border:1px solid #e5e7eb;">
                        @include('mail.partials.booking-receipt-table', [
                            'tenant' => $tenant,
                            'booking' => $booking,
                            'nights' => $nights,
                            'printedAt' => $printedAt,
                        ])
                    </td>
                </tr>
        @if($guestReceiptUrl)
                <tr><td style="height:20px;"></td></tr>
                <tr>
                    <td align="center" style="padding-bottom: 8px;">
                        <a href="{{ $guestReceiptUrl }}" style="display:inline-block;font-family:ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:#ffffff;background:#0d9488;text-decoration:none;padding:12px 20px;border-radius:8px;">
                            {{ __('Open printable receipt') }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 12px; line-height: 1.5; color: #6b7280; text-align: center; padding-bottom: 8px;">
                        {{ __('Use this link on your phone or computer to print a thermal-friendly receipt. The link stays valid for one year.') }}
                    </td>
                </tr>
        @endif
    @elseif($booking->is_fully_paid)
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151; padding-top: 8px;">
                        <strong>{{ __('Important — bring your receipt') }}</strong><br>
                        {{ __('Please present proof of payment (printed or on your phone) at check-in.') }}
                    </td>
                </tr>
    @else
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151; padding-top: 8px;">
                        {{ __('Payment may still be due. Contact the resort if you are unsure of your balance.') }}
                    </td>
                </tr>
    @endif

@elseif($status === 'cancelled')
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 18px; font-weight: 700; color: #111827; padding-bottom: 8px;">
                        {{ __('Booking cancelled') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151;">
                        {{ __('Your reservation has been cancelled.') }}<br><br>
                        <strong>{{ __('Room') }}:</strong> {{ $room }}<br>
                        <strong>{{ __('Dates') }}:</strong> {{ $checkIn }} – {{ $checkOut }}
                    </td>
                </tr>

@else
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 18px; font-weight: 700; color: #111827; padding-bottom: 8px;">
                        {{ __('Booking received') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151;">
                        {{ __('We have received your booking request.') }}<br><br>
                        <strong>{{ __('Room') }}:</strong> {{ $room }}<br>
                        <strong>{{ __('Check-in') }}:</strong> {{ $checkIn }}<br>
                        <strong>{{ __('Check-out') }}:</strong> {{ $checkOut }}<br><br>
                        {{ __('You will be notified once it is confirmed.') }}
                    </td>
                </tr>
@endif

                <tr>
                    <td style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 12px; color: #9ca3af; padding-top: 24px;">
                        {{ $siteName }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
