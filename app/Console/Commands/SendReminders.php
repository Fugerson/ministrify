<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Event;
use App\Models\EventResponsibility;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders';
    protected $description = 'Send reminders for upcoming assignments based on event settings';

    public function handle(): int
    {
        $now = Carbon::now();
        $this->info("Checking reminders at {$now->format('Y-m-d H:i')}");

        // Find all upcoming events with reminder settings
        $events = Event::whereNotNull('reminder_settings')
            ->where('date', '>=', $now->copy()->startOfDay())
            ->where('date', '<=', $now->copy()->addDays(30)->endOfDay())
            ->with(['responsibilities' => function ($query) {
                $query->whereNull('reminded_at')
                    ->with(['person.church']);
            }, 'ministry'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($events as $event) {
            if (empty($event->reminder_settings)) {
                continue;
            }

            // Check each reminder setting
            foreach ($event->reminder_settings as $reminder) {
                if ($this->shouldSendReminder($event, $reminder, $now)) {
                    $this->info("  Event #{$event->id}: {$event->title} - sending reminders");

                    // Filter responsibilities based on recipients setting
                    $responsibilities = $this->filterRecipients($event->responsibilities, $reminder);

                    foreach ($responsibilities as $responsibility) {
                        $church = $responsibility->person->church ?? null;

                        if (!$church || !$church->telegram_bot_token || !$responsibility->person->telegram_chat_id) {
                            continue;
                        }

                        try {
                            $telegram = new TelegramService($church->telegram_bot_token);
                            $telegram->sendResponsibilityReminder($responsibility);

                            $responsibility->update(['reminded_at' => now()]);
                            $sent++;

                            $this->line("    Sent reminder to {$responsibility->person->full_name}");
                        } catch (\Exception $e) {
                            $failed++;
                            $this->error("    Failed to send to {$responsibility->person->full_name}: {$e->getMessage()}");
                        }
                    }
                }
            }
        }

        $this->info("Done! Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }

    /**
     * Filter responsibilities based on reminder recipients setting
     */
    private function filterRecipients($responsibilities, array $reminder)
    {
        $recipientsType = $reminder['recipients'] ?? 'all';
        $personIds = $reminder['person_ids'] ?? [];

        return $responsibilities->filter(function ($resp) use ($recipientsType, $personIds) {
            switch ($recipientsType) {
                case 'confirmed':
                    return $resp->status === 'confirmed';
                case 'pending':
                    return $resp->status === 'pending';
                case 'custom':
                    return in_array($resp->person_id, $personIds);
                case 'all':
                default:
                    return true;
            }
        });
    }

    /**
     * Check if reminder should be sent for this event at the current time
     */
    private function shouldSendReminder(Event $event, array $reminder, Carbon $now): bool
    {
        $eventDateTime = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->time->format('H:i'));

        if ($reminder['type'] === 'days') {
            // For days-based reminders, check if we're at the specified time on the correct day
            $reminderDate = $eventDateTime->copy()->subDays($reminder['value']);
            $reminderTime = $reminder['time'] ?? '18:00';

            // Parse the reminder time
            [$hour, $minute] = explode(':', $reminderTime);
            $reminderDateTime = $reminderDate->copy()->setTime((int) $hour, (int) $minute);

            // Check if current time is within 15 minutes of the reminder time
            $diffMinutes = abs($now->diffInMinutes($reminderDateTime, false));

            return $diffMinutes <= 15 && $now >= $reminderDateTime;
        }

        if ($reminder['type'] === 'hours') {
            // For hours-based reminders, check if we're within the time window
            $reminderDateTime = $eventDateTime->copy()->subHours($reminder['value']);

            // Check if current time is within 15 minutes of the reminder time
            $diffMinutes = abs($now->diffInMinutes($reminderDateTime, false));

            return $diffMinutes <= 15 && $now >= $reminderDateTime;
        }

        return false;
    }
}
