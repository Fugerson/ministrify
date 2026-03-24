<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $churchId,
        public string $type,
        public float $amount,
        public string $currency,
        public ?string $category,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('church.'.$this->churchId.'.finances'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.created';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'category' => $this->category,
        ];
    }
}
