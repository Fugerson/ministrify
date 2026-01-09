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

    // Membership statuses (journey from guest to active member)
    public const STATUS_GUEST = 'guest';
    public const STATUS_NEWCOMER = 'newcomer';
    public const STATUS_MEMBER = 'member';
    public const STATUS_ACTIVE = 'active';

    public const MEMBERSHIP_STATUSES = [
        self::STATUS_GUEST => ['label' => 'Гість', 'color' => '#9ca3af', 'icon' => 'user'],
        self::STATUS_NEWCOMER => ['label' => 'Новоприбулий', 'color' => '#f59e0b', 'icon' => 'star'],
        self::STATUS_MEMBER => ['label' => 'Член церкви', 'color' => '#3b82f6', 'icon' => 'users'],
        self::STATUS_ACTIVE => ['label' => 'Активний член', 'color' => '#10b981', 'icon' => 'badge-check'],
    ];

    // Gender
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const GENDERS = [
        self::GENDER_MALE => 'Чоловік',
        self::GENDER_FEMALE => 'Жінка',
    ];

    // Marital status
    public const MARITAL_SINGLE = 'single';
    public const MARITAL_MARRIED = 'married';
    public const MARITAL_WIDOWED = 'widowed';
    public const MARITAL_DIVORCED = 'divorced';

    public const MARITAL_STATUSES = [
        self::MARITAL_SINGLE => 'Неодружений/а',
        self::MARITAL_MARRIED => 'Одружений/а',
        self::MARITAL_WIDOWED => 'Вдівець/вдова',
        self::MARITAL_DIVORCED => 'Розлучений/а',
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
        'gender',
        'marital_status',
        'telegram_username',
        'telegram_chat_id',
        'photo',
        'address',
        'birth_date',
        'anniversary',
        'first_visit_date',
        'joined_date',
        'baptism_date',
        'church_role',
        'church_role_id',
        'membership_status',
        'notes',
        'last_scheduled_at',
        'times_scheduled_this_month',
        'times_scheduled_this_year',
        'is_shepherd',
        'shepherd_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'anniversary' => 'date',
        'first_visit_date' => 'date',
        'joined_date' => 'date',
        'baptism_date' => 'date',
        'last_scheduled_at' => 'datetime',
        'is_shepherd' => 'boolean',
    ];

    protected $appends = ['full_name'];

    protected static function booted(): void
    {
        static::created(function (Person $person) {
            // Create follow-up tasks for new guests
            if ($person->membership_status === self::STATUS_GUEST) {
                app(\App\Services\VisitorFollowupService::class)->createFollowupTasks($person);
            }
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function churchRoleRelation(): BelongsTo
    {
        return $this->belongsTo(ChurchRole::class, 'church_role_id');
    }

    /**
     * The shepherd assigned to this person
     */
    public function shepherd(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'shepherd_id');
    }

    /**
     * People this person shepherds (if they are a shepherd)
     */
    public function sheep(): HasMany
    {
        return $this->hasMany(Person::class, 'shepherd_id');
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

    public function responsibilities(): HasMany
    {
        return $this->hasMany(EventResponsibility::class);
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

    public function getMembershipStatusLabelAttribute(): string
    {
        return self::MEMBERSHIP_STATUSES[$this->membership_status]['label'] ?? 'Член церкви';
    }

    public function getMembershipStatusColorAttribute(): string
    {
        return self::MEMBERSHIP_STATUSES[$this->membership_status]['color'] ?? '#3b82f6';
    }

    public function getIsBaptizedAttribute(): bool
    {
        return $this->baptism_date !== null;
    }

    public function getGenderLabelAttribute(): ?string
    {
        return $this->gender ? self::GENDERS[$this->gender] ?? null : null;
    }

    public function getMaritalStatusLabelAttribute(): ?string
    {
        return $this->marital_status ? self::MARITAL_STATUSES[$this->marital_status] ?? null : null;
    }

    public function getIsMarriedAttribute(): bool
    {
        return $this->marital_status === self::MARITAL_MARRIED;
    }

    public function getAnniversaryYearsAttribute(): ?int
    {
        if (!$this->anniversary) {
            return null;
        }
        return $this->anniversary->diffInYears(now());
    }

    public function getDaysSinceFirstVisitAttribute(): ?int
    {
        if (!$this->first_visit_date) {
            return null;
        }
        return $this->first_visit_date->diffInDays(now());
    }

    // ==================
    // Relationships for unified system
    // ==================

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function donations(): HasMany
    {
        return $this->transactions()->where('source_type', Transaction::SOURCE_DONATION);
    }

    public function tithes(): HasMany
    {
        return $this->transactions()->where('source_type', Transaction::SOURCE_TITHE);
    }

    /**
     * Get total giving for this year
     */
    public function getTotalGivingThisYearAttribute(): float
    {
        return $this->transactions()
            ->incoming()
            ->completed()
            ->thisYear()
            ->sum('amount');
    }

    /**
     * Promote membership status
     */
    public function promoteStatus(): void
    {
        $order = [self::STATUS_GUEST, self::STATUS_NEWCOMER, self::STATUS_MEMBER, self::STATUS_ACTIVE];
        $currentIndex = array_search($this->membership_status, $order);

        if ($currentIndex !== false && $currentIndex < count($order) - 1) {
            $this->update(['membership_status' => $order[$currentIndex + 1]]);
        }
    }

    /**
     * Check if person is serving in any ministry
     */
    public function getIsServingAttribute(): bool
    {
        return $this->ministries()->exists();
    }

    /**
     * Scopes for membership status
     */
    public function scopeGuests($query)
    {
        return $query->where('membership_status', self::STATUS_GUEST);
    }

    public function scopeNewcomers($query)
    {
        return $query->where('membership_status', self::STATUS_NEWCOMER);
    }

    public function scopeMembers($query)
    {
        return $query->whereIn('membership_status', [self::STATUS_MEMBER, self::STATUS_ACTIVE]);
    }

    public function scopeActiveMembers($query)
    {
        return $query->where('membership_status', self::STATUS_ACTIVE);
    }

    public function scopeBaptized($query)
    {
        return $query->whereNotNull('baptism_date');
    }

    public function scopeServing($query)
    {
        return $query->whereHas('ministries');
    }

    public function scopeShepherds($query)
    {
        return $query->where('is_shepherd', true);
    }

    // ========== FAMILY RELATIONSHIPS ==========

    /**
     * Get all family relationships where this person is the primary
     */
    public function familyRelationships(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class, 'person_id');
    }

    /**
     * Get all family relationships where this person is the related person
     */
    public function inverseFamilyRelationships(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class, 'related_person_id');
    }

    /**
     * Get all family members (combined from both sides)
     */
    public function getFamilyMembersAttribute(): \Illuminate\Support\Collection
    {
        $directRelations = $this->familyRelationships()
            ->with('relatedPerson')
            ->get()
            ->map(function ($rel) {
                return (object) [
                    'person' => $rel->relatedPerson,
                    'relationship_type' => $rel->relationship_type,
                    'relationship_label' => $rel->getTypeLabel(),
                    'relationship_id' => $rel->id,
                ];
            });

        $inverseRelations = $this->inverseFamilyRelationships()
            ->with('person')
            ->get()
            ->map(function ($rel) {
                return (object) [
                    'person' => $rel->person,
                    'relationship_type' => FamilyRelationship::getInverseType($rel->relationship_type),
                    'relationship_label' => FamilyRelationship::getTypes()[FamilyRelationship::getInverseType($rel->relationship_type)] ?? $rel->relationship_type,
                    'relationship_id' => $rel->id,
                ];
            });

        return $directRelations->concat($inverseRelations)->unique(fn($item) => $item->person->id);
    }

    /**
     * Get spouse
     */
    public function getSpouseAttribute(): ?Person
    {
        $rel = $this->familyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_SPOUSE)
            ->with('relatedPerson')
            ->first();

        if ($rel) {
            return $rel->relatedPerson;
        }

        $inverseRel = $this->inverseFamilyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_SPOUSE)
            ->with('person')
            ->first();

        return $inverseRel?->person;
    }

    /**
     * Get children
     */
    public function getChildrenAttribute(): \Illuminate\Support\Collection
    {
        $directChildren = $this->familyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_CHILD)
            ->with('relatedPerson')
            ->get()
            ->pluck('relatedPerson');

        $inverseChildren = $this->inverseFamilyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_PARENT)
            ->with('person')
            ->get()
            ->pluck('person');

        return $directChildren->concat($inverseChildren)->unique('id');
    }

    /**
     * Get parents
     */
    public function getParentsAttribute(): \Illuminate\Support\Collection
    {
        $directParents = $this->familyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_PARENT)
            ->with('relatedPerson')
            ->get()
            ->pluck('relatedPerson');

        $inverseParents = $this->inverseFamilyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_CHILD)
            ->with('person')
            ->get()
            ->pluck('person');

        return $directParents->concat($inverseParents)->unique('id');
    }

    /**
     * Get siblings
     */
    public function getSiblingsAttribute(): \Illuminate\Support\Collection
    {
        $directSiblings = $this->familyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_SIBLING)
            ->with('relatedPerson')
            ->get()
            ->pluck('relatedPerson');

        $inverseSiblings = $this->inverseFamilyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_SIBLING)
            ->with('person')
            ->get()
            ->pluck('person');

        return $directSiblings->concat($inverseSiblings)->unique('id');
    }

    /**
     * Check if person has any family members
     */
    public function getHasFamilyAttribute(): bool
    {
        return $this->familyRelationships()->exists() || $this->inverseFamilyRelationships()->exists();
    }

    // ========== VOLUNTEER SCHEDULING ==========

    public function blockoutDates(): HasMany
    {
        return $this->hasMany(BlockoutDate::class);
    }

    public function activeBlockouts(): HasMany
    {
        return $this->hasMany(BlockoutDate::class)->active()->upcoming();
    }

    public function schedulingPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SchedulingPreference::class);
    }

    /**
     * Check if person has a blockout on a specific date
     */
    public function hasBlockoutOn($date, $ministryId = null): bool
    {
        $query = $this->blockoutDates()->active()->forDate($date);

        if ($ministryId) {
            $query->forMinistry($ministryId);
        }

        return $query->exists();
    }

    /**
     * Get blockout reason for a specific date
     */
    public function getBlockoutReasonFor($date, $ministryId = null): ?string
    {
        $query = $this->blockoutDates()->active()->forDate($date);

        if ($ministryId) {
            $query->forMinistry($ministryId);
        }

        $blockout = $query->first();

        return $blockout ? $blockout->reason_label : null;
    }

    /**
     * Get "last scheduled" formatted string
     */
    public function getLastScheduledLabelAttribute(): string
    {
        if (!$this->last_scheduled_at) {
            return 'Ніколи';
        }

        $weeks = $this->last_scheduled_at->diffInWeeks(now());

        if ($weeks === 0) return 'Цього тижня';
        if ($weeks === 1) return '1 тиж. тому';
        if ($weeks < 4) return "+{$weeks}т";

        $months = $this->last_scheduled_at->diffInMonths(now());
        if ($months < 12) return "+{$months}м";

        return $this->last_scheduled_at->format('d.m.Y');
    }

    /**
     * Get or create scheduling preference
     */
    public function getOrCreateSchedulingPreference(): SchedulingPreference
    {
        return SchedulingPreference::getOrCreate($this->id, $this->church_id);
    }

    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class);
    }
}
