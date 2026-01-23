<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Auditable, SoftDeletes;

    protected $fillable = [
        'church_id',
        'name',
        'email',
        'password',
        'google_id',
        'church_role_id',
        'is_super_admin',
        'theme',
        'preferences',
        'onboarding_completed',
        'onboarding_state',
        'onboarding_started_at',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_set_at' => 'datetime',
        'preferences' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_state' => 'array',
        'onboarding_started_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }

    public function churchRole(): BelongsTo
    {
        return $this->belongsTo(ChurchRole::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function isAdmin(): bool
    {
        return $this->churchRole?->is_admin_role === true;
    }

    public function isLeader(): bool
    {
        // Check if user has a role with 'leader' slug or name contains 'лідер'
        if (!$this->churchRole) {
            return false;
        }
        return $this->churchRole->slug === 'leader'
            || str_contains(mb_strtolower($this->churchRole->name), 'лідер');
    }

    public function isVolunteer(): bool
    {
        // User is volunteer if they have a role that's not admin and not leader
        if (!$this->churchRole) {
            return false;
        }
        return !$this->isAdmin() && !$this->isLeader();
    }

    /**
     * Check if user has one of the specified roles
     * Supports both legacy role names and ChurchRole slugs
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->churchRole) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            // Check by admin status
            if ($role === 'admin' && $this->isAdmin()) {
                return true;
            }
            // Check by leader status
            if ($role === 'leader' && $this->isLeader()) {
                return true;
            }
            // Check by volunteer status (any non-admin, non-leader)
            if ($role === 'volunteer' && $this->isVolunteer()) {
                return true;
            }
            // Check by exact slug match
            if ($this->churchRole->slug === $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get display name for user's role
     */
    public function getRoleNameAttribute(): string
    {
        return $this->churchRole?->name ?? 'Без ролі';
    }

    /**
     * Check if user has permission for module and action
     */
    public function hasPermission(string $module, string $action = 'view'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->church_id) {
            return false;
        }

        // Use ChurchRole permission system
        if ($this->churchRole) {
            return $this->churchRole->hasPermission($module, $action);
        }

        return false;
    }


    /**
     * Check if user can view module
     */
    public function canView(string $module): bool
    {
        return $this->hasPermission($module, 'view');
    }

    /**
     * Check if user can create in module
     */
    public function canCreate(string $module): bool
    {
        return $this->hasPermission($module, 'create');
    }

    /**
     * Check if user can edit in module
     */
    public function canEdit(string $module): bool
    {
        return $this->hasPermission($module, 'edit');
    }

    /**
     * Check if user can delete in module
     */
    public function canDelete(string $module): bool
    {
        return $this->hasPermission($module, 'delete');
    }

    public function canManageMinistry(Ministry $ministry): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isLeader() && $this->person) {
            return $ministry->leader_id === $this->person->id;
        }

        return false;
    }

    // Onboarding Methods

    public const ONBOARDING_STEPS = [
        'welcome' => ['order' => 1, 'required' => true, 'title' => 'Вітаємо', 'icon' => 'hand-raised'],
        'church_profile' => ['order' => 2, 'required' => true, 'title' => 'Профіль церкви', 'icon' => 'building-library'],
        'first_ministry' => ['order' => 3, 'required' => false, 'title' => 'Служіння', 'icon' => 'user-group'],
        'add_people' => ['order' => 4, 'required' => false, 'title' => 'Люди', 'icon' => 'users'],
        'set_roles' => ['order' => 5, 'required' => false, 'title' => 'Ролі', 'icon' => 'shield-check'],
        'feature_tour' => ['order' => 6, 'required' => true, 'title' => 'Огляд', 'icon' => 'sparkles'],
    ];

    public function needsOnboarding(): bool
    {
        return $this->isAdmin() && !$this->isSuperAdmin() && !$this->onboarding_completed;
    }

    public function startOnboarding(): void
    {
        $steps = [];
        foreach (self::ONBOARDING_STEPS as $key => $step) {
            $steps[$key] = [
                'completed' => false,
                'skipped' => false,
                'completed_at' => null,
            ];
        }

        $this->update([
            'onboarding_state' => [
                'current_step' => 'welcome',
                'steps' => $steps,
                'dismissed_hints' => [],
            ],
            'onboarding_started_at' => now(),
        ]);
    }

    public function getOnboardingStep(string $step): ?array
    {
        $state = $this->onboarding_state ?? [];
        return $state['steps'][$step] ?? null;
    }

    public function getCurrentOnboardingStep(): string
    {
        return $this->onboarding_state['current_step'] ?? 'welcome';
    }

    public function completeOnboardingStep(string $step, array $data = []): void
    {
        $state = $this->onboarding_state ?? [];
        $state['steps'][$step] = [
            'completed' => true,
            'skipped' => false,
            'completed_at' => now()->toISOString(),
            'data' => $data,
        ];

        // Move to next step
        $steps = array_keys(self::ONBOARDING_STEPS);
        $currentIndex = array_search($step, $steps);
        if ($currentIndex !== false && $currentIndex < count($steps) - 1) {
            $state['current_step'] = $steps[$currentIndex + 1];
        }

        $this->update(['onboarding_state' => $state]);
    }

    public function skipOnboardingStep(string $step): void
    {
        $stepConfig = self::ONBOARDING_STEPS[$step] ?? null;
        if (!$stepConfig || $stepConfig['required']) {
            return; // Cannot skip required steps
        }

        $state = $this->onboarding_state ?? [];
        $state['steps'][$step] = [
            'completed' => false,
            'skipped' => true,
            'skipped_at' => now()->toISOString(),
        ];

        // Move to next step
        $steps = array_keys(self::ONBOARDING_STEPS);
        $currentIndex = array_search($step, $steps);
        if ($currentIndex !== false && $currentIndex < count($steps) - 1) {
            $state['current_step'] = $steps[$currentIndex + 1];
        }

        $this->update(['onboarding_state' => $state]);
    }

    public function finishOnboarding(): void
    {
        $this->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);
    }

    public function restartOnboarding(): void
    {
        $this->update([
            'onboarding_completed' => false,
            'onboarding_completed_at' => null,
            'onboarding_state' => null,
            'onboarding_started_at' => null,
        ]);
        $this->startOnboarding();
    }

    public function getOnboardingProgress(): array
    {
        $state = $this->onboarding_state ?? [];
        $steps = $state['steps'] ?? [];

        $total = count(self::ONBOARDING_STEPS);
        $completed = 0;

        foreach ($steps as $step) {
            if ($step['completed'] || $step['skipped']) {
                $completed++;
            }
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function dismissOnboardingHint(string $hint): void
    {
        $state = $this->onboarding_state ?? [];
        $hints = $state['dismissed_hints'] ?? [];

        if (!in_array($hint, $hints)) {
            $hints[] = $hint;
            $state['dismissed_hints'] = $hints;
            $this->update(['onboarding_state' => $state]);
        }
    }

    public function hasSeenOnboardingHint(string $hint): bool
    {
        $state = $this->onboarding_state ?? [];
        $hints = $state['dismissed_hints'] ?? [];
        return in_array($hint, $hints);
    }

    public function sendPasswordResetNotification($token, bool $isInvite = false): void
    {
        $this->notify(new ResetPassword($token, $isInvite));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }
}
