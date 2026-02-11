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

                    $message = $this->buildMessage($birthdaysToday, $birthdaysTomorrow, $church);

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
        // Use church-specific role via pivot instead of global User.role
        return Person::where('church_id', $church->id)
            ->whereNotNull('telegram_chat_id')
            ->where(function ($query) use ($church) {
                $query->whereHas('user', function ($q) use ($church) {
                        $q->whereIn('users.id', function ($sub) use ($church) {
                            $sub->select('church_user.user_id')
                                ->from('church_user')
                                ->join('church_roles', 'church_user.church_role_id', '=', 'church_roles.id')
                                ->where('church_user.church_id', $church->id)
                                ->where('church_roles.is_admin_role', true);
                        });
                    })
                    ->orWhereHas('leadingMinistries')
                    ->orWhereHas('leadingGroups');
            })
            ->get();
    }

    private function buildMessage($birthdaysToday, $birthdaysTomorrow, Church $church): string
    {
        $message = "🎂 <b>Дні народження</b>\n";
        $message .= "⛪ {$church->name}\n\n";

        if ($birthdaysToday->isNotEmpty()) {
            $message .= "📅 <b>Сьогодні:</b>\n";
            foreach ($birthdaysToday as $person) {
                $message .= "• {$person->full_name}\n";
            }
            $message .= "\n";
        }

        if ($birthdaysTomorrow->isNotEmpty()) {
            $message .= "📅 <b>Завтра:</b>\n";
            foreach ($birthdaysTomorrow as $person) {
                $message .= "• {$person->full_name}\n";
            }
        }

        $message .= "\nНе забудьте привітати! 🎉";

        return $message;
    }
}
