<?php

declare(strict_types=1);

namespace App\Services\PayMongo;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal PayMongo REST client (PaymentIntents + GCash PaymentMethod).
 *
 * @see https://developers.paymongo.com/reference/create-a-paymentintent
 */
final class PayMongoClient
{
    private const BASE = 'https://api.paymongo.com/v1';

    public function isConfigured(): bool
    {
        $secret = (string) config('services.paymongo.secret', '');

        return config('services.paymongo.enabled', false) && $secret !== '';
    }

    /**
     * @param  array<string, string>  $metadata  PayMongo only accepts string values
     * @return array<string, mixed>
     */
    public function createPaymentIntent(int $amountCentavos, array $metadata = []): array
    {
        $metadata = $this->stringifyMetadata($metadata);

        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $amountCentavos,
                    'currency' => 'PHP',
                    'payment_method_allowed' => ['gcash'],
                    'description' => 'Resort booking payment',
                    'metadata' => $metadata,
                ],
            ],
        ];

        return $this->decode($this->post('/payment_intents', $payload));
    }

    /**
     * @return array<string, mixed>
     */
    public function createGcashPaymentMethod(string $name, string $email, string $phone): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'type' => 'gcash',
                    'billing' => [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                    ],
                ],
            ],
        ];

        return $this->decode($this->post('/payment_methods', $payload));
    }

    /**
     * @return array<string, mixed>
     */
    public function attachPaymentIntent(string $paymentIntentId, string $paymentMethodId, string $returnUrl): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                    'return_url' => $returnUrl,
                ],
            ],
        ];

        return $this->decode($this->post('/payment_intents/' . $paymentIntentId . '/attach', $payload));
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        return $this->decode($this->get('/payment_intents/' . $paymentIntentId));
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePayment(string $paymentId): array
    {
        return $this->decode($this->get('/payments/' . $paymentId));
    }

    /**
     * @return array<string, string>
     */
    private function stringifyMetadata(array $metadata): array
    {
        $out = [];
        foreach ($metadata as $k => $v) {
            $out[(string) $k] = (string) $v;
        }

        return $out;
    }

    private function post(string $path, array $json): Response
    {
        return Http::withBasicAuth($this->secret(), '')
            ->acceptJson()
            ->asJson()
            ->post(self::BASE . $path, $json);
    }

    private function get(string $path): Response
    {
        return Http::withBasicAuth($this->secret(), '')
            ->acceptJson()
            ->get(self::BASE . $path);
    }

    private function secret(): string
    {
        $secret = (string) config('services.paymongo.secret', '');
        if ($secret === '') {
            throw new RuntimeException('PayMongo secret key is not configured.');
        }

        return $secret;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        $json = $response->json();
        if (! $response->successful()) {
            $msg = $json['errors'][0]['detail'] ?? $json['message'] ?? $response->body() ?? 'PayMongo request failed';

            throw new RuntimeException(is_string($msg) ? $msg : 'PayMongo request failed');
        }

        if (! is_array($json)) {
            throw new RuntimeException('Invalid PayMongo response.');
        }

        return $json;
    }
}
