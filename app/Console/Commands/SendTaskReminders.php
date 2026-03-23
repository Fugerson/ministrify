<?php

namespace App\Console\Commands;

use App\Models\BoardCard;
use App\Models\Church;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'app:send-task-reminders';

    protected $description = 'Send reminders for tasks with upcoming deadlines';

    public function handle(): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $this->info("Checking task deadlines for {$today->format('Y-m-d')}");

        if (! config('services.telegram.bot_token')) {
            $this->warn('Telegram bot not configured');

            return self::SUCCESS;
        }

        $churches = Church::all();
        $sent = 0;

        foreach ($churches as $church) {
            // Get notification settings
            $settings = $church->settings['notifications'] ?? [];

            // Check if task reminders are enabled (default: true)
            if (! ($settings['task_reminders'] ?? true)) {
                continue;
            }

            // Find tasks due today (not completed)
            $tasksDueToday = BoardCard::whereHas('column.board', fn ($q) => $q->where('church_id', $church->id))
                ->where('is_completed', false)
                ->whereDate('due_date', $today)
                ->with(['assignee', 'column.board'])
                ->get();

            // Find tasks due tomorrow (not completed)
            $tasksDueTomorrow = BoardCard::whereHas('column.board', fn ($q) => $q->where('church_id', $church->id))
                ->where('is_completed', false)
                ->whereDate('due_date', $tomorrow)
                ->with(['assignee', 'column.board'])
                ->get();

            // Find overdue tasks
            $tasksOverdue = BoardCard::whereHas('column.board', fn ($q) => $q->where('church_id', $church->id))
                ->where('is_completed', false)
                ->whereDate('due_date', '<', $today)
                ->with(['assignee', 'column.board'])
                ->get();

            if ($tasksDueToday->isEmpty() && $tasksDueTomorrow->isEmpty() && $tasksOverdue->isEmpty()) {
                continue;
            }

            try {
                $telegram = TelegramService::make();

                // Group tasks by assignee
                $allTasks = $tasksDueToday->merge($tasksDueTomorrow)->merge($tasksOverdue);
                $tasksByAssignee = $allTasks->groupBy('assigned_to');

                foreach ($tasksByAssignee as $assigneeId => $tasks) {
                    if (! $assigneeId) {
                        continue;
                    }

                    $assignee = $tasks->first()->assignee;

                    if (! $assignee || ! $assignee->telegram_chat_id) {
                        continue;
                    }

                    $personTasksToday = $tasks->filter(fn ($t) => $t->due_date->isToday());
                    $personTasksTomorrow = $tasks->filter(fn ($t) => $t->due_date->isTomorrow());
                    $personTasksOverdue = $tasks->filter(fn ($t) => $t->due_date->isPast() && ! $t->due_date->isToday());

                    $message = $this->buildMessage($personTasksToday, $personTasksTomorrow, $personTasksOverdue, $church);

                    if ($telegram->sendMessage($assignee->telegram_chat_id, $message)) {
                        $sent++;
                        $this->line("  Sent to {$assignee->full_name}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("  Error for church {$church->name}: {$e->getMessage()}");
            }
        }

        $this->info("Done! Sent {$sent} task reminders.");

        return self::SUCCESS;
    }

    private function buildMessage($tasksToday, $tasksTomorrow, $tasksOverdue, Church $church): string
    {
        $message = "📋 <b>Нагадування про завдання</b>\n";
        $message .= "⛪ {$church->name}\n\n";

        if ($tasksOverdue->isNotEmpty()) {
            $message .= "🚨 <b>Прострочено:</b>\n";
            foreach ($tasksOverdue as $task) {
                $days = $task->due_date->diffInDays(now());
                $message .= "• {$task->title} ({$days} дн.)\n";
            }
            $message .= "\n";
        }

        if ($tasksToday->isNotEmpty()) {
            $message .= "⏰ <b>Сьогодні:</b>\n";
            foreach ($tasksToday as $task) {
                $priority = $this->getPriorityIcon($task->priority);
                $message .= "• {$priority} {$task->title}\n";
            }
            $message .= "\n";
        }

        if ($tasksTomorrow->isNotEmpty()) {
            $message .= "📅 <b>Завтра:</b>\n";
            foreach ($tasksTomorrow as $task) {
                $priority = $this->getPriorityIcon($task->priority);
                $message .= "• {$priority} {$task->title}\n";
            }
        }

        return $message;
    }

    private function getPriorityIcon(?string $priority): string
    {
        return match ($priority) {
            'urgent' => '🔴',
            'high' => '🟠',
            'medium' => '🟡',
            default => '⚪',
        };
    }
}
