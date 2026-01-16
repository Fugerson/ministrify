<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $expenseCategories = $church->expenseCategories;
        $tags = $church->tags;
        $users = $church->users()->with('person')->get();
        $ministries = $church->ministries()->orderBy('name')->get();

        // Audit logs
        $auditLogs = AuditLog::where('church_id', $church->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('settings.index', compact('church', 'expenseCategories', 'tags', 'users', 'ministries', 'auditLogs'));
    }

    public function updateChurch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048',
        ]);

        $church = $this->getCurrentChurch();

        if ($request->hasFile('logo')) {
            if ($church->logo) {
                Storage::disk('public')->delete($church->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $church->update($validated);

        return back()->with('success', 'Налаштування церкви оновлено.');
    }

    public function updateTelegram(Request $request)
    {
        $validated = $request->validate([
            'telegram_bot_token' => 'nullable|string|max:100',
        ]);

        $church = $this->getCurrentChurch();
        $oldToken = $church->telegram_bot_token;
        $church->update($validated);

        // Auto-setup webhook if token was added or changed
        if ($validated['telegram_bot_token'] && $validated['telegram_bot_token'] !== $oldToken) {
            try {
                $telegram = new TelegramService($validated['telegram_bot_token']);

                // First verify the token is valid
                $botInfo = $telegram->getMe();

                // Set webhook
                $webhookUrl = url('/api/telegram/webhook');
                $webhookResult = $telegram->setWebhook($webhookUrl);

                if ($webhookResult) {
                    return back()->with('success', "Бот @{$botInfo['username']} підключено та webhook налаштовано!");
                } else {
                    return back()->with('warning', "Бот підключено, але не вдалося налаштувати webhook. Спробуйте вручну.");
                }
            } catch (\Exception $e) {
                \Log::error('Telegram setup error', ['error' => $e->getMessage()]);
                // Token saved but webhook failed - still inform user
                return back()->with('error', 'Помилка підключення. Перевірте токен та спробуйте ще раз.');
            }
        }

        return back()->with('success', 'Налаштування Telegram оновлено.');
    }

    public function testTelegram()
    {
        $church = $this->getCurrentChurch();

        if (!$church->telegram_bot_token) {
            return back()->with('error', 'Токен бота не налаштовано.');
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);
            $botInfo = $telegram->getMe();

            return back()->with('success', "Бот підключено: @{$botInfo['username']}");
        } catch (\Exception $e) {
            // Log error for debugging but don't expose to user
            \Log::error('Telegram connection error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Помилка підключення до Telegram. Перевірте токен.');
        }
    }

    public function setupWebhook()
    {
        $church = $this->getCurrentChurch();

        if (!$church->telegram_bot_token) {
            return back()->with('error', 'Спочатку введіть токен бота.');
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);

            // Get webhook URL
            $webhookUrl = url('/api/telegram/webhook');

            // Set webhook
            $result = $telegram->setWebhook($webhookUrl);

            if ($result) {
                return back()->with('success', "Webhook встановлено: {$webhookUrl}");
            } else {
                return back()->with('error', 'Не вдалося встановити webhook.');
            }
        } catch (\Exception $e) {
            \Log::error('Telegram webhook error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Помилка налаштування webhook. Перевірте токен та спробуйте ще раз.');
        }
    }

    public function getTelegramStatus()
    {
        $church = $this->getCurrentChurch();

        if (!$church->telegram_bot_token) {
            return response()->json(['connected' => false, 'error' => 'Токен не налаштовано']);
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);
            $botInfo = $telegram->getMe();

            // Get webhook info
            $response = \Illuminate\Support\Facades\Http::get(
                "https://api.telegram.org/bot{$church->telegram_bot_token}/getWebhookInfo"
            );
            $webhookInfo = $response->json()['result'] ?? null;

            return response()->json([
                'connected' => true,
                'bot_username' => $botInfo['username'],
                'bot_name' => $botInfo['first_name'],
                'webhook_url' => $webhookInfo['url'] ?? null,
                'pending_updates' => $webhookInfo['pending_update_count'] ?? 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Telegram status check error', ['error' => $e->getMessage()]);
            return response()->json(['connected' => false, 'error' => 'Не вдалося перевірити статус']);
        }
    }

    public function updateNotifications(Request $request)
    {
        $church = $this->getCurrentChurch();

        $settings = $church->settings ?? [];
        $settings['notifications'] = [
            'reminder_day_before' => $request->boolean('reminder_day_before'),
            'reminder_same_day' => $request->boolean('reminder_same_day'),
            'notify_leader_on_decline' => $request->boolean('notify_leader_on_decline'),
        ];

        $church->update(['settings' => $settings]);

        return back()->with('success', 'Налаштування сповіщень оновлено.');
    }

    public function updatePublicSite(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'slug' => 'required|string|max:50|alpha_dash|unique:churches,slug,' . $church->id,
            'public_site_enabled' => 'boolean',
            'public_description' => 'nullable|string|max:1000',
            'public_email' => 'nullable|email|max:255',
            'public_phone' => 'nullable|string|max:20',
            'website_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'service_times' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|max:4096',
            'pastor_name' => 'nullable|string|max:255',
            'pastor_photo' => 'nullable|image|max:2048',
            'pastor_message' => 'nullable|string|max:2000',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['public_site_enabled'] = $request->boolean('public_site_enabled');

        if ($request->hasFile('cover_image')) {
            if ($church->cover_image) {
                Storage::disk('public')->delete($church->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('pastor_photo')) {
            if ($church->pastor_photo) {
                Storage::disk('public')->delete($church->pastor_photo);
            }
            $validated['pastor_photo'] = $request->file('pastor_photo')->store('pastors', 'public');
        }

        $church->update($validated);

        return back()->with('success', 'Налаштування публічного сайту оновлено.');
    }

    /**
     * Update payment settings (LiqPay, Monobank)
     */
    public function updatePaymentSettings(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'liqpay_enabled' => 'boolean',
            'liqpay_public_key' => 'nullable|string|max:255',
            'liqpay_private_key' => 'nullable|string|max:255',
            'monobank_enabled' => 'boolean',
            'monobank_jar_id' => 'nullable|string|max:255',
        ]);

        $church->update([
            'payment_settings' => [
                'liqpay_enabled' => $validated['liqpay_enabled'] ?? false,
                'liqpay_public_key' => $validated['liqpay_public_key'] ?? null,
                'liqpay_private_key' => $validated['liqpay_private_key'] ?? null,
                'monobank_enabled' => $validated['monobank_enabled'] ?? false,
                'monobank_jar_id' => $validated['monobank_jar_id'] ?? null,
            ],
        ]);

        return back()->with('success', 'Налаштування платежів оновлено.');
    }

    /**
     * Update theme color (accent color)
     */
    public function updateThemeColor(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $church = $this->getCurrentChurch();
        $church->update(['primary_color' => $validated['primary_color']]);

        return back();
    }

    /**
     * Update design theme (overall UI style)
     */
    public function updateDesignTheme(Request $request)
    {
        $validated = $request->validate([
            'design_theme' => 'required|string|in:modern,minimal,brutalist,glass,neumorphism,corporate,playful',
        ]);

        $church = $this->getCurrentChurch();
        $church->update(['design_theme' => $validated['design_theme']]);

        return back()->with('success', 'Стиль дизайну оновлено!');
    }

    /**
     * Update finance settings (initial balance)
     */
    public function updateFinance(Request $request)
    {
        $validated = $request->validate([
            'initial_balance' => 'required|numeric|min:0',
            'initial_balance_date' => 'required|date',
        ]);

        $church = $this->getCurrentChurch();
        $church->update([
            'initial_balance' => $validated['initial_balance'],
            'initial_balance_date' => $validated['initial_balance_date'],
        ]);

        return back()->with('success', 'Початковий баланс оновлено.');
    }

    /**
     * Update currency settings
     */
    public function updateCurrencies(Request $request)
    {
        $validated = $request->validate([
            'currencies' => 'required|array|min:1',
            'currencies.*' => 'in:UAH,USD,EUR',
        ]);

        // UAH is always required
        $currencies = collect($validated['currencies'])->unique()->values()->toArray();
        if (!in_array('UAH', $currencies)) {
            array_unshift($currencies, 'UAH');
        }

        $church = $this->getCurrentChurch();
        $church->update([
            'enabled_currencies' => $currencies,
        ]);

        return back()->with('success', 'Налаштування валют оновлено.');
    }
}
