<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Services\LiqPayService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Show billing page with current plan and usage
     */
    public function index()
    {
        $church = $this->getCurrentChurch();
        $plans = SubscriptionPlan::getActive();
        $usage = $church->getUsageStats();
        $payments = $church->payments()
            ->with('subscriptionPlan')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('billing.index', compact('church', 'plans', 'usage', 'payments'));
    }

    /**
     * Show plan upgrade page
     */
    public function upgrade(SubscriptionPlan $plan)
    {
        $church = $this->getCurrentChurch();

        if ($plan->isFree()) {
            return redirect()->route('billing.index')
                ->with('error', 'Неможливо оплатити безкоштовний план.');
        }

        return view('billing.upgrade', compact('church', 'plan'));
    }

    /**
     * Process payment
     */
    public function pay(Request $request, SubscriptionPlan $plan)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'period' => 'required|in:monthly,yearly',
        ]);

        if ($plan->isFree()) {
            return redirect()->route('billing.index')
                ->with('error', 'Неможливо оплатити безкоштовний план.');
        }

        if (!LiqPayService::isConfigured()) {
            return redirect()->route('billing.index')
                ->with('error', 'Платіжна система не налаштована. Зверніться до адміністратора.');
        }

        $liqpay = new LiqPayService();
        $result = $liqpay->createSubscriptionPayment($church, $plan, $validated['period']);

        // Return view with auto-submit form
        return view('billing.redirect', [
            'formData' => $result['form_data'],
            'checkoutUrl' => $result['checkout_url'],
        ]);
    }

    /**
     * Handle return from LiqPay
     */
    public function callback(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        if ($data && $signature) {
            $liqpay = new LiqPayService();

            if ($liqpay->verifySignature($data, $signature)) {
                $response = $liqpay->decodeData($data);
                $orderId = $response['order_id'] ?? null;

                if ($orderId) {
                    $payment = Payment::where('order_id', $orderId)->first();

                    if ($payment && $payment->isSuccess()) {
                        return redirect()->route('billing.index')
                            ->with('success', 'Оплата пройшла успішно! Вашу підписку активовано.');
                    }
                }
            }
        }

        // Check if there's a pending payment that succeeded via webhook
        $church = $this->getCurrentChurch();
        $recentPayment = $church->payments()
            ->where('status', Payment::STATUS_SUCCESS)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if ($recentPayment) {
            return redirect()->route('billing.index')
                ->with('success', 'Оплата пройшла успішно! Вашу підписку активовано.');
        }

        return redirect()->route('billing.index')
            ->with('info', 'Обробка платежу. Статус буде оновлено найближчим часом.');
    }

    /**
     * Downgrade to free plan
     */
    public function downgrade()
    {
        $church = $this->getCurrentChurch();
        $freePlan = SubscriptionPlan::free();

        if (!$freePlan) {
            return redirect()->route('billing.index')
                ->with('error', 'Безкоштовний план не знайдено.');
        }

        $church->update([
            'subscription_plan_id' => $freePlan->id,
            'subscription_ends_at' => null,
        ]);

        return redirect()->route('billing.index')
            ->with('success', 'Ви перейшли на безкоштовний план.');
    }

    /**
     * Payment history page
     */
    public function history()
    {
        $church = $this->getCurrentChurch();
        $payments = $church->payments()
            ->with('subscriptionPlan')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('billing.history', compact('church', 'payments'));
    }
}
