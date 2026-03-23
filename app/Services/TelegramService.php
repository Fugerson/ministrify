<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\EventResponsibility;
use App\Models\Person;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $token;

    private string $baseUrl;

    public function __construct(?string $token = null)
    {
        $this->token = $token ?? config('services.telegram.bot_token');

        if (! $this->token) {
            throw new \RuntimeException('Telegram bot token not configured');
        }

        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Create instance with default config token
     */
    public static function make(): self
    {
        return new self;
    }

    public function getMe(): array
    {
        $response = Http::get("{$this->baseUrl}/getMe");

        if (! $response->ok()) {
            throw new \Exception('Failed to connect to Telegram API');
        }

        $data = $response->json();

        if (! $data['ok']) {
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

        $success = $response->ok() && ($response->json()['ok'] ?? false);

        if (! $success) {
            \Log::warning('TelegramService: Failed to send message', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        }

        return $success;
    }

    public function sendAssignmentNotification(Assignment $assignment): bool
    {
        $person = $assignment->person;
        $event = $assignment->event;
        $position = $assignment->position;

        if (! $person?->telegram_chat_id || ! $event || ! $position || ! $event->ministry) {
            return false;
        }

        $timeStr = $event->time ? $event->time->format('H:i') : 'весь день';
        $message = "🔔 <b>Нове призначення!</b>\n\n"
            ."📅 {$event->date->format('d.m.Y')} ({$this->getDayName($event->date)}), {$timeStr}\n"
            ."⛪ Служіння: {$event->ministry->name}\n"
            ."🎯 Позиція: {$position->name}\n\n"
            .'Ви можете підтвердити або відхилити участь:';

        $keyboard = [
            [
                ['text' => '✅ Підтвердити', 'callback_data' => "confirm_{$assignment->id}"],
                ['text' => '❌ Не можу', 'callback_data' => "decline_{$assignment->id}"],
            ],
        ];

        return $this->sendMessage($person->telegram_chat_id, $message, $keyboard);
    }

    public function sendResponsibilityReminder(EventResponsibility $responsibility): bool
    {
        $person = $responsibility->person;
        $event = $responsibility->event;

        if (! $person?->telegram_chat_id || ! $event || ! $event->ministry) {
            return false;
        }

        $isToday = $event->date->isToday();
        $prefix = $isToday ? '⏰ <b>Нагадування!</b>' : '⏰ <b>Нагадування на завтра!</b>';

        $timeStr = $event->time ? $event->time->format('H:i') : 'весь день';
        $ministryName = $event->ministry?->name ?? 'Служіння';
        $message = "{$prefix}\n\n"
            .($isToday ? 'Сьогодні' : 'Завтра')." ти служиш:\n"
            ."📅 {$event->date->format('d.m.Y')}, {$timeStr}\n"
            ."⛪ {$ministryName}\n"
            ."🎯 {$responsibility->name}\n\n"
            .'Не забудь! 🙏';

        return $this->sendMessage($person->telegram_chat_id, $message);
    }

    public function sendDeclineNotification(Assignment $assignment, Person $leader): bool
    {
        $person = $assignment->person;
        $event = $assignment->event;
        $position = $assignment->position;

        if (! $leader->telegram_chat_id || ! $person || ! $event || ! $position || ! $event->ministry) {
            return false;
        }

        $timeStr = $event->time ? $event->time->format('H:i') : 'весь день';
        $message = "⚠️ <b>Відмова від служіння</b>\n\n"
            ."{$person->full_name} відхилив(ла) участь:\n"
            ."📅 {$event->date->format('d.m.Y')}, {$timeStr}\n"
            ."⛪ {$event->ministry->name}\n"
            ."🎯 {$position->name}\n\n"
            .'Потрібно знайти заміну.';

        return $this->sendMessage($leader->telegram_chat_id, $message);
    }

    public function getScheduleMessage(Person $person): string
    {
        $assignments = $person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn ($q) => $q->where('date', '>=', now())->where('date', '<=', now()->endOfMonth()))
            ->get()
            ->sortBy(fn ($a) => $a->event->date);

        if ($assignments->isEmpty()) {
            return '📅 У тебе немає запланованих служінь на цей місяць.';
        }

        $message = '📅 <b>Твій розклад на '.now()->translatedFormat('F').":</b>\n\n";

        foreach ($assignments as $assignment) {
            $event = $assignment->event;
            if (! $event || ! $event->ministry || ! $assignment->position) {
                continue;
            }

            $status = match ($assignment->status) {
                'confirmed' => '✅',
                'pending' => '⏳',
                'declined' => '❌',
                default => '❓',
            };

            $message .= "{$event->date->format('d.m')} ({$this->getShortDayName($event->date)}) — "
                .($event->ministry?->name ?? 'Служіння').', '.($assignment->position?->name ?? '')." {$status}\n";
        }

        $message .= "\n✅ — підтверджено\n⏳ — очікує підтвердження";

        return $message;
    }

    public function getNextEventMessage(Person $person): string
    {
        $assignment = $person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn ($q) => $q->where('date', '>=', now()))
            ->where('status', '!=', 'declined')
            ->first();

        if (! $assignment || ! $assignment->event || ! $assignment->position || ! $assignment->event->ministry) {
            return 'У тебе немає запланованих служінь.';
        }

        $event = $assignment->event;

        $timeStr = $event->time ? $event->time->format('H:i') : 'весь день';

        return "📅 <b>Наступне служіння:</b>\n\n"
            ."📆 {$event->date->format('d.m.Y')} ({$this->getDayName($event->date)})\n"
            ."🕐 {$timeStr}\n"
            .'⛪ '.($event->ministry?->name ?? 'Служіння')."\n"
            .'🎯 '.($assignment->position?->name ?? '');
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
