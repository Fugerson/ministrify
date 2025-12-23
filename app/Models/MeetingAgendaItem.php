<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAgendaItem extends Model
{
    protected $fillable = [
        'meeting_id',
        'title',
        'description',
        'duration_minutes',
        'responsible_id',
        'sort_order',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MinistryMeeting::class, 'meeting_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'responsible_id');
    }
}
