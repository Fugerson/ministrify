<?php

namespace App\Services;

use App\Models\Church;
use App\Models\DonationCampaign;
use App\Models\Transaction;
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

        // Create transaction record (unified system)
        $transaction = Transaction::create([
            'church_id' => $this->church->id,
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_DONATION,
            'order_id' => $orderId,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'UAH',
            'date' => now()->toDateString(),
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'description' => $data['message'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'payment_method' => Transaction::PAYMENT_LIQPAY,
            'status' => Transaction::STATUS_PENDING,
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
            'transaction_id' => $transaction->id,
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
    public function processLiqPayCallback(array $data): ?Transaction
    {
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            return null;
        }

        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            return null;
        }

        $status = match ($data['status'] ?? '') {
            'success', 'sandbox' => Transaction::STATUS_COMPLETED,
            'failure', 'error' => Transaction::STATUS_FAILED,
            'reversed' => Transaction::STATUS_REFUNDED,
            default => Transaction::STATUS_PENDING,
        };

        $transaction->update([
            'status' => $status,
            'transaction_id' => $data['payment_id'] ?? null,
            'payment_data' => $data,
            'paid_at' => $status === Transaction::STATUS_COMPLETED ? now() : null,
        ]);

        if ($status === Transaction::STATUS_COMPLETED) {
            $this->onTransactionCompleted($transaction);
        }

        return $transaction;
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
    public function createMonobankPayment(array $data): Transaction
    {
        $orderId = 'mono_' . Str::uuid();

        return Transaction::create([
            'church_id' => $this->church->id,
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_DONATION,
            'order_id' => $orderId,
            'amount' => $data['amount'],
            'currency' => 'UAH',
            'date' => now()->toDateString(),
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'description' => $data['message'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'payment_method' => Transaction::PAYMENT_MONOBANK,
            'status' => Transaction::STATUS_PENDING, // Will be updated manually or via webhook
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
     * Actions when transaction is completed
     */
    protected function onTransactionCompleted(Transaction $transaction): void
    {
        // Update campaign progress if applicable
        if ($transaction->campaign_id) {
            $campaign = $transaction->campaign;
            if ($campaign) {
                $campaign->updateCollectedAmount();
            }
        }

        // Log transaction
        Log::channel('security')->info('Donation transaction completed', [
            'church_id' => $transaction->church_id,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method,
        ]);

        // Could also trigger notifications here
    }

    /**
     * Get donation statistics
     */
    public function getStatistics(?string $period = null): array
    {
        $query = Transaction::where('church_id', $this->church->id)
            ->where('source_type', Transaction::SOURCE_DONATION)
            ->where('status', Transaction::STATUS_COMPLETED);

        if ($period === 'month') {
            $query->whereMonth('paid_at', now()->month)
                  ->whereYear('paid_at', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('paid_at', now()->year);
        }

        $transactions = $query->get();

        return [
            'total_amount' => $transactions->sum('amount'),
            'count' => $transactions->count(),
            'average' => $transactions->count() > 0 ? round($transactions->avg('amount'), 2) : 0,
            'by_method' => $transactions->groupBy('payment_method')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ]),
            'by_campaign' => $transactions->where('campaign_id', '!=', null)
                ->groupBy('campaign_id')
                ->map(fn($group) => $group->sum('amount')),
        ];
    }
}
