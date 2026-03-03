<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Person;
use App\Models\TransactionCategory;
use App\Services\ImageService;
use App\Services\NbuExchangeRateService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index()
    {
        $church = $this->getCurrentChurch();
        $tags = $church->tags;
        $ministries = $church->ministries()->orderBy('name')->get();

        // Transaction categories (unified)
        $transactionCategories = TransactionCategory::where('church_id', $church->id)
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        // Audit logs
        $auditLogs = AuditLog::where('church_id', $church->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // Church roles with permissions (loaded before $users — needed for pivot resolution)
        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->withCount(['users as people_count'])
            ->with('permissions')
            ->orderBy('sort_order')
            ->get();
        $churchRolesById = $churchRoles->keyBy('id');

        // Load users via pivot table for multi-church support
        $users = $church->members()
            ->orderBy('name')
            ->get();

        // Bulk load persons for this church
        $personsByUserId = Person::where('church_id', $church->id)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $users->each(function ($user) use ($churchRolesById, $personsByUserId) {
            // Set person for this specific church from pivot
            $user->setRelation('person', $personsByUserId->get($user->id));
            // Set church_role_id from pivot (not from user's active church)
            $pivotRoleId = $user->pivot->church_role_id;
            $user->church_role_id = $pivotRoleId;
            $user->setRelation('churchRole',
                $pivotRoleId ? ($churchRolesById[$pivotRoleId] ?? null) : null
            );
            // Load permission overrides from pivot for this church
            $pivotOverrides = $user->pivot->permission_overrides;
            if ($pivotOverrides) {
                $user->permission_overrides = is_string($pivotOverrides)
                    ? json_decode($pivotOverrides, true)
                    : $pivotOverrides;
            }
        });

        $rolesJson = $churchRoles->map(fn($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'color' => $role->color,
            'is_admin_role' => $role->is_admin_role,
            'is_default' => $role->is_default,
            'people_count' => $role->people_count,
        ]);

        $permissionModules = ChurchRolePermission::MODULES;
        $permissionActions = ChurchRolePermission::ACTIONS;

        // Available people for person linking (pending user approvals)
        $availablePeople = Person::where('church_id', $church->id)
            ->whereNull('user_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        // Find potential matches for pending users
        $potentialMatches = [];
        foreach ($users->filter(fn($u) => !$u->church_role_id) as $u) {
            $matches = $this->findPersonMatches($u, $availablePeople);
            if ($matches->isNotEmpty()) {
                $potentialMatches[$u->id] = $matches;
            }
        }

        return view('settings.index', compact(
            'church', 'tags', 'users', 'ministries',
            'transactionCategories', 'auditLogs', 'churchRoles', 'rolesJson',
            'permissionModules', 'permissionActions',
            'availablePeople', 'potentialMatches'
        ));
    }

    public function updateChurch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
        ]);

        $church = $this->getCurrentChurch();

        if ($request->hasFile('logo')) {
            $this->imageService->delete($church->logo);
            $validated['logo'] = $this->imageService->store($request->file('logo'), 'logos', 300);
        }

        $church->update($validated);

        // Log settings update
        $this->logAuditAction('settings_updated', 'Church', $church->id, $church->name, [
            'updated_fields' => array_keys($validated),
        ]);

        return $this->successResponse($request, 'Налаштування церкви оновлено.');
    }

    public function updateTelegram(Request $request)
    {
        // Telegram bot is now configured globally via .env
        // This method is kept for backwards compatibility
        return $this->successResponse(request(), 'Telegram бот налаштовується централізовано адміністратором системи.');
    }

    public function testTelegram()
    {
        if (!config('services.telegram.bot_token')) {
            return $this->errorResponse(request(), 'Telegram бот не налаштовано в системі.');
        }

        try {
            $telegram = TelegramService::make();
            $botInfo = $telegram->getMe();

            return $this->successResponse(request(), "Бот підключено: @{$botInfo['username']}");
        } catch (\Exception $e) {
            \Log::error('Telegram connection error', ['error' => $e->getMessage()]);
            return $this->errorResponse(request(), 'Помилка підключення до Telegram.');
        }
    }

    public function setupWebhook()
    {
        if (!config('services.telegram.bot_token')) {
            return $this->errorResponse(request(), 'Telegram бот не налаштовано в системі.');
        }

        try {
            $telegram = TelegramService::make();
            $webhookUrl = url('/api/telegram/webhook');
            $result = $telegram->setWebhook($webhookUrl);

            if ($result) {
                return $this->successResponse(request(), "Webhook встановлено: {$webhookUrl}");
            } else {
                return $this->errorResponse(request(), 'Не вдалося встановити webhook.');
            }
        } catch (\Exception $e) {
            \Log::error('Telegram webhook error', ['error' => $e->getMessage()]);
            return $this->errorResponse(request(), 'Помилка налаштування webhook.');
        }
    }

    public function getTelegramStatus()
    {
        $token = config('services.telegram.bot_token');

        if (!$token) {
            return response()->json(['connected' => false, 'error' => 'Бот не налаштовано']);
        }

        try {
            $telegram = TelegramService::make();
            $botInfo = $telegram->getMe();

            $response = \Illuminate\Support\Facades\Http::get(
                "https://api.telegram.org/bot{$token}/getWebhookInfo"
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
        $currentNotifications = $settings['notifications'] ?? [];

        $settings['notifications'] = [
            'notify_on_assignment' => $request->has('notify_on_assignment') ? $request->boolean('notify_on_assignment') : ($currentNotifications['notify_on_assignment'] ?? true),
            'notify_on_responsibility' => $request->has('notify_on_responsibility') ? $request->boolean('notify_on_responsibility') : ($currentNotifications['notify_on_responsibility'] ?? true),
            'notify_on_plan_request' => $request->has('notify_on_plan_request') ? $request->boolean('notify_on_plan_request') : ($currentNotifications['notify_on_plan_request'] ?? true),
            'notify_leader_on_decline' => $request->has('notify_leader_on_decline') ? $request->boolean('notify_leader_on_decline') : ($currentNotifications['notify_leader_on_decline'] ?? true),
            'birthday_reminders' => $request->has('birthday_reminders') ? $request->boolean('birthday_reminders') : ($currentNotifications['birthday_reminders'] ?? true),
            'reminder_day_before' => $request->has('reminder_day_before') ? $request->boolean('reminder_day_before') : ($currentNotifications['reminder_day_before'] ?? true),
            'reminder_same_day' => $request->has('reminder_same_day') ? $request->boolean('reminder_same_day') : ($currentNotifications['reminder_same_day'] ?? true),
            'task_reminders' => $request->has('task_reminders') ? $request->boolean('task_reminders') : ($currentNotifications['task_reminders'] ?? true),
        ];

        $church->update(['settings' => $settings]);

        // Log notification settings update
        $this->logAuditAction('notification_settings_updated', 'Church', $church->id, $church->name, [
            'notifications' => $settings['notifications'],
        ]);

        return $this->successResponse($request, 'Налаштування сповіщень оновлено.');
    }

    public function updateSelfRegistration(Request $request)
    {
        $church = $this->getCurrentChurch();

        $settings = $church->settings ?? [];
        $settings['self_registration_enabled'] = $request->boolean('enabled');

        $church->update(['settings' => $settings]);

        // Log self registration setting
        $this->logAuditAction('settings_updated', 'Church', $church->id, $church->name, [
            'self_registration_enabled' => $settings['self_registration_enabled'],
        ]);

        return response()->json(['success' => true]);
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
            'cover_image' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:4096',
            'pastor_name' => 'nullable|string|max:255',
            'pastor_photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'pastor_message' => 'nullable|string|max:2000',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['public_site_enabled'] = $request->boolean('public_site_enabled');

        if ($request->hasFile('cover_image')) {
            $this->imageService->delete($church->cover_image);
            $validated['cover_image'] = $this->imageService->store($request->file('cover_image'), 'covers', 1200);
        }

        if ($request->hasFile('pastor_photo')) {
            $this->imageService->delete($church->pastor_photo);
            $validated['pastor_photo'] = $this->imageService->storeProfilePhoto($request->file('pastor_photo'), 'pastors');
        }

        $church->update($validated);

        // Log public site settings update
        $this->logAuditAction('public_site_updated', 'Church', $church->id, $church->name, [
            'public_site_enabled' => $validated['public_site_enabled'],
            'slug' => $validated['slug'],
        ]);

        return $this->successResponse($request, 'Налаштування публічного сайту оновлено.');
    }

    /**
     * Update payment settings (LiqPay, Monobank)
     */
    public function updatePaymentSettings(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'liqpay_enabled' => 'boolean',
            'liqpay_public_key' => 'required_if:liqpay_enabled,1|nullable|string|max:255',
            'liqpay_private_key' => 'nullable|string|max:255',
            'monobank_enabled' => 'boolean',
            'monobank_jar_id' => 'required_if:monobank_enabled,1|nullable|string|max:255',
        ]);

        $currentSettings = $church->payment_settings ?? [];

        // Handle private key: if placeholder sent, keep existing encrypted value
        $privateKey = $validated['liqpay_private_key'] ?? null;
        if ($privateKey === '********') {
            // User didn't change the key — keep existing
            $privateKey = $currentSettings['liqpay_private_key'] ?? null;
        } elseif ($privateKey) {
            // New key provided — encrypt it
            $privateKey = Crypt::encryptString($privateKey);
        }

        // Validate private key is required when enabling LiqPay
        if (($validated['liqpay_enabled'] ?? false) && !$privateKey && !($currentSettings['liqpay_private_key'] ?? null)) {
            return back()->withErrors(['liqpay_private_key' => __('validation.required_if', ['attribute' => 'Private Key', 'other' => 'LiqPay', 'value' => 'увімкнено'])]);
        }

        $church->update([
            'payment_settings' => [
                'liqpay_enabled' => $validated['liqpay_enabled'] ?? false,
                'liqpay_public_key' => $validated['liqpay_public_key'] ?? null,
                'liqpay_private_key' => $privateKey,
                'monobank_enabled' => $validated['monobank_enabled'] ?? false,
                'monobank_jar_id' => $validated['monobank_jar_id'] ?? null,
            ],
        ]);

        // Log payment settings update
        $this->logAuditAction('payment_settings_updated', 'Church', $church->id, $church->name, [
            'liqpay_enabled' => $validated['liqpay_enabled'] ?? false,
            'monobank_enabled' => $validated['monobank_enabled'] ?? false,
        ]);

        return $this->successResponse($request, 'Налаштування платежів оновлено.');
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
        $oldColor = $church->primary_color;
        $church->update(['primary_color' => $validated['primary_color']]);

        // Log theme color change
        $this->logAuditAction('theme_updated', 'Church', $church->id, $church->name, [
            'primary_color' => $validated['primary_color'],
        ], [
            'primary_color' => $oldColor,
        ]);

        return back();
    }

    /**
     * Update design theme (overall UI style)
     */
    public function updateDesignTheme(Request $request)
    {
        $validated = $request->validate([
            'design_theme' => 'required|string|in:modern,minimal,brutalist,glass,neumorphism,corporate,playful,ocean,sunset',
        ]);

        $church = $this->getCurrentChurch();
        $oldTheme = $church->design_theme;
        $church->update(['design_theme' => $validated['design_theme']]);

        // Log theme change
        $this->logAuditAction('theme_updated', 'Church', $church->id, $church->name, [
            'design_theme' => $validated['design_theme'],
        ], [
            'design_theme' => $oldTheme,
        ]);

        return $this->successResponse($request, 'Стиль дизайну оновлено!');
    }

    public function updateMenuPosition(Request $request)
    {
        $validated = $request->validate([
            'menu_position' => 'required|string|in:left,right,top,bottom',
        ]);

        $church = $this->getCurrentChurch();
        $oldPosition = $church->menu_position;
        $church->update(['menu_position' => $validated['menu_position']]);

        // Log menu position change
        $this->logAuditAction('theme_updated', 'Church', $church->id, $church->name, [
            'menu_position' => $validated['menu_position'],
        ], [
            'menu_position' => $oldPosition,
        ]);

        return $this->successResponse($request, 'Позицію меню оновлено!');
    }

    /**
     * Update finance settings (initial balance - multi-currency)
     */
    public function updateFinance(Request $request)
    {
        $validated = $request->validate([
            'initial_balances' => 'required|array',
            'initial_balances.UAH' => 'nullable|numeric|min:0',
            'initial_balances.USD' => 'nullable|numeric|min:0',
            'initial_balances.EUR' => 'nullable|numeric|min:0',
            'initial_balance_date' => 'required|date',
        ]);

        $church = $this->getCurrentChurch();

        // Filter out zero/null values and build the balances array
        $balances = [];
        foreach ($validated['initial_balances'] as $currency => $amount) {
            if ($amount && $amount > 0) {
                $balances[$currency] = (float) $amount;
            }
        }

        // Also update legacy field for backwards compatibility
        $uahBalance = $balances['UAH'] ?? 0;

        $oldBalances = $church->initial_balances;
        $oldDate = $church->initial_balance_date;

        $church->update([
            'initial_balances' => $balances ?: null,
            'initial_balance' => $uahBalance,
            'initial_balance_date' => $validated['initial_balance_date'],
        ]);

        // Log finance settings update
        $this->logAuditAction('finance_settings_updated', 'Church', $church->id, $church->name, [
            'initial_balances' => $balances,
            'initial_balance_date' => $validated['initial_balance_date'],
        ], [
            'initial_balances' => $oldBalances,
            'initial_balance_date' => $oldDate,
        ]);

        return $this->successResponse($request, 'Початковий баланс оновлено.');
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
        $oldCurrencies = $church->enabled_currencies;

        $church->update([
            'enabled_currencies' => $currencies,
        ]);

        // Log currency settings update
        $this->logAuditAction('currency_settings_updated', 'Church', $church->id, $church->name, [
            'enabled_currencies' => $currencies,
        ], [
            'enabled_currencies' => $oldCurrencies,
        ]);

        // Auto-sync exchange rates if foreign currency enabled
        if (in_array('USD', $currencies) || in_array('EUR', $currencies)) {
            app(NbuExchangeRateService::class)->getCurrentRates();
        }

        return $this->successResponse($request, 'Налаштування валют оновлено.');
    }

    /**
     * Store a new transaction category
     */
    public function storeTransactionCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $church = $this->getCurrentChurch();

        TransactionCategory::create([
            'church_id' => $church->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? '#3B82F6',
            'sort_order' => TransactionCategory::where('church_id', $church->id)
                ->where('type', $validated['type'])
                ->max('sort_order') + 1,
        ]);

        return $this->successResponse($request, 'Категорію додано.');
    }

    /**
     * Update a transaction category
     */
    public function updateTransactionCategory(Request $request, TransactionCategory $category)
    {
        $church = $this->getCurrentChurch();

        if ($category->church_id !== $church->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category->update($validated);

        return $this->successResponse($request, 'Категорію оновлено.');
    }

    /**
     * Delete a transaction category
     */
    public function destroyTransactionCategory(Request $request, TransactionCategory $category)
    {
        $church = $this->getCurrentChurch();

        if ($category->church_id !== $church->id) {
            abort(403);
        }

        if ($category->transactions()->count() > 0) {
            return $this->errorResponse($request, 'Неможливо видалити категорію з транзакціями.');
        }

        $category->delete();

        return $this->successResponse($request, 'Категорію видалено.');
    }

    public function updateSongBoardTags(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'present|array',
            'tags.*' => 'string|max:50',
        ]);

        $church = $this->getCurrentChurch();

        $settings = $church->settings ?? [];
        $settings['song_board_tags'] = array_values(array_filter($validated['tags']));
        $church->update(['settings' => $settings]);

        return response()->json(['success' => true, 'tags' => $settings['song_board_tags']]);
    }

    private function findPersonMatches($user, $availablePeople): \Illuminate\Support\Collection
    {
        $nameParts = explode(' ', mb_strtolower(trim($user->name)), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        $email = mb_strtolower($user->email ?? '');
        $phone = preg_replace('/\D/', '', $user->person?->phone ?? '');

        return $availablePeople->filter(function ($person) use ($firstName, $lastName, $email, $phone) {
            $pFirst = mb_strtolower($person->first_name ?? '');
            $pLast = mb_strtolower($person->last_name ?? '');
            $pEmail = mb_strtolower($person->email ?? '');
            $pPhone = preg_replace('/\D/', '', $person->phone ?? '');

            if ($email && $pEmail && $email === $pEmail) return true;

            if ($phone && $pPhone && strlen($phone) >= 9 && strlen($pPhone) >= 9) {
                if (substr($phone, -9) === substr($pPhone, -9)) return true;
            }

            if ($firstName && $pFirst && $firstName === $pFirst) {
                if ($lastName && $pLast && $lastName === $pLast) return true;
                if (!$lastName || !$pLast) return true;
            }

            if ($firstName && $lastName && $pFirst && $pLast) {
                if ($firstName === $pLast && $lastName === $pFirst) return true;
            }

            return false;
        })->values();
    }
}
