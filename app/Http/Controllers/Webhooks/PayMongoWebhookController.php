<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tenant;
use App\Services\PayMongo\BookingPayMongoSync;
use App\Services\PayMongo\PayMongoClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PayMongoWebhookController extends Controller
{
    public function __construct(
        private readonly PayMongoClient $payMongo,
        private readonly BookingPayMongoSync $sync
    ) {}

    public function handle(Request $request): SymfonyResponse
    {
        if (! $this->payMongo->isConfigured()) {
            return response()->json(['message' => 'PayMongo disabled'], 503);
        }

        $raw = $request->getContent();
        $secret = (string) config('services.paymongo.webhook_secret', '');
        if ($secret !== '' && ! $this->signatureValid($request, $raw, $secret)) {
            Log::warning('paymongo.webhook.bad_signature');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $json = json_decode($raw, true);
        if (! is_array($json)) {
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        $eventType = (string) data_get($json, 'data.attributes.type', '');
        if ($eventType !== 'payment.paid') {
            return response()->json(['received' => true], 200);
        }

        $intentId = $this->extractPaymentIntentId($json);
        if ($intentId === '') {
            $payId = data_get($json, 'data.attributes.data.id');
            if (is_string($payId) && str_starts_with($payId, 'pay_')) {
                try {
                    $pay = $this->payMongo->retrievePayment($payId);
                    $intentId = (string) data_get($pay, 'data.attributes.payment_intent_id', '');
                } catch (RuntimeException) {
                    $intentId = '';
                }
            }
        }
        if ($intentId === '') {
            return response()->json(['received' => true], 200);
        }

        try {
            $pi = $this->payMongo->retrievePaymentIntent($intentId);
        } catch (RuntimeException $e) {
            Log::error('paymongo.webhook.retrieve_failed', ['intent' => $intentId, 'error' => $e->getMessage()]);

            return response()->json(['received' => true], 200);
        }

        $meta = data_get($pi, 'data.attributes.metadata', []);
        if (! is_array($meta)) {
            return response()->json(['received' => true], 200);
        }

        $tenantId = (int) ($meta['central_tenant_id'] ?? 0);
        $bookingId = (int) ($meta['booking_id'] ?? 0);
        if ($tenantId < 1 || $bookingId < 1) {
            return response()->json(['received' => true], 200);
        }

        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            return response()->json(['received' => true], 200);
        }

        $this->applyTenantConnection($tenant);

        $booking = Booking::query()->find($bookingId);
        if (! $booking) {
            return response()->json(['received' => true], 200);
        }

        if (($pi['data']['attributes']['status'] ?? '') === 'succeeded') {
            $this->sync->applySucceededIntent($booking->fresh(), $pi);
        }

        return response()->json(['received' => true], 200);
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function extractPaymentIntentId(array $json): string
    {
        $candidates = [
            data_get($json, 'data.attributes.data.attributes.payment_intent_id'),
            data_get($json, 'data.attributes.data.attributes.payments.0'),
            data_get($json, 'data.attributes.data.id'),
        ];
        foreach ($candidates as $c) {
            if (is_string($c) && str_starts_with($c, 'pi_')) {
                return $c;
            }
        }

        $nested = data_get($json, 'data.attributes.data');
        if (is_array($nested)) {
            $id = $nested['id'] ?? null;
            if (is_string($id) && str_starts_with($id, 'pi_')) {
                return $id;
            }
        }

        return '';
    }

    private function signatureValid(Request $request, string $raw, string $webhookSecret): bool
    {
        $header = (string) $request->header('Paymongo-Signature', '');
        if ($header === '') {
            return false;
        }

        $parts = array_map('trim', explode(',', $header));
        $timestamp = null;
        $signatures = [];
        foreach ($parts as $part) {
            if (! str_contains($part, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $part, 2);
            if ($k === 't') {
                $timestamp = $v;
            }
            if ($k === 'te' || $k === 'li') {
                $signatures[] = $v;
            }
        }
        if ($timestamp === null || $signatures === []) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $raw;
        $expected = hash_hmac('sha256', $signedPayload, $webhookSecret);

        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }

        return false;
    }

    private function applyTenantConnection(Tenant $tenant): void
    {
        config([
            'database.connections.tenant.database' => $tenant->database_name,
            'database.connections.tenant.host' => env('DB_HOST', '127.0.0.1'),
            'database.connections.tenant.port' => env('DB_PORT', '3306'),
            'database.connections.tenant.username' => env('DB_USERNAME', 'root'),
            'database.connections.tenant.password' => env('DB_PASSWORD', ''),
        ]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
