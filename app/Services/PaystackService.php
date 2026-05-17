<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->baseUrl = config('services.paystack.payment_url', 'https://api.paystack.co');
    }

    /**
     * Initialize a Paystack transaction and get the authorization URL.
     * Amount must be in the smallest currency unit (pesewas for GHS).
     */
    public function initializeTransaction(array $data): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", $data);

        if (!$response->successful()) {
            Log::error('Paystack initialize failed', ['response' => $response->body(), 'data' => $data]);
            throw new Exception('Could not initialize payment: ' . ($response->json('message') ?? 'Unknown error'));
        }

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new Exception('Paystack initialization failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }

    /**
     * Verify a Paystack transaction by reference.
     */
    public function verifyTransaction(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        if (!$response->successful()) {
            Log::error('Paystack verify failed', ['reference' => $reference, 'response' => $response->body()]);
            throw new Exception('Could not verify payment: ' . ($response->json('message') ?? 'Unknown error'));
        }

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new Exception('Paystack verification failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }

    /**
     * Initiate a refund for a transaction. Pass null amount for a full refund.
     */
    public function refundTransaction(string $transactionId, ?float $amount = null): array
    {
        $payload = ['transaction' => $transactionId];

        if ($amount !== null) {
            $payload['amount'] = (int) round($amount * 100);
        }

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/refund", $payload);

        if (!$response->successful()) {
            Log::error('Paystack refund failed', ['transaction_id' => $transactionId, 'response' => $response->body()]);
            throw new Exception('Could not process refund: ' . ($response->json('message') ?? 'Unknown error'));
        }

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new Exception('Paystack refund failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }

    /**
     * Fetch a single transaction by ID.
     */
    public function getTransaction(string $id): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/{$id}");

        if (!$response->successful()) {
            throw new Exception('Could not fetch transaction: ' . ($response->json('message') ?? 'Unknown error'));
        }

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new Exception('Paystack fetch failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }

    /**
     * Verify the HMAC signature on an incoming Paystack webhook.
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($expected, $signature);
    }
}
