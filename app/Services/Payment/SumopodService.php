<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SumopodService
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.sumopod.base_url', 'https://api-pay.sumopod.com'), '/');
        $this->apiKey = (string) config('services.sumopod.api_key', '');
    }

    /**
     * Create a payment link for a subscription.
     *
     * @param  array{order_id: string, amount: int, success_return_url?: string, cancel_return_url?: string}  $params
     * @return array{payment_id: string, payment_link_url: string, status: string, amount: ?int, fee: ?int}
     */
    public function createPayment(array $params): array
    {
        $redirectUrl = $params['success_return_url'] ?? config('services.sumopod.redirect_url', url('/dashboard/subscription'));

        $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
            ->timeout(30)
            ->post("{$this->baseUrl}/api/v1/payments", [
                'order_id' => $params['order_id'],
                'amount' => $params['amount'],
                'currency' => 'IDR',
                'success_return_url' => $redirectUrl,
                'cancel_return_url' => $params['cancel_return_url'] ?? $redirectUrl,
                'payment_method_type_code' => 'QRIS',
            ]);

        if (! $response->successful()) {
            Log::error('Sumopod payment creation failed in fabriku', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $params['order_id'],
            ]);

            $errorMsg = $response->json('message') ?? $response->body();

            throw new RuntimeException("Gagal membuat payment link Sumopod: {$errorMsg}");
        }

        $data = $response->json();
        if (! is_array($data)
            || ! is_string($data['payment_id'] ?? null) || $data['payment_id'] === ''
            || ! is_string($data['payment_link_url'] ?? null) || $data['payment_link_url'] === '') {
            throw new RuntimeException('Respons payment Sumopod tidak valid.');
        }

        return [
            'payment_id' => $data['payment_id'],
            'payment_link_url' => $data['payment_link_url'],
            'status' => $data['status'] ?? 'pending',
            'amount' => is_int($data['amount'] ?? null) ? $data['amount'] : null,
            'fee' => is_int($data['fee'] ?? null) ? $data['fee'] : null,
        ];
    }
}
