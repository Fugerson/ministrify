<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResponsibilityStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $eventId,
        public int $responsibilityId,
        public string $personName,
        public string $status,
        public ?string $updatedBy = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('event.' . $this->eventId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'responsibility.status-changed';
    }
}
