<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected static function booted(): void
    {
        // Auto-add leader as member when leader_id is set/changed
        static::saved(function (Group $group) {
            if ($group->leader_id && $group->wasChanged('leader_id')) {
                $group->members()->syncWithoutDetaching([
                    $group->leader_id => ['role' => 'leader'],
                ]);
            }
        });
    }

    /**
     * Cached computed attributes to prevent N+1 queries
     */
    protected array $computedCache = [];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_VACATION = 'vacation';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Активна',
        self::STATUS_PAUSED => 'На паузі',
        self::STATUS_VACATION => 'У відпустці',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => __('app.status_active'),
            self::STATUS_PAUSED => __('app.status_paused'),
            self::STATUS_VACATION => __('app.status_vacation'),
        ];
    }

    public const ROLE_LEADER = 'leader';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_MEMBER = 'member';
    public const ROLE_GUEST = 'guest';

    public const ROLES = [
        self::ROLE_LEADER => 'Лідер',
        self::ROLE_ASSISTANT => 'Помічник',
        self::ROLE_MEMBER => 'Учасник',
        self::ROLE_GUEST => 'Гість',
    ];

    public static function getRoles(): array
    {
        return [
            self::ROLE_LEADER => __('app.role_leader'),
            self::ROLE_ASSISTANT => __('app.role_assistant'),
            self::ROLE_MEMBER => __('app.role_member'),
            self::ROLE_GUEST => __('app.group_role_guest'),
        ];
    }

    protected $fillable = [
        'church_id',
        'leader_id',
        'name',
        'slug',
        'description',
        'color',
        'meeting_day',
        'meeting_time',
        'location',
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

    public function getMeetingDayNameAttribute(): ?string
    {
        if (!$this->meeting_day) return null;

        $days = [
            'monday' => __('app.monday'),
            'tuesday' => __('app.tuesday'),
            'wednesday' => __('app.wednesday'),
            'thursday' => __('app.thursday'),
            'friday' => __('app.friday'),
            'saturday' => __('app.saturday'),
            'sunday' => __('app.sunday'),
        ];

        return $days[$this->meeting_day] ?? $this->meeting_day;
    }

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

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)
            ->withPivot(['role', 'joined_at'])
            ->wherePivot('role', self::ROLE_GUEST)
            ->withTimestamps();
    }

    /**
     * Get all attendances for this group (unified system)
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Legacy: Get old GroupAttendance records
     * @deprecated Use attendances() instead
     */
    public function legacyAttendances(): HasMany
    {
        return $this->hasMany(GroupAttendance::class);
    }

    /**
     * Get last attendance (memoized)
     */
    public function getLastAttendanceAttribute(): ?Attendance
    {
        if (array_key_exists('last_attendance', $this->computedCache)) {
            return $this->computedCache['last_attendance'];
        }
        return $this->computedCache['last_attendance'] = $this->attendances()->orderByDesc('date')->first();
    }

    /**
     * Get average attendance (memoized)
     */
    public function getAverageAttendanceAttribute(): float
    {
        if (isset($this->computedCache['average_attendance'])) {
            return $this->computedCache['average_attendance'];
        }

        $attendances = $this->attendances()->take(10)->get();
        if ($attendances->isEmpty()) return $this->computedCache['average_attendance'] = 0;

        return $this->computedCache['average_attendance'] = round($attendances->avg(fn($a) => $a->total_count ?? $a->members_present ?? 0), 1);
    }

    /**
     * Get attendance trend (memoized)
     */
    public function getAttendanceTrendAttribute(): string
    {
        if (isset($this->computedCache['attendance_trend'])) {
            return $this->computedCache['attendance_trend'];
        }

        $recent = $this->attendances()->orderByDesc('date')->take(4)->get()->map(fn($a) => $a->total_count ?? $a->members_present ?? 0)->reverse()->values();
        if ($recent->count() < 2) return $this->computedCache['attendance_trend'] = 'stable';

        $half = (int) floor($recent->count() / 2);
        $first = $recent->take($half)->avg();
        $last = $recent->skip($half)->avg();

        if ($last > $first * 1.1) return $this->computedCache['attendance_trend'] = 'up';
        if ($last < $first * 0.9) return $this->computedCache['attendance_trend'] = 'down';
        return $this->computedCache['attendance_trend'] = 'stable';
    }

    /**
     * Batch load attendance stats for a collection of groups (prevents N+1)
     */
    public static function loadAttendanceStats(\Illuminate\Support\Collection $groups): void
    {
        if ($groups->isEmpty()) return;

        $groupIds = $groups->pluck('id');

        // Load last 10 attendances for each group in one query
        $attendances = Attendance::whereIn('attendable_id', $groupIds)
            ->where('attendable_type', self::class)
            ->orderByDesc('date')
            ->get()
            ->groupBy('attendable_id');

        foreach ($groups as $group) {
            $groupAttendances = $attendances->get($group->id, collect())->take(10);

            $group->computedCache['last_attendance'] = $groupAttendances->first();
            $group->computedCache['average_attendance'] = $groupAttendances->isEmpty()
                ? 0
                : round($groupAttendances->avg(fn($a) => $a->total_count ?? $a->members_present ?? 0), 1);

            // Calculate trend
            $recent = $groupAttendances->take(4)->map(fn($a) => $a->total_count ?? $a->members_present ?? 0)->reverse()->values();
            if ($recent->count() < 2) {
                $group->computedCache['attendance_trend'] = 'stable';
            } else {
                $first = $recent->take(2)->avg();
                $last = $recent->skip(2)->avg() ?: $recent->last();
                if ($last > $first * 1.1) {
                    $group->computedCache['attendance_trend'] = 'up';
                } elseif ($last < $first * 0.9) {
                    $group->computedCache['attendance_trend'] = 'down';
                } else {
                    $group->computedCache['attendance_trend'] = 'stable';
                }
            }
        }
    }

    /**
     * Create a new attendance record for this group
     */
    public function createAttendance(array $data): Attendance
    {
        return Attendance::create([
            'church_id' => $this->church_id,
            'attendable_type' => self::class,
            'attendable_id' => $this->id,
            'type' => Attendance::TYPE_GROUP,
            'date' => $data['date'] ?? now(),
            'time' => $data['time'] ?? $this->meeting_time,
            'location' => $data['location'] ?? $this->location,
            'total_count' => $data['total_count'] ?? 0,
            'members_present' => $data['members_present'] ?? 0,
            'guests_count' => $data['guests_count'] ?? 0,
            'anonymous_guests_count' => $data['anonymous_guests_count'] ?? 0,
            'total_members' => $this->members()->count(),
            'recorded_by' => $data['recorded_by'] ?? auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Get attendance stats for dashboard (memoized)
     */
    public function getAttendanceStatsAttribute(): array
    {
        if (isset($this->computedCache['attendance_stats'])) {
            return $this->computedCache['attendance_stats'];
        }

        return $this->computedCache['attendance_stats'] = [
            'total_meetings' => $this->attendances()->count(),
            'average_attendance' => $this->average_attendance,
            'last_meeting' => $this->last_attendance,
            'trend' => $this->attendance_trend,
        ];
    }
}
