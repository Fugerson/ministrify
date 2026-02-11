<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Ministry extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    // Visibility options
    public const VISIBILITY_PUBLIC = 'public';      // Everyone can see
    public const VISIBILITY_MEMBERS = 'members';    // Only members can see
    public const VISIBILITY_LEADERS = 'leaders';    // Only admins and ministry leaders can see
    public const VISIBILITY_SPECIFIC = 'specific';  // Only specific people can see

    public const VISIBILITY_OPTIONS = [
        self::VISIBILITY_PUBLIC => 'Всі користувачі',
        self::VISIBILITY_MEMBERS => 'Тільки учасники команди',
        self::VISIBILITY_LEADERS => 'Тільки адміни та лідери служінь',
        self::VISIBILITY_SPECIFIC => 'Тільки конкретні люди',
    ];

    protected $fillable = [
        'church_id',
        'type_id',
        'name',
        'description',
        'vision',
        'icon',
        'color',
        'leader_id',
        'monthly_budget',
        'is_public',
        'is_worship_ministry',
        'is_sunday_service_part',
        'slug',
        'public_description',
        'cover_image',
        'allow_registrations',
        'is_private',
        'visibility',
        'allowed_person_ids',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'is_public' => 'boolean',
        'is_worship_ministry' => 'boolean',
        'is_sunday_service_part' => 'boolean',
        'allow_registrations' => 'boolean',
        'is_private' => 'boolean',
        'allowed_person_ids' => 'array',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MinistryType::class, 'type_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'leader_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('sort_order');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'ministry_person')
            ->withPivot('position_ids')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function ministryRoles(): HasMany
    {
        return $this->hasMany(MinistryRole::class)->orderBy('sort_order');
    }

    public function eventMinistryTeam(): HasMany
    {
        return $this->hasMany(EventMinistryTeam::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(MinistryBudget::class);
    }

    public function currentBudget(): ?MinistryBudget
    {
        return $this->budgets()
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(MinistryMeeting::class)->orderByDesc('date');
    }

    public function upcomingMeetings(): HasMany
    {
        return $this->hasMany(MinistryMeeting::class)
            ->where('date', '>=', now()->startOfDay())
            ->where('status', '!=', 'cancelled')
            ->orderBy('date');
    }

    public function getSpentThisMonthAttribute(): float
    {
        return $this->expenses()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }

    public function getBudgetUsagePercentAttribute(): float
    {
        if (!$this->monthly_budget || $this->monthly_budget == 0) {
            return 0;
        }

        return min(100, round(($this->spent_this_month / $this->monthly_budget) * 100, 1));
    }

    public function getRemainingBudgetAttribute(): float
    {
        return max(0, ($this->monthly_budget ?? 0) - $this->spent_this_month);
    }

    /**
     * Check if budget is at warning level (80%+)
     */
    public function isBudgetWarning(): bool
    {
        return $this->monthly_budget > 0 && $this->budget_usage_percent >= 80;
    }

    /**
     * Check if budget is exceeded (100%+)
     */
    public function isBudgetExceeded(): bool
    {
        return $this->monthly_budget > 0 && $this->spent_this_month >= $this->monthly_budget;
    }

    /**
     * Check if expense can be added within budget
     */
    public function canAddExpense(float $amount): array
    {
        if (!$this->monthly_budget || $this->monthly_budget <= 0) {
            return ['allowed' => true, 'warning' => null];
        }

        $newTotal = $this->spent_this_month + $amount;
        $newPercentage = ($newTotal / $this->monthly_budget) * 100;

        if ($newTotal > $this->monthly_budget) {
            return [
                'allowed' => false,
                'warning' => 'exceeded',
                'message' => sprintf(
                    'Ця витрата перевищить бюджет на %.2f грн. Залишок: %.2f грн.',
                    $newTotal - $this->monthly_budget,
                    $this->remaining_budget
                ),
            ];
        }

        if ($newPercentage >= 80) {
            return [
                'allowed' => true,
                'warning' => 'high',
                'message' => sprintf(
                    'Увага! Після цієї витрати буде використано %.1f%% бюджету.',
                    $newPercentage
                ),
            ];
        }

        return ['allowed' => true, 'warning' => null];
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(MinistryJoinRequest::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(MinistryGoal::class);
    }

    public function activeGoals(): HasMany
    {
        return $this->goals()->where('status', 'active');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MinistryTask::class);
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->whereIn('status', ['todo', 'in_progress']);
    }

    public function getHasVisionAttribute(): bool
    {
        return !empty($this->vision);
    }

    public function getGoalsProgressAttribute(): int
    {
        $goals = $this->activeGoals;
        if ($goals->isEmpty()) {
            return 0;
        }
        return (int) round($goals->avg('calculated_progress'));
    }

    public function pendingJoinRequests(): HasMany
    {
        return $this->joinRequests()->where('status', 'pending');
    }

    public function getPublicUrlAttribute(): string
    {
        if (!$this->church) {
            return '#';
        }
        return route('public.ministry', [$this->church->slug, $this->slug]);
    }

    /**
     * Check if user can access this ministry page
     */
    public function canAccess(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Admins can always access
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user is in allowed_person_ids list
        $allowedPersonIds = $this->allowed_person_ids ?? [];
        if ($user->person && in_array($user->person->id, $allowedPersonIds)) {
            return true;
        }

        $visibility = $this->visibility ?? self::VISIBILITY_PUBLIC;

        // Public - everyone can access
        if ($visibility === self::VISIBILITY_PUBLIC) {
            return true;
        }

        // Specific - only allowed_person_ids (already checked above)
        if ($visibility === self::VISIBILITY_SPECIFIC) {
            return false;
        }

        // Leaders visibility - only admins and leaders of THIS ministry
        if ($visibility === self::VISIBILITY_LEADERS) {
            if ($user->person && $this->leader_id === $user->person->id) {
                return true;
            }
            return false;
        }

        // Members visibility - members, leaders, admins
        if ($visibility === self::VISIBILITY_MEMBERS) {
            // Leaders of this ministry can access
            if ($user->person && $this->leader_id === $user->person->id) {
                return true;
            }

            // Members can access
            if ($user->person && $this->members()->where('person_id', $user->person->id)->exists()) {
                return true;
            }

            return false;
        }

        // Fallback for legacy is_private
        if ($this->is_private) {
            if ($user->person && $this->leader_id === $user->person->id) {
                return true;
            }
            if ($user->person && $this->members()->where('person_id', $user->person->id)->exists()) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Check if user is a member of this ministry
     */
    public function isMember(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user || !$user->person) {
            return false;
        }

        return $this->leader_id === $user->person->id
            || $this->members()->where('person_id', $user->person->id)->exists();
    }
}
