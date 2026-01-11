<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Policies\GroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Event::class => EventPolicy::class,
        Group::class => GroupPolicy::class,
    ];

    public function boot(): void
    {
        // Admin can do everything
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // Ministry management
        Gate::define('manage-ministry', function (User $user, Ministry $ministry) {
            return $user->canManageMinistry($ministry);
        });

        Gate::define('view-ministry', function (User $user, Ministry $ministry) {
            // All users with view permission can see ministries in their church
            if ($user->canView('ministries') && $user->church_id === $ministry->church_id) {
                return true;
            }

            if ($user->canManageMinistry($ministry)) {
                return true;
            }

            // Volunteers can view their own ministries
            if ($user->person) {
                return $user->person->ministries()->where('ministry_id', $ministry->id)->exists();
            }

            return false;
        });

        // Person management
        Gate::define('manage-person', function (User $user, Person $person) {
            // Leaders can manage people in their ministries
            if ($user->isLeader() && $user->person) {
                $leaderMinistryIds = $user->person->leadingMinistries()->pluck('id')->toArray();
                return $person->ministries()->whereIn('ministry_id', $leaderMinistryIds)->exists();
            }

            return false;
        });

        // Volunteer self-management
        Gate::define('manage-own-profile', function (User $user, Person $person) {
            return $user->person && $user->person->id === $person->id;
        });
    }
}
