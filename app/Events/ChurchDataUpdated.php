<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChurchDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $churchId,
        public string $domain,
        public string $action = 'updated',
        public ?string $label = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('church.'.$this->churchId.'.'.$this->domain),
        ];
    }

    public function broadcastAs(): string
    {
        return 'data.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'domain' => $this->domain,
            'action' => $this->action,
            'label' => $this->label,
        ];
    }
}
