<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Events\LongWaitDetected;

class SendHorizonAlertToTelegram
{
    public function handle(LongWaitDetected $event): void
    {
        $botToken = config('services.telegram.alert_bot_token');
        $chatId = config('services.telegram.alert_chat_id');

        if (! $botToken || ! $chatId) {
            return;
        }

        $message = "⚠️ *Horizon: Long Queue Wait*\n\n";
        $message .= "Connection: `{$event->connection}`\n";
        $message .= "Queue: `{$event->queue}`\n";
        $message .= 'Time: ' . now()->format('Y-m-d H:i:s') . "\n\n";
        $message .= 'Jobs are waiting longer than expected. Check `/horizon` dashboard.';

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Horizon Telegram alert: ' . $e->getMessage());
        }
    }
}
