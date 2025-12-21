<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Church;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders {--days=1 : Days before event} {--hours=0 : Hours before event}';
    protected $description = 'Send reminders for upcoming assignments';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $hours = (int) $this->option('hours');

        $targetDate = now();
        if ($days > 0) {
            $targetDate = now()->addDays($days)->startOfDay();
        }

        $this->info("Sending reminders for events on {$targetDate->format('Y-m-d')}");

        $assignments = Assignment::with(['person.church', 'event.ministry', 'position'])
            ->whereHas('event', function ($query) use ($targetDate, $days, $hours) {
                if ($hours > 0) {
                    $query->whereBetween('date', [
                        now()->startOfDay(),
                        now()->endOfDay(),
                    ])->whereRaw("TIME(time) BETWEEN ? AND ?", [
                        now()->addHours($hours)->format('H:i:s'),
                        now()->addHours($hours + 1)->format('H:i:s'),
                    ]);
                } else {
                    $query->whereDate('date', $targetDate);
                }
            })
            ->where('status', 'confirmed')
            ->whereNull('reminded_at')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($assignments as $assignment) {
            $church = $assignment->person->church;

            if (!$church->telegram_bot_token || !$assignment->person->telegram_chat_id) {
                continue;
            }

            try {
                $telegram = new TelegramService($church->telegram_bot_token);
                $telegram->sendReminder($assignment);

                $assignment->update(['reminded_at' => now()]);
                $sent++;

                $this->line("  Sent reminder to {$assignment->person->full_name}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("  Failed to send to {$assignment->person->full_name}: {$e->getMessage()}");
            }
        }

        $this->info("Done! Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
