<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Donation;
use App\Models\DonationCampaign;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    protected Church $church;
    protected array $config;

    public function __construct(Church $church)
    {
        $this->church = $church;
        $this->config = [
            'liqpay' => [
                'public_key' => $church->payment_settings['liqpay_public_key'] ?? null,
                'private_key' => $church->payment_settings['liqpay_private_key'] ?? null,
                'enabled' => !empty($church->payment_settings['liqpay_enabled']),
            ],
            'monobank' => [
                'token' => $church->payment_settings['monobank_token'] ?? null,
                'jar_id' => $church->payment_settings['monobank_jar_id'] ?? null,
                'enabled' => !empty($church->payment_settings['monobank_enabled']),
            ],
        ];
    }

    /**
     * Check if any payment method is available
     */
    public function isAvailable(): bool
    {
        return $this->isLiqPayAvailable() || $this->isMonobankAvailable();
    }

    /**
     * Check if LiqPay is configured
     */
    public function isLiqPayAvailable(): bool
    {
        return $this->config['liqpay']['enabled']
            && !empty($this->config['liqpay']['public_key'])
            && !empty($this->config['liqpay']['private_key']);
    }

    /**
     * Check if Monobank is configured
     */
    public function isMonobankAvailable(): bool
    {
        return $this->config['monobank']['enabled']
            && !empty($this->config['monobank']['jar_id']);
    }

    /**
     * Create LiqPay payment form data
     */
    public function createLiqPayPayment(array $data): array
    {
        if (!$this->isLiqPayAvailable()) {
            throw new \Exception('LiqPay is not configured');
        }

        $orderId = 'donation_' . Str::uuid();

        // Create donation record
        $donation = Donation::create([
            'church_id' => $this->church->id,
            'order_id' => $orderId,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'UAH',
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'message' => $data['message'] ?? null,
            'payment_method' => 'liqpay',
            'status' => 'pending',
        ]);

        $paymentData = [
            'version' => 3,
            'public_key' => $this->config['liqpay']['public_key'],
            'action' => 'pay',
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'UAH',
            'description' => $this->getPaymentDescription($data),
            'order_id' => $orderId,
            'result_url' => route('public.donate.success', $this->church->slug),
            'server_url' => route('api.webhooks.liqpay'),
        ];

        $dataEncoded = base64_encode(json_encode($paymentData));
        $signature = $this->generateLiqPaySignature($dataEncoded);

        return [
            'data' => $dataEncoded,
            'signature' => $signature,
            'donation_id' => $donation->id,
        ];
    }

    /**
     * Generate LiqPay signature
     */
    protected function generateLiqPaySignature(string $data): string
    {
        $privateKey = $this->config['liqpay']['private_key'];
        return base64_encode(sha1($privateKey . $data . $privateKey, true));
    }

    /**
     * Verify LiqPay callback
     */
    public function verifyLiqPayCallback(string $data, string $signature): bool
    {
        $expectedSignature = $this->generateLiqPaySignature($data);
        return $signature === $expectedSignature;
    }

    /**
     * Process LiqPay callback
     */
    public function processLiqPayCallback(array $data): ?Donation
    {
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            return null;
        }

        $donation = Donation::where('order_id', $orderId)->first();

        if (!$donation) {
            return null;
        }

        $status = match ($data['status'] ?? '') {
            'success', 'sandbox' => 'completed',
            'failure', 'error' => 'failed',
            'reversed' => 'refunded',
            default => 'pending',
        };

        $donation->update([
            'status' => $status,
            'payment_id' => $data['payment_id'] ?? null,
            'payment_data' => $data,
            'paid_at' => $status === 'completed' ? now() : null,
        ]);

        if ($status === 'completed') {
            $this->onDonationCompleted($donation);
        }

        return $donation;
    }

    /**
     * Get Monobank jar link
     */
    public function getMonobankJarLink(): ?string
    {
        if (!$this->isMonobankAvailable()) {
            return null;
        }

        $jarId = $this->config['monobank']['jar_id'];

        // If it's already a full URL, return as is
        if (Str::startsWith($jarId, 'https://')) {
            return $jarId;
        }

        // Build mono.ua jar link
        return "https://send.monobank.ua/{$jarId}";
    }

    /**
     * Create Monobank payment record
     */
    public function createMonobankPayment(array $data): Donation
    {
        $orderId = 'mono_' . Str::uuid();

        return Donation::create([
            'church_id' => $this->church->id,
            'order_id' => $orderId,
            'amount' => $data['amount'],
            'currency' => 'UAH',
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'message' => $data['message'] ?? null,
            'payment_method' => 'monobank',
            'status' => 'pending', // Will be updated manually or via webhook
        ]);
    }

    /**
     * Get payment description
     */
    protected function getPaymentDescription(array $data): string
    {
        $churchName = $this->church->name;

        if (!empty($data['campaign_id'])) {
            $campaign = DonationCampaign::find($data['campaign_id']);
            if ($campaign) {
                return "Пожертва на {$campaign->name} - {$churchName}";
            }
        }

        return "Пожертва для {$churchName}";
    }

    /**
     * Actions when donation is completed
     */
    protected function onDonationCompleted(Donation $donation): void
    {
        // Update campaign progress if applicable
        if ($donation->campaign_id) {
            $campaign = $donation->campaign;
            if ($campaign) {
                $campaign->updateCollectedAmount();
            }
        }

        // Log donation
        Log::channel('security')->info('Donation completed', [
            'church_id' => $donation->church_id,
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'payment_method' => $donation->payment_method,
        ]);

        // Could also trigger notifications here
    }

    /**
     * Get donation statistics
     */
    public function getStatistics(?string $period = null): array
    {
        $query = Donation::where('church_id', $this->church->id)
            ->where('status', 'completed');

        if ($period === 'month') {
            $query->whereMonth('paid_at', now()->month)
                  ->whereYear('paid_at', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('paid_at', now()->year);
        }

        $donations = $query->get();

        return [
            'total_amount' => $donations->sum('amount'),
            'count' => $donations->count(),
            'average' => $donations->count() > 0 ? round($donations->avg('amount'), 2) : 0,
            'by_method' => $donations->groupBy('payment_method')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ]),
            'by_campaign' => $donations->where('campaign_id', '!=', null)
                ->groupBy('campaign_id')
                ->map(fn($group) => $group->sum('amount')),
        ];
    }
}
