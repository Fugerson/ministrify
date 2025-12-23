<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use Auditable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_VACATION = 'vacation';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Активна',
        self::STATUS_PAUSED => 'На паузі',
        self::STATUS_VACATION => 'У відпустці',
    ];

    public const ROLE_LEADER = 'leader';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_MEMBER = 'member';

    public const ROLES = [
        self::ROLE_LEADER => 'Лідер',
        self::ROLE_ASSISTANT => 'Помічник',
        self::ROLE_MEMBER => 'Учасник',
    ];

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
        'status',
    ];

    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'is_public' => 'boolean',
        'allow_join_requests' => 'boolean',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Невідомо';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_PAUSED => 'yellow',
            self::STATUS_VACATION => 'blue',
            default => 'gray',
        };
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

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

    public function assistants(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)
            ->withPivot(['role', 'joined_at'])
            ->wherePivot('role', self::ROLE_ASSISTANT)
            ->withTimestamps();
    }

    public function regularMembers(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)
            ->withPivot(['role', 'joined_at'])
            ->wherePivot('role', self::ROLE_MEMBER)
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(GroupAttendance::class);
    }

    public function getLastAttendanceAttribute(): ?GroupAttendance
    {
        return $this->attendances()->orderByDesc('date')->first();
    }

    public function getAverageAttendanceAttribute(): float
    {
        $attendances = $this->attendances()->take(10)->get();
        if ($attendances->isEmpty()) return 0;

        return round($attendances->avg('members_present'), 1);
    }

    public function getAttendanceTrendAttribute(): string
    {
        $recent = $this->attendances()->orderByDesc('date')->take(4)->pluck('members_present')->reverse()->values();
        if ($recent->count() < 2) return 'stable';

        $first = $recent->take(2)->avg();
        $last = $recent->skip(2)->avg() ?: $recent->last();

        if ($last > $first * 1.1) return 'up';
        if ($last < $first * 0.9) return 'down';
        return 'stable';
    }
}
