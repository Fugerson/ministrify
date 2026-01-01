<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LiqPayService;
use Illuminate\Http\Request;

class LiqPayWebhookController extends Controller
{
    /**
     * Handle LiqPay webhook callback
     */
    public function handle(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        if (!$data || !$signature) {
            logger()->warning('LiqPay webhook: Missing data or signature');
            return response()->json(['status' => 'error', 'message' => 'Missing data'], 400);
        }

        $liqpay = new LiqPayService();
        $payment = $liqpay->processCallback($data, $signature);

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 400);
        }

        logger()->info('LiqPay webhook processed', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'status' => $payment->status,
        ]);

        return response()->json(['status' => 'ok']);
    }
}
