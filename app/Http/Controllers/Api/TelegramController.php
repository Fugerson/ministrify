<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Church;
use App\Models\EventResponsibility;
use App\Models\Person;
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

        // Parse callback data
        if (str_starts_with($data, 'confirm_')) {
            $assignmentId = (int) str_replace('confirm_', '', $data);
            $assignment = Assignment::find($assignmentId);

            if ($assignment && $assignment->person_id === $person->id) {
                $assignment->confirm();

                $church = $person->church;
                if ($church?->telegram_bot_token) {
                    $event = $assignment->event;
                    $position = $assignment->position;

                    // Save response to chat history
                    TelegramMessage::create([
                        'church_id' => $church->id,
                        'person_id' => $person->id,
                        'direction' => 'incoming',
                        'message' => "✅ Підтвердив участь: {$event->date->format('d.m.Y')} - {$position->name}",
                        'is_read' => false,
                    ]);

                    $telegram = new TelegramService($church->telegram_bot_token);
                    $telegram->sendMessage($chatId, '✅ Дякуємо! Ви підтвердили участь.');
                }
            }
        } elseif (str_starts_with($data, 'decline_')) {
            $assignmentId = (int) str_replace('decline_', '', $data);
            $assignment = Assignment::find($assignmentId);

            if ($assignment && $assignment->person_id === $person->id) {
                $assignment->decline();

                $church = $person->church;
                if (!$church?->telegram_bot_token) {
                    return response()->json(['ok' => true]);
                }

                $event = $assignment->event;
                $position = $assignment->position;

                // Save response to chat history
                TelegramMessage::create([
                    'church_id' => $church->id,
                    'person_id' => $person->id,
                    'direction' => 'incoming',
                    'message' => "❌ Відхилив участь: {$event->date->format('d.m.Y')} - {$position->name}",
                    'is_read' => false,
                ]);

                $settings = $church->settings ?? [];

                if (!empty($settings['notifications']['notify_leader_on_decline'])) {
                    $leader = $assignment->event?->ministry?->leader;
                    if ($leader && $leader->telegram_chat_id) {
                        $telegram = new TelegramService($church->telegram_bot_token);
                        $telegram->sendDeclineNotification($assignment, $leader);
                    }
                }

                $telegram = new TelegramService($church->telegram_bot_token);
                $telegram->sendMessage($chatId, '❌ Ви відхилили участь. Повідомлення надіслано лідеру.');
            }
        } elseif (str_starts_with($data, 'resp_confirm_')) {
            $responsibilityId = (int) str_replace('resp_confirm_', '', $data);
            $responsibility = EventResponsibility::find($responsibilityId);

            if ($responsibility && $responsibility->person_id === $person->id) {
                $responsibility->confirm();

                $church = $person->church;
                if ($church?->telegram_bot_token) {
                    $event = $responsibility->event;

                    // Save response to chat history
                    TelegramMessage::create([
                        'church_id' => $church->id,
                        'person_id' => $person->id,
                        'direction' => 'incoming',
                        'message' => "✅ Візьму на себе: {$event->title} - {$responsibility->name}",
                        'is_read' => false,
                    ]);

                    $telegram = new TelegramService($church->telegram_bot_token);
                    $telegram->sendMessage($chatId, "✅ Супер! Ви берете на себе: {$responsibility->name}");
                }
            }
        } elseif (str_starts_with($data, 'resp_decline_')) {
            $responsibilityId = (int) str_replace('resp_decline_', '', $data);
            $responsibility = EventResponsibility::find($responsibilityId);

            if ($responsibility && $responsibility->person_id === $person->id) {
                $responsibility->decline();

                $church = $person->church;
                if ($church?->telegram_bot_token) {
                    $event = $responsibility->event;

                    // Save response to chat history
                    TelegramMessage::create([
                        'church_id' => $church->id,
                        'person_id' => $person->id,
                        'direction' => 'incoming',
                        'message' => "❌ Не може: {$event->title} - {$responsibility->name}",
                        'is_read' => false,
                    ]);

                    $telegram = new TelegramService($church->telegram_bot_token);
                    $telegram->sendMessage($chatId, "😔 Зрозуміло, пошукаємо когось іншого.");
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

        // Try to find person by chat ID or username
        $person = Person::where('telegram_chat_id', $chatId)->first();

        if (!$person && $username) {
            $person = Person::where('telegram_username', '@' . $username)
                ->orWhere('telegram_username', $username)
                ->first();

            // Link chat ID if found
            if ($person) {
                $person->update(['telegram_chat_id' => $chatId]);
            }
        }

        // Handle commands
        if (str_starts_with($text, '/')) {
            return $this->handleCommand($text, $chatId, $person);
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

    private function handleCommand(string $text, string $chatId, ?Person $person): \Illuminate\Http\JsonResponse
    {
        $command = strtolower(explode(' ', $text)[0]);

        // Find church for this person or use default response
        $church = $person?->church;
        $telegram = $church ? new TelegramService($church->telegram_bot_token) : null;

        switch ($command) {
            case '/start':
                if ($person) {
                    $telegram?->sendMessage($chatId,
                        "👋 Вітаємо, {$person->first_name}!\n\n"
                        . "Ваш акаунт підключено до Ministrify.\n\n"
                        . "Доступні команди:\n"
                        . "/schedule — ваш розклад\n"
                        . "/next — наступне служіння\n"
                        . "/help — допомога"
                    );
                } else {
                    // Generic response for unlinked users
                    $this->sendGenericMessage($chatId,
                        "👋 Вітаємо в Ministrify!\n\n"
                        . "Для підключення акаунту:\n"
                        . "1. Увійдіть в Ministrify\n"
                        . "2. Перейдіть в профіль\n"
                        . "3. Натисніть «Підключити Telegram»\n"
                        . "4. Введіть код, який ви отримаєте"
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
