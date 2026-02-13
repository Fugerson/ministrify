<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'role',
        'google_id',
        'church_role_id',
        'is_super_admin',
        'theme',
        'preferences',
        'settings',
        'onboarding_completed',
        'onboarding_state',
        'onboarding_started_at',
        'onboarding_completed_at',
        'permission_overrides',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'settings',
        'permission_overrides',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_set_at' => 'datetime',
        'preferences' => 'array',
        'settings' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_state' => 'array',
        'onboarding_started_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'permission_overrides' => 'array',
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

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * All churches this user belongs to (via pivot).
     */
    public function churches(): BelongsToMany
    {
        return $this->belongsToMany(Church::class, 'church_user')
            ->withPivot('church_role_id', 'person_id', 'permission_overrides', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Check if user belongs to a church (via pivot).
     */
    public function belongsToChurch(int $churchId): bool
    {
        return $this->churches()->where('churches.id', $churchId)->exists();
    }

    /**
     * Switch active church: update church_id, church_role_id, permission_overrides from pivot.
     */
    public function switchToChurch(int $churchId): bool
    {
        $pivot = \Illuminate\Support\Facades\DB::table('church_user')
            ->where('user_id', $this->id)
            ->where('church_id', $churchId)
            ->first();

        if (!$pivot) {
            return false;
        }

        $this->update([
            'church_id' => $churchId,
            'church_role_id' => $pivot->church_role_id,
            'permission_overrides' => $pivot->permission_overrides ? json_decode($pivot->permission_overrides, true) : null,
        ]);

        return true;
    }

    /**
     * Join a new church: create pivot record + Person for the new church.
     */
    public function joinChurch(int $churchId, ?int $roleId = null): void
    {
        // Create pivot if not exists
        $exists = \Illuminate\Support\Facades\DB::table('church_user')
            ->where('user_id', $this->id)
            ->where('church_id', $churchId)
            ->exists();

        if ($exists) {
            return;
        }

        // Find or create Person for the new church
        $person = Person::where('user_id', $this->id)
            ->where('church_id', $churchId)
            ->first();

        if (!$person) {
            // Try to find existing Person by email (may have been added manually before)
            $person = Person::where('church_id', $churchId)
                ->where('email', $this->email)
                ->whereNull('user_id')
                ->first();

            if ($person) {
                $person->update(['user_id' => $this->id]);
            } else {
                $nameParts = explode(' ', $this->name, 2);
                $person = Person::create([
                    'church_id' => $churchId,
                    'user_id' => $this->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $this->email,
                    'membership_status' => 'newcomer',
                ]);
            }
        }

        \Illuminate\Support\Facades\DB::table('church_user')->insert([
            'user_id' => $this->id,
            'church_id' => $churchId,
            'church_role_id' => $roleId,
            'person_id' => $person->id,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function isAdmin(): bool
    {
        // Super admins are always admins in any church context
        return $this->is_super_admin || $this->churchRole?->is_admin_role === true;
    }

    public function isLeader(): bool
    {
        if (!$this->churchRole) {
            return false;
        }
        return $this->churchRole->slug === 'leader';
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

        // Check role-based permissions
        if ($this->churchRole && $this->churchRole->hasPermission($module, $action)) {
            return true;
        }

        // Check per-user permission overrides
        $overrides = $this->permission_overrides ?? [];
        if (isset($overrides[$module]) && in_array($action, $overrides[$module])) {
            return true;
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

    /**
     * Get all effective permissions (role + overrides combined)
     */
    public function getEffectivePermissions(): array
    {
        $rolePermissions = $this->churchRole ? $this->churchRole->getAllPermissions() : [];
        $overrides = $this->permission_overrides ?? [];

        $effective = $rolePermissions;
        foreach ($overrides as $module => $actions) {
            $existing = $effective[$module] ?? [];
            $effective[$module] = array_values(array_unique(array_merge($existing, $actions)));
        }

        return $effective;
    }

    /**
     * Get per-user permission overrides
     */
    public function getPermissionOverrides(): array
    {
        return $this->permission_overrides ?? [];
    }

    /**
     * Set per-user permission overrides
     */
    public function setPermissionOverrides(array $overrides): void
    {
        $this->update(['permission_overrides' => $overrides ?: null]);
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

    private function getValidOnboardingState(): array
    {
        $state = $this->onboarding_state;
        if (!is_array($state)) {
            return ['current_step' => 'welcome', 'steps' => [], 'dismissed_hints' => []];
        }
        if (!isset($state['steps']) || !is_array($state['steps'])) {
            $state['steps'] = [];
        }
        return $state;
    }

    public function getOnboardingStep(string $step): ?array
    {
        $state = $this->getValidOnboardingState();
        return $state['steps'][$step] ?? null;
    }

    public function getCurrentOnboardingStep(): string
    {
        $state = $this->getValidOnboardingState();
        return $state['current_step'] ?? 'welcome';
    }

    public function completeOnboardingStep(string $step, array $data = []): void
    {
        $state = $this->getValidOnboardingState();
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

        $state = $this->getValidOnboardingState();
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
        $state = $this->getValidOnboardingState();
        $steps = $state['steps'] ?? [];

        $total = count(self::ONBOARDING_STEPS);
        $completed = 0;

        foreach ($steps as $step) {
            if (($step['completed'] ?? false) || ($step['skipped'] ?? false)) {
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
        $state = $this->getValidOnboardingState();
        $hints = $state['dismissed_hints'] ?? [];

        if (!in_array($hint, $hints)) {
            $hints[] = $hint;
            $state['dismissed_hints'] = $hints;
            $this->update(['onboarding_state' => $state]);
        }
    }

    public function hasSeenOnboardingHint(string $hint): bool
    {
        $state = $this->getValidOnboardingState();
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
