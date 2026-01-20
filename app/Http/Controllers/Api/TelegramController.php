<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\EventResponsibility;
use App\Models\Person;
use App\Models\ServicePlanItem;
use App\Models\TelegramMessage;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->all();

        // Handle callback query (button clicks)
        if (isset($data['callback_query'])) {
            return $this->handleCallbackQuery($data['callback_query']);
        }

        // Handle regular message
        if (isset($data['message'])) {
            return $this->handleMessage($data['message']);
        }

        return response()->json(['ok' => true]);
    }

    private function handleCallbackQuery(array $callbackQuery): \Illuminate\Http\JsonResponse
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];

        // Find person by chat ID
        $person = Person::where('telegram_chat_id', $chatId)->first();

        if (!$person) {
            return response()->json(['ok' => true]);
        }

        $church = $person->church;
        $telegram = $church?->telegram_bot_token ? new TelegramService($church->telegram_bot_token) : null;

        // Handle Assignment callbacks (confirm_{id}, decline_{id})
        if (preg_match('/^(confirm|decline)_(\d+)$/', $data, $matches)) {
            return $this->handleAssignmentCallback($matches[1], (int) $matches[2], $person, $church, $telegram, $chatId);
        }

        // Handle EventResponsibility callbacks (resp_confirm_{id}, resp_decline_{id})
        if (preg_match('/^resp_(confirm|decline)_(\d+)$/', $data, $matches)) {
            $this->handleResponsibilityCallback($matches[1], (int) $matches[2], $person, $church, $telegram, $chatId);
        }

        // Handle ServicePlanItem callbacks (plan_confirm_{itemId}_{personId}, plan_decline_{itemId}_{personId})
        if (preg_match('/^plan_(confirm|decline)_(\d+)(?:_(\d+))?$/', $data, $matches)) {
            $targetPersonId = isset($matches[3]) ? (int) $matches[3] : null;
            $this->handlePlanItemCallback($matches[1], (int) $matches[2], $targetPersonId, $person, $church, $telegram, $chatId);
        }

        return response()->json(['ok' => true]);
    }

    private function handleResponsibilityCallback(string $action, int $responsibilityId, Person $person, ?Church $church, ?TelegramService $telegram, string $chatId): void
    {
        $responsibility = EventResponsibility::with('event')->find($responsibilityId);

        if (!$responsibility || $responsibility->person_id !== $person->id) {
            return;
        }

        $event = $responsibility->event;
        $isConfirm = $action === 'confirm';

        $isConfirm ? $responsibility->confirm() : $responsibility->decline();

        if ($telegram && $church) {
            $logMessage = $isConfirm
                ? "✅ Візьму на себе: {$event->title} - {$responsibility->name}"
                : "❌ Не може: {$event->title} - {$responsibility->name}";

            TelegramMessage::create([
                'church_id' => $church->id,
                'person_id' => $person->id,
                'direction' => 'incoming',
                'message' => $logMessage,
                'is_read' => false,
            ]);

            $responseMessage = $isConfirm
                ? "✅ Супер! Ви берете на себе: {$responsibility->name}"
                : "😔 Зрозуміло, пошукаємо когось іншого.";

            $telegram->sendMessage($chatId, $responseMessage);
        }
    }

    private function handlePlanItemCallback(string $action, int $itemId, ?int $targetPersonId, Person $person, ?Church $church, ?TelegramService $telegram, string $chatId): void
    {
        $item = ServicePlanItem::with('event')->find($itemId);

        if (!$item || ($targetPersonId !== $person->id && $item->responsible_id !== $person->id)) {
            return;
        }

        $isConfirm = $action === 'confirm';
        $item->setPersonStatus($person->id, $isConfirm ? 'confirmed' : 'declined');

        if ($telegram && $church) {
            $logMessage = $isConfirm
                ? "✅ Підтверджено: {$item->title}"
                : "❌ Відхилено: {$item->title}";

            TelegramMessage::create([
                'church_id' => $church->id,
                'person_id' => $person->id,
                'direction' => 'incoming',
                'message' => $logMessage,
                'is_read' => false,
            ]);

            $responseMessage = $isConfirm
                ? "✅ Чудово! Ви підтвердили участь у: {$item->title}"
                : "😔 Зрозуміло, пошукаємо когось іншого для: {$item->title}";

            $telegram->sendMessage($chatId, $responseMessage);
        }
    }

    private function handleAssignmentCallback(string $action, int $assignmentId, Person $person, ?Church $church, ?TelegramService $telegram, string $chatId): \Illuminate\Http\JsonResponse
    {
        $assignment = \App\Models\Assignment::with(['event.ministry', 'position'])->find($assignmentId);

        if (!$assignment || $assignment->person_id !== $person->id) {
            return response()->json(['ok' => true]);
        }

        $event = $assignment->event;
        $position = $assignment->position;

        if ($action === 'confirm') {
            $assignment->confirm();

            if ($telegram && $church) {
                TelegramMessage::create([
                    'church_id' => $church->id,
                    'person_id' => $person->id,
                    'direction' => 'incoming',
                    'message' => "✅ Підтверджено: {$event->ministry->name} - {$position->name} ({$event->date->format('d.m.Y')})",
                    'is_read' => false,
                ]);

                $telegram->sendMessage($chatId, "✅ Чудово! Ви підтвердили участь у служінні {$event->date->format('d.m.Y')}.");
            }
        } else {
            $assignment->decline();

            if ($telegram && $church) {
                TelegramMessage::create([
                    'church_id' => $church->id,
                    'person_id' => $person->id,
                    'direction' => 'incoming',
                    'message' => "❌ Відхилено: {$event->ministry->name} - {$position->name} ({$event->date->format('d.m.Y')})",
                    'is_read' => false,
                ]);

                $telegram->sendMessage($chatId, "😔 Зрозуміло. Повідомлення надіслано лідеру.");

                // Notify ministry leader if available
                $leader = $event->ministry->leader ?? $church->people()
                    ->whereNotNull('telegram_chat_id')
                    ->whereHas('user', fn($q) => $q->where('role', 'admin'))
                    ->first();

                if ($leader) {
                    $telegram->sendDeclineNotification($assignment, $leader);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    private function handleMessage(array $message): \Illuminate\Http\JsonResponse
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? null;
        $firstName = $message['from']['first_name'] ?? 'User';
        $lastName = $message['from']['last_name'] ?? '';
        $wasJustLinked = false;

        // Try to find person by chat ID
        $person = Person::where('telegram_chat_id', $chatId)->first();

        // If not found, try to auto-link by username
        if (!$person && $username) {
            $person = Person::where('telegram_username', '@' . $username)
                ->orWhere('telegram_username', $username)
                ->first();

            // Auto-link chat ID if found by username
            if ($person) {
                $person->update([
                    'telegram_chat_id' => $chatId,
                ]);
                $wasJustLinked = true;

                // Notify admin about auto-link
                $this->notifyAdminAboutNewUser($person, 'auto_linked', $username);
            }
        }

        // If still not found and this is /start, notify admin about new unknown user
        if (!$person && str_starts_with($text, '/start')) {
            $this->notifyAdminAboutNewUser(null, 'unknown', $username, $firstName, $lastName, $chatId);
        }

        // Handle commands
        if (str_starts_with($text, '/')) {
            return $this->handleCommand($text, $chatId, $person, $wasJustLinked);
        }

        // Check if this is a linking code
        if (preg_match('/^[A-Z0-9]{6}$/', $text)) {
            return $this->handleLinkingCode($text, $chatId, $message);
        }

        // Save incoming message if person is linked
        if ($person && !empty($text)) {
            TelegramMessage::create([
                'church_id' => $person->church_id,
                'person_id' => $person->id,
                'direction' => 'incoming',
                'message' => $text,
                'telegram_message_id' => $message['message_id'] ?? null,
                'is_read' => false,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleCommand(string $text, string $chatId, ?Person $person, bool $wasJustLinked = false): \Illuminate\Http\JsonResponse
    {
        $command = strtolower(explode(' ', $text)[0]);

        // Find church for this person or use default response
        $church = $person?->church;
        $telegram = $church ? new TelegramService($church->telegram_bot_token) : null;

        switch ($command) {
            case '/start':
                if ($person) {
                    if ($wasJustLinked) {
                        // User was just auto-linked by username
                        $telegram?->sendMessage($chatId,
                            "🎉 Вітаємо, {$person->first_name}!\n\n"
                            . "Ваш Telegram автоматично підключено до Ministrify!\n\n"
                            . "Тепер ви будете отримувати сповіщення про служіння.\n\n"
                            . "Доступні команди:\n"
                            . "/schedule — ваш розклад\n"
                            . "/next — наступне служіння\n"
                            . "/help — допомога"
                        );
                    } else {
                        $telegram?->sendMessage($chatId,
                            "👋 Вітаємо, {$person->first_name}!\n\n"
                            . "Ваш акаунт підключено до Ministrify.\n\n"
                            . "Доступні команди:\n"
                            . "/schedule — ваш розклад\n"
                            . "/next — наступне служіння\n"
                            . "/help — допомога"
                        );
                    }
                } else {
                    // Generic response for unlinked users
                    $this->sendGenericMessage($chatId,
                        "👋 Вітаємо в Ministrify!\n\n"
                        . "Ваш акаунт ще не підключено.\n\n"
                        . "Адміністратор отримав сповіщення про вас і зможе додати вас до системи.\n\n"
                        . "Або підключіться самостійно:\n"
                        . "1. Увійдіть в Ministrify\n"
                        . "2. Перейдіть в профіль\n"
                        . "3. Натисніть «Підключити Telegram»"
                    );
                }
                break;

            case '/schedule':
                if ($person && $telegram) {
                    $message = $telegram->getScheduleMessage($person);
                    $telegram->sendMessage($chatId, $message);
                } else {
                    $this->sendGenericMessage($chatId, '❌ Ваш акаунт не підключено.');
                }
                break;

            case '/next':
                if ($person && $telegram) {
                    $message = $telegram->getNextEventMessage($person);
                    $telegram->sendMessage($chatId, $message);
                } else {
                    $this->sendGenericMessage($chatId, '❌ Ваш акаунт не підключено.');
                }
                break;

            case '/unavailable':
                if ($person && $telegram) {
                    $telegram->sendMessage($chatId,
                        "📅 Щоб вказати дати недоступності:\n\n"
                        . "1. Увійдіть в Ministrify\n"
                        . "2. Перейдіть в «Мій профіль»\n"
                        . "3. Додайте дати недоступності"
                    );
                } else {
                    $this->sendGenericMessage($chatId, '❌ Ваш акаунт не підключено.');
                }
                break;

            case '/help':
                $helpMessage = "📚 <b>Допомога Ministrify</b>\n\n"
                    . "/start — початок роботи\n"
                    . "/schedule — ваш розклад на місяць\n"
                    . "/next — наступне служіння\n"
                    . "/unavailable — вказати недоступність\n"
                    . "/help — ця допомога\n\n"
                    . "Якщо є питання — зверніться до адміністратора.";

                if ($telegram) {
                    $telegram->sendMessage($chatId, $helpMessage);
                } else {
                    $this->sendGenericMessage($chatId, $helpMessage);
                }
                break;
        }

        return response()->json(['ok' => true]);
    }

    private function handleLinkingCode(string $code, string $chatId, array $message): \Illuminate\Http\JsonResponse
    {
        $cached = Cache::get("telegram_link_{$code}");

        if (!$cached) {
            $this->sendGenericMessage($chatId, '❌ Невірний або застарілий код.');
            return response()->json(['ok' => true]);
        }

        $person = Person::find($cached['person_id']);

        if (!$person) {
            $this->sendGenericMessage($chatId, '❌ Помилка: людину не знайдено.');
            return response()->json(['ok' => true]);
        }

        // Update person with chat ID
        $person->update(['telegram_chat_id' => $chatId]);

        // Clear the code
        Cache::forget("telegram_link_{$code}");

        $church = $person->church;
        if ($church?->telegram_bot_token) {
            $telegram = new TelegramService($church->telegram_bot_token);
            $telegram->sendMessage($chatId,
                "✅ Акаунт успішно підключено!\n\n"
                . "Тепер ви будете отримувати сповіщення про служіння.\n\n"
                . "Доступні команди:\n"
                . "/schedule — ваш розклад\n"
                . "/next — наступне служіння"
            );
        }

        return response()->json(['ok' => true]);
    }

    private function notifyAdminAboutNewUser(?Person $person, string $type, ?string $username, ?string $firstName = null, ?string $lastName = null, ?string $chatId = null): void
    {
        // Get church (from person or first available)
        $church = $person?->church ?? Church::whereNotNull('telegram_bot_token')->first();

        if (!$church || !$church->telegram_bot_token) {
            return;
        }

        // Find admin users with telegram connected
        $admins = $church->people()
            ->whereNotNull('telegram_chat_id')
            ->whereHas('user', fn($q) => $q->where('role', 'admin'))
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        $telegram = new TelegramService($church->telegram_bot_token);

        if ($type === 'auto_linked' && $person) {
            $message = "🔗 <b>Автопідключення Telegram</b>\n\n"
                . "👤 {$person->full_name}\n"
                . "📱 @{$username}\n\n"
                . "Користувач автоматично підключився до бота.";
        } else {
            $fullName = trim("{$firstName} {$lastName}");
            $usernameText = $username ? "@{$username}" : "без username";
            $message = "👋 <b>Новий користувач бота</b>\n\n"
                . "👤 {$fullName}\n"
                . "📱 {$usernameText}\n"
                . "🆔 Chat ID: {$chatId}\n\n"
                . "Користувач не знайдений в системі.\n"
                . "Додайте його та вкажіть Telegram username для автопідключення.";
        }

        foreach ($admins as $admin) {
            try {
                $telegram->sendMessage($admin->telegram_chat_id, $message);
            } catch (\Exception $e) {
                logger()->error('Failed to notify admin about new bot user', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function sendGenericMessage(string $chatId, string $text): void
    {
        // Use first available church's bot token or env variable
        $church = Church::whereNotNull('telegram_bot_token')->first();

        if ($church) {
            $telegram = new TelegramService($church->telegram_bot_token);
            $telegram->sendMessage($chatId, $text);
        }
    }

    public function link(string $code)
    {
        $cached = Cache::get("telegram_link_{$code}");

        if (!$cached) {
            return response()->json(['error' => 'Invalid code'], 404);
        }

        return response()->json(['status' => 'pending']);
    }

    public static function generateLinkingCode(Person $person): string
    {
        $code = strtoupper(Str::random(6));

        Cache::put("telegram_link_{$code}", [
            'person_id' => $person->id,
        ], now()->addMinutes(10));

        return $code;
    }
}
