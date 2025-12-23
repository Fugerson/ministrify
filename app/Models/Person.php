<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'people';

    // Church roles
    public const ROLE_MEMBER = 'member';
    public const ROLE_SERVANT = 'servant';
    public const ROLE_DEACON = 'deacon';
    public const ROLE_PRESBYTER = 'presbyter';
    public const ROLE_PASTOR = 'pastor';

    public const CHURCH_ROLES = [
        self::ROLE_MEMBER => 'Член церкви',
        self::ROLE_SERVANT => 'Служитель',
        self::ROLE_DEACON => 'Диякон',
        self::ROLE_PRESBYTER => 'Пресвітер',
        self::ROLE_PASTOR => 'Пастор',
    ];

    // Age categories
    public const AGE_CHILD = 'child';        // 0-12
    public const AGE_TEEN = 'teen';          // 13-17
    public const AGE_YOUTH = 'youth';        // 18-35
    public const AGE_ADULT = 'adult';        // 36-59
    public const AGE_SENIOR = 'senior';      // 60+

    public const AGE_CATEGORIES = [
        self::AGE_CHILD => ['label' => 'Діти', 'min' => 0, 'max' => 12, 'color' => '#f59e0b'],
        self::AGE_TEEN => ['label' => 'Підлітки', 'min' => 13, 'max' => 17, 'color' => '#8b5cf6'],
        self::AGE_YOUTH => ['label' => 'Молодь', 'min' => 18, 'max' => 35, 'color' => '#3b82f6'],
        self::AGE_ADULT => ['label' => 'Дорослі', 'min' => 36, 'max' => 59, 'color' => '#10b981'],
        self::AGE_SENIOR => ['label' => 'Старші', 'min' => 60, 'max' => 150, 'color' => '#6b7280'],
    ];

    protected $fillable = [
        'church_id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'telegram_username',
        'telegram_chat_id',
        'photo',
        'address',
        'birth_date',
        'joined_date',
        'church_role',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'joined_date' => 'date',
    ];

    protected $appends = ['full_name'];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'person_tag');
    }

    public function ministries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_person')
            ->withPivot('position_ids')
            ->withTimestamps();
    }

    public function leadingMinistries(): HasMany
    {
        return $this->hasMany(Ministry::class, 'leader_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(UnavailableDate::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function leadingGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'leader_id');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(PersonCommunication::class)->orderByDesc('communicated_at');
    }

    public function getAttendanceStatsAttribute(): array
    {
        $records = $this->attendanceRecords()
            ->with('attendance')
            ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
            ->get();

        $total = $records->count();
        $present = $records->where('present', true)->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'rate' => $total > 0 ? round(($present / $total) * 100) : 0,
        ];
    }

    public function getLastAttendedAttribute(): ?\Carbon\Carbon
    {
        return $this->attendanceRecords()
            ->where('present', true)
            ->with('attendance')
            ->get()
            ->sortByDesc(fn($r) => $r->attendance->date)
            ->first()?->attendance?->date;
    }

    public function getMembershipDurationAttribute(): string
    {
        if (!$this->joined_date) {
            return 'Невідомо';
        }

        $diff = $this->joined_date->diff(now());

        if ($diff->y > 0) {
            return $diff->y . ' ' . trans_choice('рік|роки|років', $diff->y);
        }
        if ($diff->m > 0) {
            return $diff->m . ' ' . trans_choice('місяць|місяці|місяців', $diff->m);
        }
        return $diff->d . ' ' . trans_choice('день|дні|днів', $diff->d);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isAvailableOn(\DateTime $date): bool
    {
        return !$this->unavailableDates()
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->exists();
    }

    public function hasPositionInMinistry(Ministry $ministry, Position $position): bool
    {
        $pivot = $this->ministries()->where('ministry_id', $ministry->id)->first()?->pivot;

        if (!$pivot || !$pivot->position_ids) {
            return false;
        }

        $positionIds = is_array($pivot->position_ids)
            ? $pivot->position_ids
            : json_decode($pivot->position_ids, true);

        return in_array($position->id, $positionIds ?? []);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeWithTag($query, int $tagId)
    {
        return $query->whereHas('tags', fn($q) => $q->where('tags.id', $tagId));
    }

    public function scopeInMinistry($query, int $ministryId)
    {
        return $query->whereHas('ministries', fn($q) => $q->where('ministries.id', $ministryId));
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        return $this->birth_date->age;
    }

    public function getAgeCategoryAttribute(): ?string
    {
        $age = $this->age;
        if ($age === null) {
            return null;
        }

        foreach (self::AGE_CATEGORIES as $key => $category) {
            if ($age >= $category['min'] && $age <= $category['max']) {
                return $key;
            }
        }
        return null;
    }

    public function getAgeCategoryLabelAttribute(): ?string
    {
        $category = $this->age_category;
        return $category ? self::AGE_CATEGORIES[$category]['label'] : null;
    }

    public function getChurchRoleLabelAttribute(): string
    {
        return self::CHURCH_ROLES[$this->church_role] ?? 'Член церкви';
    }
}
