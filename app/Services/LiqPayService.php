<?php

namespace App\Services;

class LiqPayService
{
    private string $publicKey;
    private string $privateKey;
    private string $apiUrl = 'https://www.liqpay.ua/api/3/checkout';

    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * Create payment form data
     */
    public function createPayment(array $params): array
    {
        $data = [
            'version' => 3,
            'public_key' => $this->publicKey,
            'action' => $params['action'] ?? 'pay',
            'amount' => $params['amount'],
            'currency' => $params['currency'] ?? 'UAH',
            'description' => $params['description'],
            'order_id' => $params['order_id'],
            'result_url' => $params['result_url'] ?? null,
            'server_url' => $params['server_url'] ?? null,
            'language' => 'uk',
        ];

        // Add subscription params if recurring
        if (($params['action'] ?? 'pay') === 'subscribe') {
            $data['subscribe'] = 1;
            $data['subscribe_date_start'] = now()->format('Y-m-d H:i:s');
            $data['subscribe_periodicity'] = 'month';
        }

        // Remove null values
        $data = array_filter($data, fn($v) => $v !== null);

        $jsonData = json_encode($data);
        $base64Data = base64_encode($jsonData);
        $signature = $this->generateSignature($base64Data);

        return [
            'data' => $base64Data,
            'signature' => $signature,
            'action_url' => $this->apiUrl,
        ];
    }

    /**
     * Generate signature for data
     */
    public function generateSignature(string $data): string
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));
    }

    /**
     * Verify callback signature
     */
    public function verifySignature(string $data, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($data);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Decode callback data
     */
    public function decodeData(string $data): array
    {
        return json_decode(base64_decode($data), true) ?? [];
    }

    /**
     * Check payment status via API
     */
    public function checkStatus(string $orderId): ?array
    {
        $data = [
            'version' => 3,
            'public_key' => $this->publicKey,
            'action' => 'status',
            'order_id' => $orderId,
        ];

        $jsonData = json_encode($data);
        $base64Data = base64_encode($jsonData);
        $signature = $this->generateSignature($base64Data);

        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.liqpay.ua/api/request', [
            'data' => $base64Data,
            'signature' => $signature,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
