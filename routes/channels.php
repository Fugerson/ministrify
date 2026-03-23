<?php

use App\Models\Event;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Event plan real-time updates (responsibilities, team status)
Broadcast::channel('event.{eventId}', function ($user, $eventId) {
    $event = Event::find($eventId);

    return $event && $event->church_id === $user->church_id;
});

// Private messages
Broadcast::channel('private-messages.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
