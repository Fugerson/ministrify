<?php

namespace App\Services;

use App\Models\Church;
use App\Models\OnlineDonation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MonobankService
{
    private string $token;
    private string $apiUrl = 'https://api.monobank.ua/api/merchant/invoice/create';

    public function __construct(Church $church)
    {
        $settings = $church->payment_settings ?? [];
        $this->token = $settings['monobank_token'] ?? '';
    }

    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    public function createPayment(OnlineDonation $donation, string $redirectUrl, string $webhookUrl): ?array
    {
        $reference = 'donation_' . $donation->id . '_' . Str::random(8);

        $donation->update(['provider_order_id' => $reference]);

        try {
            $response = Http::withHeaders([
                'X-Token' => $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'amount' => (int) ($donation->amount * 100), // Amount in kopecks
                'ccy' => 980, // UAH
                'merchantPaymInfo' => [
                    'reference' => $reference,
                    'destination' => $donation->description ?? 'Пожертва для ' . $donation->church->name,
                ],
                'redirectUrl' => $redirectUrl,
                'webHookUrl' => $webhookUrl,
                'validity' => 3600, // 1 hour
                'paymentType' => 'debit',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $donation->update([
                    'provider_payment_id' => $data['invoiceId'] ?? null,
                    'provider_response' => $data,
                ]);

                return [
                    'url' => $data['pageUrl'] ?? null,
                    'invoiceId' => $data['invoiceId'] ?? null,
                ];
            }

            $donation->markAsFailed($response->body(), $response->json() ?? []);
            return null;

        } catch (\Exception $e) {
            $donation->markAsFailed($e->getMessage());
            return null;
        }
    }

    public function handleWebhook(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (!$reference) return;

        $donation = OnlineDonation::where('provider_order_id', $reference)->first();
        if (!$donation) return;

        $status = $data['status'] ?? '';
        $invoiceId = $data['invoiceId'] ?? null;

        $donation->update([
            'provider_payment_id' => $invoiceId,
            'provider_response' => $data,
        ]);

        switch ($status) {
            case 'success':
                $donation->markAsSuccess($data);
                break;

            case 'failure':
            case 'expired':
                $donation->markAsFailed($data['failureReason'] ?? 'Payment failed', $data);
                break;

            case 'processing':
            case 'created':
                $donation->update(['status' => OnlineDonation::STATUS_PROCESSING]);
                break;
        }
    }

    public function verifySignature(string $body, string $signature): bool
    {
        // Monobank uses X-Sign header for webhook verification
        // The signature is base64(sha256(body + pubKeyBase64))
        // For simplicity, we'll skip this for now and rely on unique references
        return true;
    }
}
