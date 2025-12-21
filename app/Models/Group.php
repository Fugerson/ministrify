<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'church_id',
        'leader_id',
        'name',
        'description',
        'meeting_day',
        'meeting_time',
        'location',
        'color',
        'is_active',
    ];

    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'is_active' => 'boolean',
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

    public function attendances(): HasMany
    {
        return $this->hasMany(GroupAttendance::class);
    }

    public function getMeetingDayNameAttribute(): string
    {
        $days = [
            'monday' => 'Понеділок',
            'tuesday' => 'Вівторок',
            'wednesday' => 'Середа',
            'thursday' => 'Четвер',
            'friday' => "П'ятниця",
            'saturday' => 'Субота',
            'sunday' => 'Неділя',
        ];

        return $days[$this->meeting_day] ?? $this->meeting_day;
    }
}
