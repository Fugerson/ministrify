<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Person;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $token;
    private string $baseUrl;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->baseUrl = "https://api.telegram.org/bot{$token}";
    }

    public function getMe(): array
    {
        $response = Http::get("{$this->baseUrl}/getMe");

        if (!$response->ok()) {
            throw new \Exception('Failed to connect to Telegram API');
        }

        $data = $response->json();

        if (!$data['ok']) {
            throw new \Exception($data['description'] ?? 'Unknown error');
        }

        return $data['result'];
    }

    public function setWebhook(string $url): bool
    {
        $response = Http::post("{$this->baseUrl}/setWebhook", [
            'url' => $url,
        ]);

        return $response->ok() && $response->json()['ok'];
    }

    public function sendMessage(string $chatId, string $text, ?array $keyboard = null): bool
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode([
                'inline_keyboard' => $keyboard,
            ]);
        }

        $response = Http::post("{$this->baseUrl}/sendMessage", $data);

        return $response->ok() && $response->json()['ok'];
    }

    public function sendAssignmentNotification(Assignment $assignment): bool
    {
        $person = $assignment->person;
        $event = $assignment->event;
        $position = $assignment->position;

        if (!$person->telegram_chat_id) {
            return false;
        }

        $message = "🔔 <b>Нове призначення!</b>\n\n"
            . "📅 {$event->date->format('d.m.Y')} ({$this->getDayName($event->date)}), {$event->time->format('H:i')}\n"
            . "⛪ Служіння: {$event->ministry->name}\n"
            . "🎯 Позиція: {$position->name}\n\n"
            . "Ви можете підтвердити або відхилити участь:";

        $keyboard = [
            [
                ['text' => '✅ Підтвердити', 'callback_data' => "confirm_{$assignment->id}"],
                ['text' => '❌ Не можу', 'callback_data' => "decline_{$assignment->id}"],
            ],
        ];

        return $this->sendMessage($person->telegram_chat_id, $message, $keyboard);
    }

    public function sendReminder(Assignment $assignment): bool
    {
        $person = $assignment->person;
        $event = $assignment->event;
        $position = $assignment->position;

        if (!$person->telegram_chat_id) {
            return false;
        }

        $isToday = $event->date->isToday();
        $prefix = $isToday ? '⏰ <b>Нагадування!</b>' : '⏰ <b>Нагадування на завтра!</b>';

        $message = "{$prefix}\n\n"
            . ($isToday ? "Сьогодні" : "Завтра") . " ти служиш:\n"
            . "📅 {$event->date->format('d.m.Y')}, {$event->time->format('H:i')}\n"
            . "⛪ {$event->ministry->name} — {$position->name}\n\n"
            . "Не забудь! 🙏";

        return $this->sendMessage($person->telegram_chat_id, $message);
    }

    public function sendResponsibilityReminder(\App\Models\EventResponsibility $responsibility): bool
    {
        $person = $responsibility->person;
        $event = $responsibility->event;

        if (!$person->telegram_chat_id) {
            return false;
        }

        $isToday = $event->date->isToday();
        $prefix = $isToday ? '⏰ <b>Нагадування!</b>' : '⏰ <b>Нагадування на завтра!</b>';

        $message = "{$prefix}\n\n"
            . ($isToday ? "Сьогодні" : "Завтра") . " ти служиш:\n"
            . "📅 {$event->date->format('d.m.Y')}, {$event->time->format('H:i')}\n"
            . "⛪ {$event->ministry->name}\n"
            . "🎯 {$responsibility->name}\n\n"
            . "Не забудь! 🙏";

        return $this->sendMessage($person->telegram_chat_id, $message);
    }

    public function sendDeclineNotification(Assignment $assignment, Person $leader): bool
    {
        if (!$leader->telegram_chat_id) {
            return false;
        }

        $person = $assignment->person;
        $event = $assignment->event;
        $position = $assignment->position;

        $message = "⚠️ <b>Відмова від служіння</b>\n\n"
            . "{$person->full_name} відхилив(ла) участь:\n"
            . "📅 {$event->date->format('d.m.Y')}, {$event->time->format('H:i')}\n"
            . "⛪ {$event->ministry->name}\n"
            . "🎯 {$position->name}\n\n"
            . "Потрібно знайти заміну.";

        return $this->sendMessage($leader->telegram_chat_id, $message);
    }

    public function sendCancellationNotification(Assignment $assignment): bool
    {
        $person = $assignment->person;
        $event = $assignment->event;

        if (!$person->telegram_chat_id) {
            return false;
        }

        $message = "⚠️ <b>Зміна в розкладі!</b>\n\n"
            . "Твоє служіння {$event->date->format('d.m.Y')} СКАСОВАНО.\n\n"
            . "Якщо є питання — звернись до лідера.";

        return $this->sendMessage($person->telegram_chat_id, $message);
    }

    public function getScheduleMessage(Person $person): string
    {
        $assignments = $person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now())->where('date', '<=', now()->endOfMonth()))
            ->get()
            ->sortBy(fn($a) => $a->event->date);

        if ($assignments->isEmpty()) {
            return "📅 У тебе немає запланованих служінь на цей місяць.";
        }

        $message = "📅 <b>Твій розклад на " . now()->translatedFormat('F') . ":</b>\n\n";

        foreach ($assignments as $assignment) {
            $event = $assignment->event;
            $status = match ($assignment->status) {
                'confirmed' => '✅',
                'pending' => '⏳',
                'declined' => '❌',
            };

            $message .= "{$event->date->format('d.m')} ({$this->getShortDayName($event->date)}) — "
                . "{$event->ministry->name}, {$assignment->position->name} {$status}\n";
        }

        $message .= "\n✅ — підтверджено\n⏳ — очікує підтвердження";

        return $message;
    }

    public function getNextEventMessage(Person $person): string
    {
        $assignment = $person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
            ->where('status', '!=', 'declined')
            ->first();

        if (!$assignment) {
            return "У тебе немає запланованих служінь.";
        }

        $event = $assignment->event;

        return "📅 <b>Наступне служіння:</b>\n\n"
            . "📆 {$event->date->format('d.m.Y')} ({$this->getDayName($event->date)})\n"
            . "🕐 {$event->time->format('H:i')}\n"
            . "⛪ {$event->ministry->name}\n"
            . "🎯 {$assignment->position->name}";
    }

    private function getDayName(\DateTime $date): string
    {
        $days = ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'];
        return $days[$date->format('w')];
    }

    private function getShortDayName(\DateTime $date): string
    {
        $days = ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return $days[$date->format('w')];
    }
}
