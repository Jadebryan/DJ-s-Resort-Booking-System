<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report — {{ $generatedAt->format('M j, Y') }}</title>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #111; max-width: 900px; margin: 0 auto; padding: 20px; overflow-x: auto; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        .summary { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
        .summary-item { background: #f9f9f9; padding: 12px 16px; border-radius: 6px; min-width: 140px; }
        .summary-item strong { display: block; font-size: 18px; }
        .summary-item span { font-size: 11px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Booking Report</h1>
    <p class="meta">Generated {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>

    <div class="summary">
        <div class="summary-item"><span>Total Bookings</span><strong>{{ $totalBookings }}</strong></div>
        <div class="summary-item"><span>Pending</span><strong>{{ $pending }}</strong></div>
        <div class="summary-item"><span>Confirmed</span><strong>{{ $confirmed }}</strong></div>
        <div class="summary-item"><span>Cancelled</span><strong>{{ $cancelled }}</strong></div>
        <div class="summary-item"><span>Revenue (confirmed)</span><strong>₱{{ number_format($revenue, 2) }}</strong></div>
    </div>

    <h2 style="font-size: 14px; margin-bottom: 8px;">Revenue by Room</h2>
    @if($revenueByRoom->isEmpty())
        <p>No confirmed bookings.</p>
    @else
        <table>
            <thead>
                <tr><th>Room</th><th>Bookings</th><th>Revenue</th></tr>
            </thead>
            <tbody>
                @foreach($revenueByRoom as $row)
                    <tr>
                        <td>{{ $row['room']?->name ?? '—' }}</td>
                        <td>{{ $row['count'] }}</td>
                        <td>₱{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td><strong>Total</strong></td><td>{{ $revenueByRoom->sum('count') }}</td><td><strong>₱{{ number_format($revenue, 2) }}</strong></td></tr>
            </tfoot>
        </table>
    @endif

    <h2 style="font-size: 14px; margin-bottom: 8px;">All Bookings</h2>
    @if($bookings->isEmpty())
        <p>No bookings.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Room</th><th>Guest</th><th>Check-in</th><th>Check-out</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $b)
                    <tr>
                        <td>{{ $b->room?->name ?? '—' }}</td>
                        <td>{{ $b->guest_name ?? $b->user?->name ?? '—' }}<br><small>{{ $b->guest_email ?? $b->user?->email ?? '' }}</small></td>
                        <td>{{ $b->check_in?->format('M j, Y') }}</td>
                        <td>{{ $b->check_out?->format('M j, Y') }}</td>
                        <td>{{ ucfirst($b->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
