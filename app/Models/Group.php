<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use Auditable;

    protected $fillable = [
        'church_id',
        'leader_id',
        'name',
        'slug',
        'description',
        'color',
        'meeting_day',
        'meeting_time',
        'meeting_location',
        'meeting_schedule',
        'cover_image',
        'is_public',
        'allow_join_requests',
    ];

    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'is_public' => 'boolean',
        'allow_join_requests' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }
}
