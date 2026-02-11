<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventMinistryTeam extends Model
{
    use HasFactory;

    protected $table = 'event_ministry_team';

    protected $fillable = [
        'event_id',
        'ministry_id',
        'person_id',
        'ministry_role_id',
        'notes',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function ministryRole(): BelongsTo
    {
        return $this->belongsTo(MinistryRole::class);
    }
}
