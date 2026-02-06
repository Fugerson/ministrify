<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\EventPolicy;
use App\Policies\GroupPolicy;
use App\Policies\MinistryPolicy;
use App\Policies\PersonPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Attendance::class => AttendancePolicy::class,
        Event::class => EventPolicy::class,
        Group::class => GroupPolicy::class,
        Ministry::class => MinistryPolicy::class,
        Person::class => PersonPolicy::class,
        Transaction::class => TransactionPolicy::class,
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
        Gate::define('manage-ministry', function (User $user, ?Ministry $ministry = null) {
            if (!$ministry) {
                return false;
            }
            return $user->canManageMinistry($ministry);
        });

        Gate::define('view-ministry', function (User $user, ?Ministry $ministry = null) {
            if (!$ministry) {
                return false;
            }
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
        Gate::define('manage-person', function (User $user, ?Person $person = null) {
            if (!$person) {
                return false;
            }
            // Leaders can manage people in their ministries
            if ($user->isLeader() && $user->person) {
                $leaderMinistryIds = $user->person->leadingMinistries()->pluck('id')->toArray();
                return $person->ministries()->whereIn('ministry_id', $leaderMinistryIds)->exists();
            }

            return false;
        });

        // Volunteer self-management
        Gate::define('manage-own-profile', function (User $user, ?Person $person = null) {
            if (!$person) {
                return false;
            }
            return $user->person && $user->person->id === $person->id;
        });
    }
}
