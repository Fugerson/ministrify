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

// Attendance real-time sync (checkin page)
Broadcast::channel('church.{churchId}.attendance.{attendanceId}', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Finance transactions real-time updates
Broadcast::channel('church.{churchId}.finances', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// People list real-time updates
Broadcast::channel('church.{churchId}.people', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Events/calendar real-time updates
Broadcast::channel('church.{churchId}.events', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Groups real-time updates
Broadcast::channel('church.{churchId}.groups', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Announcements real-time updates
Broadcast::channel('church.{churchId}.announcements', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Prayer requests real-time updates
Broadcast::channel('church.{churchId}.prayers', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Ministries real-time updates
Broadcast::channel('church.{churchId}.ministries', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Budgets real-time updates
Broadcast::channel('church.{churchId}.budgets', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Service planning real-time updates
Broadcast::channel('church.{churchId}.service-planning', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Dashboard real-time updates
Broadcast::channel('church.{churchId}.dashboard', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Music stand real-time updates
Broadcast::channel('church.{churchId}.music-stand', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});

// Boards (kanban) real-time updates
Broadcast::channel('church.{churchId}.boards', function ($user, $churchId) {
    return (int) $user->church_id === (int) $churchId;
});
