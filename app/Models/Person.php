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

    /**
     * Cached computed attributes to prevent N+1 queries
     */
    protected array $computedCache = [];

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

    public static function getGenders(): array
    {
        return [
            self::GENDER_MALE => __('app.gender_male'),
            self::GENDER_FEMALE => __('app.gender_female'),
        ];
    }

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

    public static function getMaritalStatuses(): array
    {
        return [
            self::MARITAL_SINGLE => __('app.marital_single'),
            self::MARITAL_MARRIED => __('app.marital_married'),
            self::MARITAL_WIDOWED => __('app.marital_widowed'),
            self::MARITAL_DIVORCED => __('app.marital_divorced'),
        ];
    }

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
        'iban',
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

    /**
     * Normalize a phone number to a standard format for comparison.
     * Handles Ukrainian numbers: +380XXXXXXXXX, 380XXXXXXXXX, 0XXXXXXXXX → 0XXXXXXXXX
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        // Ukrainian: 380XXXXXXXXX (12 digits) → 0XXXXXXXXX
        if (strlen($digits) === 12 && str_starts_with($digits, '380')) {
            return '0' . substr($digits, 3);
        }

        // Local: 0XXXXXXXXX (10 digits) — already normalized
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return $digits;
        }

        // 9 digits without leading 0 → add it
        if (strlen($digits) === 9) {
            return '0' . $digits;
        }

        return $digits;
    }

    /**
     * Find a Person in a church by phone number (handles format differences).
     * @param bool $unlinkedOnly If true, only find persons without user_id (for join/merge flows)
     */
    public static function findByPhoneInChurch(?string $phone, int $churchId, bool $unlinkedOnly = true): ?self
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        // Extract core 9 digits
        if (strlen($digits) === 12 && str_starts_with($digits, '380')) {
            $core = substr($digits, 3);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $core = substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $core = $digits;
        } else {
            // Non-standard format — try exact match only
            $q = self::where('church_id', $churchId)->where('phone', $phone);
            if ($unlinkedOnly) {
                $q->whereNull('user_id');
            }
            return $q->first();
        }

        // Search all possible formats of this Ukrainian phone number
        $q = self::where('church_id', $churchId)
            ->where(function ($query) use ($core) {
                $query->where('phone', '0' . $core)
                      ->orWhere('phone', '+380' . $core)
                      ->orWhere('phone', '380' . $core)
                      ->orWhere('phone', $core);
            });

        if ($unlinkedOnly) {
            $q->whereNull('user_id');
        }

        return $q->first();
    }

    // Note: Person lifecycle events are handled by PersonObserver
    // See: App\Observers\PersonObserver (registered in EventServiceProvider)

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

    public function worshipSkills(): HasMany
    {
        return $this->hasMany(PersonWorshipSkill::class);
    }

    public function eventWorshipTeam(): HasMany
    {
        return $this->hasMany(EventWorshipTeam::class);
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

    /**
     * Get attendance statistics (memoized to prevent N+1)
     * WARNING: For collections, use Person::loadAttendanceStats($people) instead
     */
    public function getAttendanceStatsAttribute(): array
    {
        if (isset($this->computedCache['attendance_stats'])) {
            return $this->computedCache['attendance_stats'];
        }

        $records = $this->attendanceRecords()
            ->with('attendance')
            ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
            ->get();

        $total = $records->count();
        $present = $records->where('present', true)->count();

        return $this->computedCache['attendance_stats'] = [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'rate' => $total > 0 ? round(($present / $total) * 100) : 0,
        ];
    }

    /**
     * Get last attended date (memoized to prevent N+1)
     * WARNING: For collections, use Person::loadAttendanceStats($people) instead
     */
    public function getLastAttendedAttribute(): ?\Carbon\Carbon
    {
        if (array_key_exists('last_attended', $this->computedCache)) {
            return $this->computedCache['last_attended'];
        }

        return $this->computedCache['last_attended'] = $this->attendanceRecords()
            ->where('present', true)
            ->with('attendance')
            ->get()
            ->sortByDesc(fn($r) => $r->attendance?->date)
            ->first()?->attendance?->date;
    }

    /**
     * Batch load attendance stats for a collection of people (prevents N+1)
     */
    public static function loadAttendanceStats(\Illuminate\Support\Collection $people): void
    {
        if ($people->isEmpty()) return;

        $personIds = $people->pluck('id');
        $threeMonthsAgo = now()->subMonths(3);

        $stats = \DB::table('attendance_records')
            ->join('attendances', 'attendance_records.attendance_id', '=', 'attendances.id')
            ->whereIn('attendance_records.person_id', $personIds)
            ->where('attendances.date', '>=', $threeMonthsAgo)
            ->selectRaw('attendance_records.person_id,
                COUNT(*) as total,
                SUM(CASE WHEN attendance_records.present = 1 THEN 1 ELSE 0 END) as present,
                MAX(CASE WHEN attendance_records.present = 1 THEN attendances.date ELSE NULL END) as last_attended')
            ->groupBy('attendance_records.person_id')
            ->get()
            ->keyBy('person_id');

        foreach ($people as $person) {
            $stat = $stats->get($person->id);
            $total = $stat->total ?? 0;
            $present = $stat->present ?? 0;

            $person->computedCache['attendance_stats'] = [
                'total' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'rate' => $total > 0 ? round(($present / $total) * 100) : 0,
            ];

            $person->computedCache['last_attended'] = $stat->last_attended
                ? \Carbon\Carbon::parse($stat->last_attended)
                : null;
        }
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
        $search = addcslashes($search, '%_');
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

    /**
     * Scope to filter by age category using raw SQL for performance
     */
    public function scopeAgeCategory($query, string $category)
    {
        if (!isset(self::AGE_CATEGORIES[$category])) {
            return $query;
        }

        $config = self::AGE_CATEGORIES[$category];
        return $query->whereNotNull('birth_date')
            ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$config['min']])
            ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$config['max']]);
    }

    /**
     * Scope to filter by age range
     */
    public function scopeAgeBetween($query, int $minAge, int $maxAge)
    {
        return $query->whereNotNull('birth_date')
            ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$minAge])
            ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$maxAge]);
    }

    /**
     * Scope to filter by gender
     */
    public function scopeGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to filter people with birthdays in a given month
     */
    public function scopeBirthdayInMonth($query, int $month)
    {
        return $query->whereNotNull('birth_date')
            ->whereMonth('birth_date', $month);
    }

    /**
     * Scope for upcoming birthdays within N days
     */
    public function scopeUpcomingBirthdays($query, int $days = 30)
    {
        return $query->whereNotNull('birth_date')
            ->whereRaw(
                '(DAYOFYEAR(CURDATE()) + ? <= 366 AND DAYOFYEAR(birth_date) BETWEEN DAYOFYEAR(CURDATE()) AND DAYOFYEAR(CURDATE()) + ?) OR (DAYOFYEAR(CURDATE()) + ? > 366 AND (DAYOFYEAR(birth_date) >= DAYOFYEAR(CURDATE()) OR DAYOFYEAR(birth_date) <= (DAYOFYEAR(CURDATE()) + ?) - 366))',
                [$days, $days, $days, $days]
            );
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
     * Memoized to prevent N+1 queries
     */
    public function getFamilyMembersAttribute(): \Illuminate\Support\Collection
    {
        if (isset($this->computedCache['family_members'])) {
            return $this->computedCache['family_members'];
        }

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

        return $this->computedCache['family_members'] = $directRelations->concat($inverseRelations)->unique(fn($item) => $item->person->id);
    }

    /**
     * Get spouse (memoized)
     */
    public function getSpouseAttribute(): ?Person
    {
        if (array_key_exists('spouse', $this->computedCache)) {
            return $this->computedCache['spouse'];
        }

        $rel = $this->familyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_SPOUSE)
            ->with('relatedPerson')
            ->first();

        if ($rel) {
            return $this->computedCache['spouse'] = $rel->relatedPerson;
        }

        $inverseRel = $this->inverseFamilyRelationships()
            ->where('relationship_type', FamilyRelationship::TYPE_SPOUSE)
            ->with('person')
            ->first();

        return $this->computedCache['spouse'] = $inverseRel?->person;
    }

    /**
     * Get children (memoized)
     */
    public function getChildrenAttribute(): \Illuminate\Support\Collection
    {
        if (isset($this->computedCache['children'])) {
            return $this->computedCache['children'];
        }

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

        return $this->computedCache['children'] = $directChildren->concat($inverseChildren)->unique('id');
    }

    /**
     * Get parents (memoized)
     */
    public function getParentsAttribute(): \Illuminate\Support\Collection
    {
        if (isset($this->computedCache['parents'])) {
            return $this->computedCache['parents'];
        }

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

        return $this->computedCache['parents'] = $directParents->concat($inverseParents)->unique('id');
    }

    /**
     * Get siblings (memoized)
     */
    public function getSiblingsAttribute(): \Illuminate\Support\Collection
    {
        if (isset($this->computedCache['siblings'])) {
            return $this->computedCache['siblings'];
        }

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

        return $this->computedCache['siblings'] = $directSiblings->concat($inverseSiblings)->unique('id');
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
