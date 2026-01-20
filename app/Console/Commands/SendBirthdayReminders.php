<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Models\Person;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBirthdayReminders extends Command
{
    protected $signature = 'app:send-birthday-reminders';
    protected $description = 'Send birthday reminders to leaders and church admins';

    public function handle(): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $this->info("Checking birthdays for {$today->format('Y-m-d')}");

        if (!config('services.telegram.bot_token')) {
            $this->warn('Telegram bot not configured');
            return self::SUCCESS;
        }

        $churches = Church::all();
        $sent = 0;

        foreach ($churches as $church) {
            // Get notification settings
            $settings = $church->settings['notifications'] ?? [];

            // Check if birthday notifications are enabled (default: true)
            if (!($settings['birthday_reminders'] ?? true)) {
                continue;
            }

            // Find birthdays today
            $birthdaysToday = Person::where('church_id', $church->id)
                ->whereNotNull('birth_date')
                ->whereMonth('birth_date', $today->month)
                ->whereDay('birth_date', $today->day)
                ->get();

            // Find birthdays tomorrow
            $birthdaysTomorrow = Person::where('church_id', $church->id)
                ->whereNotNull('birth_date')
                ->whereMonth('birth_date', $tomorrow->month)
                ->whereDay('birth_date', $tomorrow->day)
                ->get();

            if ($birthdaysToday->isEmpty() && $birthdaysTomorrow->isEmpty()) {
                continue;
            }

            // Get leaders and admins to notify
            $recipients = $this->getRecipients($church);

            if ($recipients->isEmpty()) {
                continue;
            }

            try {
                $telegram = TelegramService::make();

                foreach ($recipients as $recipient) {
                    if (!$recipient->telegram_chat_id) {
                        continue;
                    }

                    $message = $this->buildMessage($birthdaysToday, $birthdaysTomorrow);

                    if ($telegram->sendMessage($recipient->telegram_chat_id, $message)) {
                        $sent++;
                        $this->line("  Sent to {$recipient->full_name}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("  Error for church {$church->name}: {$e->getMessage()}");
            }
        }

        $this->info("Done! Sent {$sent} birthday reminders.");
        return self::SUCCESS;
    }

    private function getRecipients(Church $church)
    {
        // Get admins and leaders with telegram connected
        return Person::where('church_id', $church->id)
            ->whereNotNull('telegram_chat_id')
            ->where(function ($query) {
                $query->whereHas('user', fn($q) => $q->where('role', 'admin'))
                    ->orWhereHas('leadingMinistries')
                    ->orWhereHas('leadingGroups');
            })
            ->get();
    }

    private function buildMessage($birthdaysToday, $birthdaysTomorrow): string
    {
        $message = "🎂 <b>Дні народження</b>\n\n";

        if ($birthdaysToday->isNotEmpty()) {
            $message .= "📅 <b>Сьогодні:</b>\n";
            foreach ($birthdaysToday as $person) {
                $age = $person->birth_date->age;
                $message .= "• {$person->full_name} — {$age} років\n";
            }
            $message .= "\n";
        }

        if ($birthdaysTomorrow->isNotEmpty()) {
            $message .= "📅 <b>Завтра:</b>\n";
            foreach ($birthdaysTomorrow as $person) {
                $age = $person->birth_date->addYear()->age;
                $message .= "• {$person->full_name} — {$age} років\n";
            }
        }

        $message .= "\nНе забудьте привітати! 🎉";

        return $message;
    }
}
