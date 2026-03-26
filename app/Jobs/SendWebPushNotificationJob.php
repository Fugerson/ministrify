<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWebPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public int $userId,
        public array $payload,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(WebPushService $pushService): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        try {
            $pushService->sendToUser($user, $this->payload);
        } catch (\Exception $e) {
            Log::warning('SendWebPushNotificationJob: Failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
