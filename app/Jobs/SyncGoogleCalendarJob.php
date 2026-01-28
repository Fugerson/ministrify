<?php

namespace App\Jobs;

use App\Models\Church;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGoogleCalendarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $userId;
    protected int $churchId;
    protected string $calendarId;
    protected ?int $ministryId;

    public function __construct(int $userId, int $churchId, string $calendarId, ?int $ministryId = null)
    {
        $this->userId = $userId;
        $this->churchId = $churchId;
        $this->calendarId = $calendarId;
        $this->ministryId = $ministryId;
    }

    public function handle(GoogleCalendarService $service): void
    {
        $user = User::find($this->userId);
        $church = Church::find($this->churchId);

        if (!$user || !$church) {
            Log::warning('SyncGoogleCalendarJob: User or Church not found', [
                'user_id' => $this->userId,
                'church_id' => $this->churchId,
            ]);
            return;
        }

        $result = $service->fullSync($user, $church, $this->calendarId, $this->ministryId);

        if ($result['success']) {
            Log::info('SyncGoogleCalendarJob: Sync completed', [
                'church_id' => $this->churchId,
                'to_google' => $result['to_google'],
                'from_google' => $result['from_google'],
            ]);
        } else {
            Log::error('SyncGoogleCalendarJob: Sync failed', [
                'church_id' => $this->churchId,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncGoogleCalendarJob: Job failed', [
            'church_id' => $this->churchId,
            'error' => $exception->getMessage(),
        ]);
    }
}
