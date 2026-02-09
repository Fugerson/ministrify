<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventResponsibility;
use App\Models\Person;
use App\Models\ServicePlanItem;
use App\Models\TelegramMessage;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    private ?TelegramService $telegram = null;

    private function telegram(): TelegramService
    {
        if (!$this->telegram) {
            $this->telegram = TelegramService::make();
        }
        return $this->telegram;
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        try {
            if (isset($data['callback_query'])) {
                return $this->handleCallbackQuery($data['callback_query']);
            }

            if (isset($data['message'])) {
                return $this->handleMessage($data['message']);
            }
        } catch (\Exception $e) {
            logger()->error('Telegram webhook error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleCallbackQuery(array $callbackQuery): \Illuminate\Http\JsonResponse
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];

        $person = Person::where('telegram_chat_id', $chatId)->first();

        if (!$person) {
            return response()->json(['ok' => true]);
        }

        // Handle Assignment callbacks (confirm_{id}, decline_{id})
        if (preg_match('/^(confirm|decline)_(\d+)$/', $data, $matches)) {
            $this->handleAssignmentCallback($matches[1], (int) $matches[2], $person, $chatId);
        }

        // Handle EventResponsibility callbacks (resp_confirm_{id}, resp_decline_{id})
        if (preg_match('/^resp_(confirm|decline)_(\d+)$/', $data, $matches)) {
            $this->handleResponsibilityCallback($matches[1], (int) $matches[2], $person, $chatId);
        }

        // Handle ServicePlanItem callbacks (plan_confirm_{itemId}_{personId}, plan_decline_{itemId}_{personId})
        if (preg_match('/^plan_(confirm|decline)_(\d+)(?:_(\d+))?$/', $data, $matches)) {
            $targetPersonId = isset($matches[3]) ? (int) $matches[3] : null;
            $this->handlePlanItemCallback($matches[1], (int) $matches[2], $targetPersonId, $person, $chatId);
        }

        return response()->json(['ok' => true]);
    }

    private function handleResponsibilityCallback(string $action, int $responsibilityId, Person $person, string $chatId): void
    {
        $responsibility = EventResponsibility::with('event')->find($responsibilityId);

        // Security: verify ownership AND church isolation
        if (!$responsibility || $responsibility->person_id !== $person->id || !$responsibility->event) {
            return;
        }

        // Ensure the event belongs to the same church as the person
        if ($responsibility->event->church_id !== $person->church_id) {
            return;
        }

        $event = $responsibility->event;
        $isConfirm = $action === 'confirm';

        $isConfirm ? $responsibility->confirm() : $responsibility->decline();

        $logMessage = $isConfirm
            ? "✅ Візьму на себе: {$event->title} - {$responsibility->name}"
            : "❌ Не може: {$event->title} - {$responsibility->name}";

        $this->saveMessage($person, $logMessage);

        $responseMessage = $isConfirm
            ? "✅ Супер! Ви берете на себе: {$responsibility->name}"
            : "😔 Зрозуміло, пошукаємо когось іншого.";

        $this->telegram()->sendMessage($chatId, $responseMessage);
    }

    private function handlePlanItemCallback(string $action, int $itemId, ?int $targetPersonId, Person $person, string $chatId): void
    {
        $item = ServicePlanItem::with('event')->find($itemId);

        if (!$item || ($targetPersonId !== $person->id && $item->responsible_id !== $person->id)) {
            return;
        }

        // Security: ensure the event belongs to the same church as the person
        if ($item->event && $item->event->church_id !== $person->church_id) {
            return;
        }

        $isConfirm = $action === 'confirm';
        $item->setPersonStatus($person->id, $isConfirm ? 'confirmed' : 'declined');

        $logMessage = $isConfirm
            ? "✅ Підтверджено: {$item->title}"
            : "❌ Відхилено: {$item->title}";

        $this->saveMessage($person, $logMessage);

        $responseMessage = $isConfirm
            ? "✅ Чудово! Ви підтвердили участь у: {$item->title}"
            : "😔 Зрозуміло, пошукаємо когось іншого для: {$item->title}";

        $this->telegram()->sendMessage($chatId, $responseMessage);
    }

    private function handleAssignmentCallback(string $action, int $assignmentId, Person $person, string $chatId): void
    {
        $assignment = \App\Models\Assignment::with(['event.ministry', 'position'])->find($assignmentId);

        if (!$assignment || $assignment->person_id !== $person->id || !$assignment->event || !$assignment->position || !$assignment->event->ministry) {
            return;
        }

        // Security: ensure the event belongs to the same church as the person
        if ($assignment->event->church_id !== $person->church_id) {
            return;
        }

        $event = $assignment->event;
        $position = $assignment->position;

        if ($action === 'confirm') {
            $assignment->confirm();

            $this->saveMessage($person, "✅ Підтверджено: {$event->ministry->name} - {$position->name} ({$event->date->format('d.m.Y')})");
            $this->telegram()->sendMessage($chatId, "✅ Чудово! Ви підтвердили участь у служінні {$event->date->format('d.m.Y')}.");
        } else {
            $assignment->decline();

            $this->saveMessage($person, "❌ Відхилено: {$event->ministry->name} - {$position->name} ({$event->date->format('d.m.Y')})");
            $this->telegram()->sendMessage($chatId, "😔 Зрозуміло. Повідомлення надіслано лідеру.");

            // Notify ministry leader
            $leader = $event->ministry->leader ?? $person->church?->people()
                ->whereNotNull('telegram_chat_id')
                ->whereHas('user', fn($q) => $q->where('role', 'admin'))
                ->first();

            if ($leader) {
                $this->telegram()->sendDeclineNotification($assignment, $leader);
            }
        }
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

        // Auto-link by username (only if exactly one match)
        if (!$person && $username) {
            $matchingPeople = Person::where(function ($q) use ($username) {
                $q->where('telegram_username', '@' . $username)
                  ->orWhere('telegram_username', $username);
            })->get();

            if ($matchingPeople->count() === 1) {
                $person = $matchingPeople->first();
                $person->update(['telegram_chat_id' => $chatId]);
                $wasJustLinked = true;
                $this->notifyAdminsAboutLink($person, $username);
            }
        }

        // Handle commands
        if (str_starts_with($text, '/')) {
            return $this->handleCommand($text, $chatId, $person, $wasJustLinked);
        }

        // Check if this is a linking code
        if (preg_match('/^[A-Z0-9]{6}$/', $text)) {
            return $this->handleLinkingCode($text, $chatId);
        }

        // Save incoming message
        if ($person && !empty($text)) {
            $this->saveMessage($person, $text, $message['message_id'] ?? null);
        }

        return response()->json(['ok' => true]);
    }

    private function handleCommand(string $text, string $chatId, ?Person $person, bool $wasJustLinked = false): \Illuminate\Http\JsonResponse
    {
        $command = strtolower(explode(' ', $text)[0]);

        switch ($command) {
            case '/start':
                if ($person) {
                    $greeting = $wasJustLinked
                        ? "🎉 Вітаємо, {$person->first_name}!\n\nВаш Telegram автоматично підключено до Ministrify!"
                        : "👋 Вітаємо, {$person->first_name}!\n\nВаш акаунт підключено до Ministrify.";

                    $this->telegram()->sendMessage($chatId,
                        "{$greeting}\n\n"
                        . "Доступні команди:\n"
                        . "/schedule — ваш розклад\n"
                        . "/next — наступне служіння\n"
                        . "/app — відкрити додаток\n"
                        . "/help — допомога"
                    );
                } else {
                    $this->telegram()->sendMessage($chatId,
                        "👋 Вітаємо в Ministrify!\n\n"
                        . "Ваш акаунт не знайдено.\n\n"
                        . "Якщо ви член церкви — зверніться до адміністратора, щоб вас додали в систему."
                    );
                }
                break;

            case '/schedule':
                if ($person) {
                    $this->telegram()->sendMessage($chatId, $this->telegram()->getScheduleMessage($person));
                } else {
                    $this->telegram()->sendMessage($chatId, '❌ Ваш акаунт не підключено.');
                }
                break;

            case '/next':
                if ($person) {
                    $this->telegram()->sendMessage($chatId, $this->telegram()->getNextEventMessage($person));
                } else {
                    $this->telegram()->sendMessage($chatId, '❌ Ваш акаунт не підключено.');
                }
                break;

            case '/unavailable':
                if ($person) {
                    $this->telegram()->sendMessage($chatId,
                        "📅 Щоб вказати дати недоступності:\n\n"
                        . "1. Увійдіть в Ministrify\n"
                        . "2. Перейдіть в «Мій профіль»\n"
                        . "3. Додайте дати недоступності"
                    );
                } else {
                    $this->telegram()->sendMessage($chatId, '❌ Ваш акаунт не підключено.');
                }
                break;

            case '/app':
                $keyboard = [
                    [['text' => "\xF0\x9F\x93\xB1 Відкрити додаток", 'web_app' => ['url' => route('telegram.app')]]],
                ];
                $this->telegram()->sendMessage(
                    $chatId,
                    "📱 <b>Ministrify App</b>\n\nНатисніть кнопку нижче, щоб відкрити додаток:",
                    $keyboard
                );
                break;

            case '/help':
                $this->telegram()->sendMessage($chatId,
                    "📚 <b>Допомога Ministrify</b>\n\n"
                    . "/start — початок роботи\n"
                    . "/schedule — ваш розклад на місяць\n"
                    . "/next — наступне служіння\n"
                    . "/app — відкрити додаток\n"
                    . "/unavailable — вказати недоступність\n"
                    . "/help — ця допомога\n\n"
                    . "Якщо є питання — зверніться до адміністратора."
                );
                break;

            default:
                $this->telegram()->sendMessage($chatId, "❓ Невідома команда.\n\nВведіть /help для списку доступних команд.");
                break;
        }

        return response()->json(['ok' => true]);
    }

    private function handleLinkingCode(string $code, string $chatId): \Illuminate\Http\JsonResponse
    {
        $cached = Cache::get("telegram_link_{$code}");

        if (!$cached) {
            $this->telegram()->sendMessage($chatId, '❌ Невірний або застарілий код.');
            return response()->json(['ok' => true]);
        }

        $person = Person::find($cached['person_id']);

        if (!$person) {
            $this->telegram()->sendMessage($chatId, '❌ Помилка: людину не знайдено.');
            return response()->json(['ok' => true]);
        }

        $person->update(['telegram_chat_id' => $chatId]);
        Cache::forget("telegram_link_{$code}");

        $this->telegram()->sendMessage($chatId,
            "✅ Акаунт успішно підключено!\n\n"
            . "Тепер ви будете отримувати сповіщення про служіння.\n\n"
            . "Доступні команди:\n"
            . "/schedule — ваш розклад\n"
            . "/next — наступне служіння"
        );

        return response()->json(['ok' => true]);
    }

    private function notifyAdminsAboutLink(Person $person, string $username): void
    {
        $admins = $person->church?->people()
            ->whereNotNull('telegram_chat_id')
            ->whereHas('user', fn($q) => $q->where('role', 'admin'))
            ->get() ?? collect();

        $message = "🔗 <b>Автопідключення Telegram</b>\n\n"
            . "👤 {$person->full_name}\n"
            . "📱 @{$username}\n\n"
            . "Користувач автоматично підключився до бота.";

        foreach ($admins as $admin) {
            try {
                $this->telegram()->sendMessage($admin->telegram_chat_id, $message);
            } catch (\Exception $e) {
                logger()->error('Failed to notify admin', ['error' => $e->getMessage()]);
            }
        }
    }


    private function saveMessage(Person $person, string $text, ?int $telegramMessageId = null): void
    {
        TelegramMessage::create([
            'church_id' => $person->church_id,
            'person_id' => $person->id,
            'direction' => 'incoming',
            'message' => $text,
            'telegram_message_id' => $telegramMessageId,
            'is_read' => false,
        ]);
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
