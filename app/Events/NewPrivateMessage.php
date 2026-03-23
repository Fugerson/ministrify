<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPrivateMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $recipientId,
        public int $messageId,
        public string $senderName,
        public string $contentPreview,
        public string $createdAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-messages.' . $this->recipientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }
}
