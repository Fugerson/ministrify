<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendee extends Model
{
    protected $fillable = [
        'meeting_id',
        'person_id',
        'status',
        'notes',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MinistryMeeting::class, 'meeting_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'invited' => 'Запрошено',
            'confirmed' => 'Підтверджено',
            'attended' => 'Був присутній',
            'absent' => 'Був відсутній',
            default => $this->status,
        };
    }
}
