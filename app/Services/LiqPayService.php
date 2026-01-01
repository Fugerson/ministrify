<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Http;

class LiqPayService
{
    private string $publicKey;
    private string $privateKey;
    private string $apiUrl = 'https://www.liqpay.ua/api/3/checkout';

    public function __construct(?string $publicKey = null, ?string $privateKey = null)
    {
        $this->publicKey = $publicKey ?? config('services.liqpay.public_key', '');
        $this->privateKey = $privateKey ?? config('services.liqpay.private_key', '');
    }

    /**
     * Create payment form data for subscription
     */
    public function createSubscriptionPayment(Church $church, SubscriptionPlan $plan, string $period = 'monthly'): array
    {
        $amount = $period === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $amountUah = $amount / 100;

        $orderId = Payment::generateOrderId();

        // Create pending payment record
        $payment = Payment::create([
            'church_id' => $church->id,
            'subscription_plan_id' => $plan->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => "Підписка {$plan->name} ({$period === 'yearly' ? 'річна' : 'місячна'}) - {$church->name}",
            'status' => Payment::STATUS_PENDING,
            'type' => Payment::TYPE_SUBSCRIPTION,
            'period' => $period,
        ]);

        $formData = $this->createPayment([
            'amount' => $amountUah,
            'description' => $payment->description,
            'order_id' => $orderId,
            'result_url' => route('billing.callback'),
            'server_url' => route('api.liqpay.webhook'),
        ]);

        return [
            'payment' => $payment,
            'form_data' => $formData,
            'checkout_url' => $this->apiUrl,
        ];
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
     * Process webhook callback from LiqPay
     */
    public function processCallback(string $data, string $signature): ?Payment
    {
        // Verify signature
        if (!$this->verifySignature($data, $signature)) {
            logger()->warning('LiqPay: Invalid signature');
            return null;
        }

        $response = $this->decodeData($data);

        if (!$response || !isset($response['order_id'])) {
            logger()->warning('LiqPay: Invalid response data');
            return null;
        }

        $payment = Payment::where('order_id', $response['order_id'])->first();

        if (!$payment) {
            logger()->warning('LiqPay: Payment not found', ['order_id' => $response['order_id']]);
            return null;
        }

        // Update payment with LiqPay data
        $payment->update([
            'liqpay_order_id' => $response['liqpay_order_id'] ?? null,
            'liqpay_payment_id' => $response['payment_id'] ?? null,
            'liqpay_data' => $response,
        ]);

        // Process based on status
        $status = $response['status'] ?? '';

        if (in_array($status, ['success', 'sandbox'])) {
            $this->handleSuccessfulPayment($payment);
        } elseif (in_array($status, ['failure', 'error'])) {
            $payment->update(['status' => Payment::STATUS_FAILURE]);
        } elseif ($status === 'reversed') {
            $payment->update(['status' => Payment::STATUS_REVERSED]);
        }

        return $payment;
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment(Payment $payment): void
    {
        $payment->update([
            'status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);

        // Activate subscription
        $church = $payment->church;
        $plan = $payment->subscriptionPlan;

        if ($church && $plan) {
            $church->upgradeToPlan($plan, $payment->period ?? 'monthly');
        }
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

        $response = Http::asForm()->post('https://www.liqpay.ua/api/request', [
            'data' => $base64Data,
            'signature' => $signature,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Check if LiqPay is configured
     */
    public static function isConfigured(): bool
    {
        return !empty(config('services.liqpay.public_key'))
            && !empty(config('services.liqpay.private_key'));
    }
}
