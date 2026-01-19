<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\DonationCampaign;
use App\Models\Transaction;
use App\Services\LiqPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    /**
     * Process donation - create LiqPay payment
     */
    public function process(Request $request, string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:100000',
            'purpose' => 'nullable|string|max:255',
            'donor_name' => 'nullable|string|max:255',
            'donor_email' => 'nullable|email|max:255',
            'is_anonymous' => 'boolean',
            'is_recurring' => 'boolean',
            'payment_method' => 'required|in:liqpay,monobank',
        ]);

        $paymentSettings = $church->payment_settings ?? [];
        $paymentMethod = $validated['payment_method'] === 'liqpay'
            ? Transaction::PAYMENT_LIQPAY
            : Transaction::PAYMENT_MONOBANK;

        // Create transaction record
        $transaction = Transaction::create([
            'church_id' => $church->id,
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_DONATION,
            'donor_name' => $validated['is_anonymous'] ?? false ? null : ($validated['donor_name'] ?? null),
            'donor_email' => $validated['donor_email'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'UAH',
            'date' => now()->toDateString(),
            'purpose' => $validated['purpose'] ?? 'Загальна пожертва',
            'status' => Transaction::STATUS_PENDING,
            'payment_method' => $paymentMethod,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'order_id' => 'DON-' . strtoupper(Str::random(12)),
        ]);

        if ($validated['payment_method'] === 'liqpay') {
            return $this->processLiqPay($church, $transaction, $paymentSettings);
        } elseif ($validated['payment_method'] === 'monobank') {
            return $this->processMonobank($church, $transaction, $paymentSettings);
        }

        return back()->with('error', 'Невідомий метод оплати');
    }

    /**
     * Process LiqPay payment
     */
    private function processLiqPay(Church $church, Transaction $transaction, array $settings)
    {
        if (empty($settings['liqpay_public_key']) || empty($settings['liqpay_private_key'])) {
            $transaction->update(['status' => Transaction::STATUS_FAILED, 'notes' => 'LiqPay не налаштовано']);
            return back()->with('error', 'LiqPay не налаштовано для цієї церкви');
        }

        $liqpay = new LiqPayService($settings['liqpay_public_key'], $settings['liqpay_private_key']);

        $callbackUrl = route('donations.callback', ['slug' => $church->slug]);
        $resultUrl = route('public.donate.thanks', ['slug' => $church->slug, 'transaction' => $transaction->id]);

        $formData = $liqpay->createPayment([
            'amount' => $transaction->amount,
            'currency' => 'UAH',
            'description' => "Пожертва для {$church->name}: {$transaction->purpose}",
            'order_id' => $transaction->order_id,
            'result_url' => $resultUrl,
            'server_url' => $callbackUrl,
            'action' => 'pay',
        ]);

        return view('public.donate-redirect', [
            'formData' => $formData,
            'church' => $church,
        ]);
    }

    /**
     * Process Monobank payment (redirect to jar)
     */
    private function processMonobank(Church $church, Transaction $transaction, array $settings)
    {
        $jarId = $settings['monobank_jar_id'] ?? null;

        if (empty($jarId)) {
            $transaction->update(['status' => Transaction::STATUS_FAILED, 'notes' => 'Monobank банка не налаштована']);
            return back()->with('error', 'Monobank банка не налаштована');
        }

        // Extract jar ID from URL if full URL provided
        if (str_contains($jarId, 'send.monobank.ua')) {
            preg_match('/jar\/([a-zA-Z0-9]+)/', $jarId, $matches);
            $jarId = $matches[1] ?? $jarId;
        }

        // For Monobank, we can't track the payment automatically
        $transaction->update(['notes' => 'Перенаправлено на Monobank']);

        $monobankUrl = "https://send.monobank.ua/jar/{$jarId}?amount={$transaction->amount}";

        return redirect()->away($monobankUrl);
    }

    /**
     * LiqPay callback (webhook)
     */
    public function callback(Request $request, string $slug)
    {
        $church = Church::where('slug', $slug)->firstOrFail();
        $paymentSettings = $church->payment_settings ?? [];

        $data = $request->input('data');
        $signature = $request->input('signature');

        if (!$data || !$signature) {
            Log::warning('LiqPay callback: missing data or signature', ['slug' => $slug]);
            return response()->json(['status' => 'error', 'message' => 'Missing data'], 400);
        }

        $liqpay = new LiqPayService(
            $paymentSettings['liqpay_public_key'] ?? '',
            $paymentSettings['liqpay_private_key'] ?? ''
        );

        if (!$liqpay->verifySignature($data, $signature)) {
            Log::warning('LiqPay callback: invalid signature', ['slug' => $slug]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $decodedData = json_decode(base64_decode($data), true);
        $orderId = $decodedData['order_id'] ?? null;
        $status = $decodedData['status'] ?? null;

        Log::info('LiqPay callback received', [
            'order_id' => $orderId,
            'status' => $status,
            'amount' => $decodedData['amount'] ?? null,
        ]);

        // Use transaction with lock to prevent race conditions
        return DB::transaction(function () use ($orderId, $status) {
            $transaction = Transaction::where('order_id', $orderId)->lockForUpdate()->first();

            if (!$transaction) {
                Log::warning('LiqPay callback: transaction not found', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            // Skip if already processed (idempotency)
            if ($transaction->status !== Transaction::STATUS_PENDING) {
                Log::info('LiqPay callback: transaction already processed', [
                    'order_id' => $orderId,
                    'current_status' => $transaction->status,
                ]);
                return response()->json(['status' => 'ok', 'message' => 'Already processed']);
            }

            // Map LiqPay status to Transaction status constants
            $newStatus = match ($status) {
                'success', 'sandbox' => Transaction::STATUS_COMPLETED,
                'failure', 'error' => Transaction::STATUS_FAILED,
                'reversed' => Transaction::STATUS_REFUNDED,
                default => Transaction::STATUS_PENDING,
            };

            $transaction->update([
                'status' => $newStatus,
                'notes' => "LiqPay status: {$status}",
                'paid_at' => $newStatus === Transaction::STATUS_COMPLETED ? now() : null,
            ]);

            return response()->json(['status' => 'ok']);
        });
    }

    /**
     * Thank you page
     */
    public function thanks(string $slug, Transaction $transaction)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        // Make sure transaction belongs to this church
        if ($transaction->church_id !== $church->id) {
            abort(404);
        }

        return view('public.donate-thanks', compact('church', 'transaction'));
    }

    // ==================== ADMIN METHODS ====================

    /**
     * Admin donations dashboard
     */
    public function index()
    {
        $church = $this->getCurrentChurch();

        $donations = Transaction::where('church_id', $church->id)
            ->where('source_type', Transaction::SOURCE_DONATION)
            ->with(['campaign'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_month' => Transaction::where('church_id', $church->id)
                ->where('source_type', Transaction::SOURCE_DONATION)
                ->where('status', Transaction::STATUS_COMPLETED)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('amount'),
            'total_year' => Transaction::where('church_id', $church->id)
                ->where('source_type', Transaction::SOURCE_DONATION)
                ->where('status', Transaction::STATUS_COMPLETED)
                ->whereYear('date', now()->year)
                ->sum('amount'),
            'transactions_count' => Transaction::where('church_id', $church->id)
                ->where('source_type', Transaction::SOURCE_DONATION)
                ->where('status', Transaction::STATUS_COMPLETED)
                ->whereYear('date', now()->year)
                ->count(),
        ];

        // Monthly chart data
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartData[] = [
                'month' => $date->translatedFormat('M'),
                'amount' => Transaction::where('church_id', $church->id)
                    ->where('source_type', Transaction::SOURCE_DONATION)
                    ->where('status', Transaction::STATUS_COMPLETED)
                    ->whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->sum('amount'),
            ];
        }

        // By purpose
        $byPurpose = Transaction::where('church_id', $church->id)
            ->where('source_type', Transaction::SOURCE_DONATION)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->whereYear('date', now()->year)
            ->selectRaw('purpose, SUM(amount) as total_amount')
            ->groupBy('purpose')
            ->orderByDesc('total_amount')
            ->get();

        $campaigns = DonationCampaign::where('church_id', $church->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('donations.index', compact('donations', 'stats', 'chartData', 'byPurpose', 'campaigns'));
    }

    /**
     * Create campaign
     */
    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'goal_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $church = $this->getCurrentChurch();

        DonationCampaign::create([
            'church_id' => $church->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'goal_amount' => $validated['goal_amount'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Кампанію створено!');
    }

    /**
     * Toggle campaign active status
     */
    public function toggleCampaign(DonationCampaign $campaign)
    {
        $church = $this->getCurrentChurch();

        if ($campaign->church_id !== $church->id) {
            abort(403);
        }

        $campaign->update(['is_active' => !$campaign->is_active]);

        return back()->with('success', $campaign->is_active ? 'Кампанію активовано!' : 'Кампанію призупинено!');
    }

    /**
     * Delete campaign
     */
    public function destroyCampaign(DonationCampaign $campaign)
    {
        $church = $this->getCurrentChurch();

        if ($campaign->church_id !== $church->id) {
            abort(403);
        }

        $campaign->delete();

        return back()->with('success', 'Кампанію видалено!');
    }

    /**
     * Generate QR code data
     */
    public function qrCode()
    {
        $church = $this->getCurrentChurch();

        if (!$church->slug || !$church->public_site_enabled) {
            return back()->with('error', 'Спочатку увімкніть публічний сайт та встановіть URL');
        }

        $donateUrl = route('public.donate', $church->slug);

        return view('donations.qr-code', compact('church', 'donateUrl'));
    }

    /**
     * Export donations report
     */
    public function export(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->input('year', now()->year);

        $transactions = Transaction::where('church_id', $church->id)
            ->where('source_type', Transaction::SOURCE_DONATION)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        // Generate CSV
        $filename = "donations-{$church->slug}-{$year}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Дата', 'Донатор', 'Email', 'Сума', 'Призначення', 'Метод', 'Статус']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->date->format('d.m.Y'),
                    $transaction->is_anonymous ? 'Анонім' : ($transaction->donor_name ?? '-'),
                    $transaction->donor_email ?? '-',
                    $transaction->amount,
                    $transaction->purpose ?? '-',
                    $transaction->payment_method_label,
                    $transaction->status_label,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
