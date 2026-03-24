<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $churchId,
        public int $attendanceId,
        public ?int $personId,
        public bool $present,
        public int $presentCount,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('church.'.$this->churchId.'.attendance.'.$this->attendanceId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attendance.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'person_id' => $this->personId,
            'present' => $this->present,
            'present_count' => $this->presentCount,
        ];
    }
}
