<?php

namespace App\Services;

use App\Models\Church;
use App\Models\OnlineDonation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    protected Church $church;

    protected array $settings;

    public function __construct(Church $church)
    {
        $this->church = $church;
        $this->settings = $church->payment_settings ?? [];
    }

    public function isLiqPayAvailable(): bool
    {
        return ! empty($this->settings['liqpay_enabled'])
            && ! empty($this->settings['liqpay_public_key'])
            && ! empty($this->settings['liqpay_private_key']);
    }

    public function isMonobankAvailable(): bool
    {
        return ! empty($this->settings['monobank_enabled'])
            && ! empty($this->settings['monobank_jar_id']);
    }

    public function getMonobankJarLink(): ?string
    {
        $jarId = $this->settings['monobank_jar_id'] ?? null;
        if (! $jarId) {
            return null;
        }

        return 'https://send.monobank.ua/jar/'.$jarId;
    }

    public function createLiqPayPayment(array $data): array
    {
        $orderId = 'donation_'.$this->church->id.'_'.time().'_'.Str::random(6);

        // Create pending donation record
        $donation = OnlineDonation::create([
            'church_id' => $this->church->id,
            'provider' => OnlineDonation::PROVIDER_LIQPAY,
            'provider_order_id' => $orderId,
            'amount' => $data['amount'],
            'currency' => 'UAH',
            'status' => OnlineDonation::STATUS_PENDING,
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'description' => $data['message'] ?? null,
        ]);

        $publicKey = $this->settings['liqpay_public_key'];
        $privateKey = $this->getLiqPayPrivateKey();

        $liqpayData = base64_encode(json_encode([
            'version' => 3,
            'public_key' => $publicKey,
            'action' => 'pay',
            'amount' => $data['amount'],
            'currency' => 'UAH',
            'description' => 'Пожертва для '.$this->church->name,
            'order_id' => $orderId,
            'server_url' => route('api.webhooks.liqpay'),
            'result_url' => route('public.donate.success', $this->church->slug),
        ]));

        $signature = base64_encode(sha1($privateKey.$liqpayData.$privateKey, true));

        return [
            'data' => $liqpayData,
            'signature' => $signature,
        ];
    }

    public function createMonobankPayment(array $data): OnlineDonation
    {
        return OnlineDonation::create([
            'church_id' => $this->church->id,
            'provider' => OnlineDonation::PROVIDER_MONOBANK,
            'provider_order_id' => 'mono_'.$this->church->id.'_'.time().'_'.Str::random(6),
            'amount' => $data['amount'],
            'currency' => 'UAH',
            'status' => OnlineDonation::STATUS_PENDING,
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'description' => $data['message'] ?? null,
        ]);
    }

    public function verifyLiqPayCallback(string $data, string $signature): bool
    {
        $privateKey = $this->getLiqPayPrivateKey();
        if (! $privateKey) {
            return false;
        }

        $expectedSignature = base64_encode(sha1($privateKey.$data.$privateKey, true));

        return hash_equals($expectedSignature, $signature);
    }

    public function processLiqPayCallback(array $decodedData): void
    {
        $orderId = $decodedData['order_id'] ?? null;
        if (! $orderId) {
            return;
        }

        $donation = OnlineDonation::where('provider_order_id', $orderId)
            ->where('church_id', $this->church->id)
            ->first();

        if (! $donation) {
            Log::warning('LiqPay callback: donation not found', ['order_id' => $orderId]);

            return;
        }

        $status = $decodedData['status'] ?? '';
        $providerResponse = [
            'liqpay_status' => $status,
            'payment_id' => $decodedData['payment_id'] ?? null,
            'amount' => $decodedData['amount'] ?? null,
        ];

        if (in_array($status, ['success', 'sandbox'])) {
            $donation->update([
                'provider_payment_id' => (string) ($decodedData['payment_id'] ?? ''),
            ]);
            $donation->markAsSuccess($providerResponse);
        } elseif (in_array($status, ['failure', 'error'])) {
            $donation->markAsFailed($decodedData['err_description'] ?? 'Payment failed', $providerResponse);
        }
    }

    protected function getLiqPayPrivateKey(): ?string
    {
        $encrypted = $this->settings['liqpay_private_key'] ?? null;
        if (! $encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            // Not encrypted (legacy plain text)
            return $encrypted;
        }
    }
}
