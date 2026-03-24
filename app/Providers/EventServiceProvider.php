<?php

namespace App\Providers;

use App\Listeners\SendHorizonAlertToTelegram;
use App\Models\Event;
use App\Models\Person;
use App\Observers\EventObserver;
use App\Observers\PersonObserver;
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
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
