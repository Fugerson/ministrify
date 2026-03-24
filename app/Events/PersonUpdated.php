<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $churchId,
        public int $personId,
        public string $action,
        public ?string $personName = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('church.'.$this->churchId.'.people'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'person.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'person_id' => $this->personId,
            'action' => $this->action,
            'person_name' => $this->personName,
        ];
    }
}
