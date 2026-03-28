<?php

namespace App\Providers;

use App\Listeners\SendHorizonAlertToTelegram;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Group;
use App\Models\Person;
use App\Models\Transaction;
use App\Observers\AssignmentObserver;
use App\Observers\AttendanceObserver;
use App\Observers\EventObserver;
use App\Observers\GroupObserver;
use App\Observers\PersonObserver;
use App\Observers\TransactionObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Horizon\Events\LongWaitDetected;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        LongWaitDetected::class => [
            SendHorizonAlertToTelegram::class,
        ],
    ];

    public function boot(): void
    {
        Person::observe(PersonObserver::class);
        Event::observe(EventObserver::class);
        Transaction::observe(TransactionObserver::class);
        Group::observe(GroupObserver::class);
        Attendance::observe(AttendanceObserver::class);
        Assignment::observe(AssignmentObserver::class);
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
