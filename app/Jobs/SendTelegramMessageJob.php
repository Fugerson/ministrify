<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public string $chatId,
        public string $text,
        public ?array $keyboard = null,
    ) {
        $this->onQueue('telegram');
    }

    public function handle(): void
    {
        $botToken = config('services.telegram.bot_token');

        if (! $botToken) {
            Log::warning('SendTelegramMessageJob: Bot token not configured');

            return;
        }

        $data = [
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'parse_mode' => 'HTML',
        ];

        if ($this->keyboard) {
            $data['reply_markup'] = json_encode([
                'inline_keyboard' => $this->keyboard,
            ]);
        }

        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $data);

        if (! $response->ok() || ! ($response->json()['ok'] ?? false)) {
            Log::warning('SendTelegramMessageJob: Failed', [
                'chat_id' => $this->chatId,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            if ($response->status() === 429) {
                $retryAfter = $response->json()['parameters']['retry_after'] ?? 30;
                $this->release($retryAfter);
            }
        }
    }
}
