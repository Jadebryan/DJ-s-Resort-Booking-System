@php
    /** @var \App\Models\Tenant $tenant */
    /** @var \App\Models\Booking $booking */
    /** @var int $nights */
    /** @var \Carbon\CarbonInterface $printedAt */
    $isGuestReceipt = $isGuestReceipt ?? false;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} #{{ $booking->id }} — {{ $tenant->appDisplayName() }}</title>
    <style>
        :root {
            --paper-w: 72mm;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #e5e7eb;
            color: #000;
            font-family: ui-monospace, "Cascadia Mono", "Segoe UI Mono", "Liberation Mono", Menlo, Consolas, monospace;
            font-size: 11px;
            line-height: 1.35;
        }
        .toolbar {
            max-width: 420px;
            margin: 0 auto;
            padding: 12px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: center;
        }
        .toolbar button {
            cursor: pointer;
            border: 1px solid #0d9488;
            background: #14b8a6;
            color: #fff;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
        }
        .toolbar button:hover { background: #0d9488; }
        .toolbar a {
            color: #0f766e;
            font-size: 13px;
        }
        .sheet-wrap {
            width: var(--paper-w);
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 4mm 3mm 6mm;
            box-shadow: 0 1px 6px rgba(0,0,0,.12);
        }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .mt { margin-top: 6px; }
        .mt-sm { margin-top: 4px; }
        .rule {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .rule-thick {
            border-top-style: solid;
            border-top-width: 2px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 6px;
            word-break: break-word;
        }
        .row > span:first-child { flex: 0 1 auto; }
        .row > span:last-child { flex: 0 0 auto; text-align: right; }
        .small { font-size: 9px; color: #333; }
        .pre-wrap { white-space: pre-wrap; word-break: break-word; }

        @media print {
            html, body { background: #fff; }
            .no-print { display: none !important; }
            .sheet-wrap {
                box-shadow: none;
                width: var(--paper-w);
                max-width: none;
                margin: 0;
                padding: 2mm 2mm 4mm;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">{{ __('Print receipt') }}</button>
        @if($isGuestReceipt)
            <a href="{{ route('tenant.landing') }}">{{ __('Home') }}</a>
        @else
            <a href="{{ route('tenant.bookings.index') }}">{{ __('Back to bookings') }}</a>
        @endif
    </div>

    <div class="sheet-wrap">
        <x-booking-receipt-sheet
            :tenant="$tenant"
            :booking="$booking"
            :nights="$nights"
            :printed-at="$printedAt"
        />
    </div>
</body>
</html>
