<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventWorshipTeam extends Model
{
    use HasFactory;

    protected $table = 'event_worship_team';

    protected $fillable = [
        'event_id',
        'event_song_id',
        'person_id',
        'worship_role_id',
        'notes',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function worshipRole(): BelongsTo
    {
        return $this->belongsTo(WorshipRole::class);
    }

    public function eventSong(): BelongsTo
    {
        return $this->belongsTo(EventSong::class);
    }
}
