<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\TenantDomain;
use Illuminate\Support\Facades\DB;

$host = $argv[1] ?? 'jedssresort.localhost';

$mapped = TenantDomain::forRequestHost((string) $host);
if (! $mapped || ! $mapped->tenant) {
    fwrite(STDERR, "No tenant mapping found for host: {$host}\n");
    exit(2);
}

$tenant = $mapped->tenant;
echo "host={$host}\n";
echo "tenant_id={$tenant->id}\n";
echo "tenant_db={$tenant->database_name}\n";

config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');
DB::reconnect('tenant');

echo "tenant_connection_db=" . (string) DB::connection('tenant')->getDatabaseName() . "\n";

$count = Booking::count();
echo "bookings_count={$count}\n";

$latest = Booking::with('room')
    ->orderByDesc('id')
    ->limit(5)
    ->get()
    ->map(function (Booking $b): array {
        return [
            'id' => $b->id,
            'room_id' => $b->room_id,
            'room_name' => $b->room?->name,
            'regular_user_id' => $b->regular_user_id,
            'guest_name' => $b->guest_name,
            'guest_email' => $b->guest_email,
            'check_in' => $b->check_in?->toDateString(),
            'check_out' => $b->check_out?->toDateString(),
            'status' => $b->status,
            'payment_type' => $b->payment_type,
            'amount_paid' => $b->amount_paid,
            'proof' => $b->payment_proof_path,
            'created_at' => $b->created_at?->toDateTimeString(),
        ];
    })
    ->all();

echo "latest_bookings:\n";
foreach ($latest as $row) {
    echo json_encode($row, JSON_UNESCAPED_SLASHES) . "\n";
}

