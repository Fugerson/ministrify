<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Services\LiqPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    /**
     * Public donation page
     */
    public function publicPage(string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $campaigns = DonationCampaign::where('church_id', $church->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($campaign) {
                $campaign->raised = Donation::where('church_id', $campaign->church_id)
                    ->where('status', 'completed')
                    ->where('purpose', $campaign->name)
                    ->sum('amount');
                $campaign->progress = $campaign->goal_amount > 0
                    ? min(100, round(($campaign->raised / $campaign->goal_amount) * 100))
                    : 0;
                $campaign->donors_count = Donation::where('church_id', $campaign->church_id)
                    ->where('status', 'completed')
                    ->where('purpose', $campaign->name)
                    ->distinct('donor_email')
                    ->count('donor_email');
                return $campaign;
            });

        $paymentSettings = $church->payment_settings ?? [];

        return view('public.donate', compact('church', 'campaigns', 'paymentSettings'));
    }

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

        // Create donation record
        $donation = Donation::create([
            'church_id' => $church->id,
            'donor_name' => $validated['is_anonymous'] ?? false ? null : ($validated['donor_name'] ?? null),
            'donor_email' => $validated['donor_email'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'UAH',
            'type' => ($validated['is_recurring'] ?? false) ? 'recurring' : 'one_time',
            'purpose' => $validated['purpose'] ?? 'Загальна пожертва',
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'transaction_id' => 'DON-' . strtoupper(Str::random(12)),
        ]);

        if ($validated['payment_method'] === 'liqpay') {
            return $this->processLiqPay($church, $donation, $paymentSettings);
        } elseif ($validated['payment_method'] === 'monobank') {
            return $this->processMonobank($church, $donation, $paymentSettings);
        }

        return back()->with('error', 'Невідомий метод оплати');
    }

    /**
     * Process LiqPay payment
     */
    private function processLiqPay(Church $church, Donation $donation, array $settings)
    {
        if (empty($settings['liqpay_public_key']) || empty($settings['liqpay_private_key'])) {
            $donation->update(['status' => 'failed', 'notes' => 'LiqPay не налаштовано']);
            return back()->with('error', 'LiqPay не налаштовано для цієї церкви');
        }

        $liqpay = new LiqPayService($settings['liqpay_public_key'], $settings['liqpay_private_key']);

        $callbackUrl = route('donations.callback', ['slug' => $church->slug]);
        $resultUrl = route('public.donate.thanks', ['slug' => $church->slug, 'donation' => $donation->id]);

        $formData = $liqpay->createPayment([
            'amount' => $donation->amount,
            'currency' => 'UAH',
            'description' => "Пожертва для {$church->name}: {$donation->purpose}",
            'order_id' => $donation->transaction_id,
            'result_url' => $resultUrl,
            'server_url' => $callbackUrl,
            'action' => $donation->type === 'recurring' ? 'subscribe' : 'pay',
        ]);

        return view('public.donate-redirect', [
            'formData' => $formData,
            'church' => $church,
        ]);
    }

    /**
     * Process Monobank payment (redirect to jar)
     */
    private function processMonobank(Church $church, Donation $donation, array $settings)
    {
        $jarId = $settings['monobank_jar_id'] ?? null;

        if (empty($jarId)) {
            $donation->update(['status' => 'failed', 'notes' => 'Monobank банка не налаштована']);
            return back()->with('error', 'Monobank банка не налаштована');
        }

        // Extract jar ID from URL if full URL provided
        if (str_contains($jarId, 'send.monobank.ua')) {
            preg_match('/jar\/([a-zA-Z0-9]+)/', $jarId, $matches);
            $jarId = $matches[1] ?? $jarId;
        }

        // For Monobank, we can't track the payment automatically
        // Mark as pending and redirect to jar
        $donation->update(['notes' => 'Перенаправлено на Monobank']);

        $monobankUrl = "https://send.monobank.ua/jar/{$jarId}?amount={$donation->amount}";

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

        $donation = Donation::where('transaction_id', $orderId)->first();

        if (!$donation) {
            Log::warning('LiqPay callback: donation not found', ['order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => 'Donation not found'], 404);
        }

        // Map LiqPay status to our status
        $statusMap = [
            'success' => 'completed',
            'sandbox' => 'completed', // For testing
            'failure' => 'failed',
            'error' => 'failed',
            'reversed' => 'refunded',
            'processing' => 'pending',
            'wait_accept' => 'pending',
        ];

        $newStatus = $statusMap[$status] ?? 'pending';

        $donation->update([
            'status' => $newStatus,
            'notes' => "LiqPay status: {$status}",
        ]);

        // TODO: Send email notification

        return response()->json(['status' => 'ok']);
    }

    /**
     * Thank you page
     */
    public function thanks(string $slug, Donation $donation)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        // Make sure donation belongs to this church
        if ($donation->church_id !== $church->id) {
            abort(404);
        }

        return view('public.donate-thanks', compact('church', 'donation'));
    }

    // ==================== ADMIN METHODS ====================

    /**
     * Admin donations dashboard
     */
    public function index()
    {
        $church = $this->getCurrentChurch();

        $donations = Donation::where('church_id', $church->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_month' => Donation::where('church_id', $church->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_year' => Donation::where('church_id', $church->id)
                ->where('status', 'completed')
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'donors_count' => Donation::where('church_id', $church->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->distinct('donor_email')
                ->count('donor_email'),
            'recurring_count' => Donation::where('church_id', $church->id)
                ->where('status', 'completed')
                ->where('type', 'recurring')
                ->count(),
            'avg_donation' => Donation::where('church_id', $church->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->avg('amount') ?? 0,
        ];

        // Monthly chart data
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartData[] = [
                'month' => $date->translatedFormat('M'),
                'amount' => Donation::where('church_id', $church->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
            ];
        }

        // Top donors
        $topDonors = Donation::where('church_id', $church->id)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereNotNull('donor_email')
            ->where('is_anonymous', false)
            ->selectRaw('donor_name, donor_email, SUM(amount) as total_amount, COUNT(*) as donations_count')
            ->groupBy('donor_email', 'donor_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // By purpose
        $byPurpose = Donation::where('church_id', $church->id)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->selectRaw('purpose, SUM(amount) as total_amount')
            ->groupBy('purpose')
            ->orderByDesc('total_amount')
            ->get();

        $campaigns = DonationCampaign::where('church_id', $church->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('donations.index', compact('donations', 'stats', 'chartData', 'topDonors', 'byPurpose', 'campaigns'));
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

        $donations = Donation::where('church_id', $church->id)
            ->where('status', 'completed')
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate CSV
        $filename = "donations-{$church->slug}-{$year}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($donations) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Дата', 'Донатор', 'Email', 'Сума', 'Призначення', 'Метод', 'Статус']);

            foreach ($donations as $donation) {
                fputcsv($file, [
                    $donation->created_at->format('d.m.Y H:i'),
                    $donation->is_anonymous ? 'Анонім' : ($donation->donor_name ?? '-'),
                    $donation->donor_email ?? '-',
                    $donation->amount,
                    $donation->purpose ?? '-',
                    $donation->payment_method,
                    $donation->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
