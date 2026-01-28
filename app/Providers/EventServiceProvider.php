<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Person;
use App\Observers\EventObserver;
use App\Observers\PersonObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
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
