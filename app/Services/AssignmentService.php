<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\BlockoutDate;
use App\Models\Event;
use App\Models\Person;
use App\Models\Position;
use App\Models\UnavailableDate;
use Carbon\Carbon;

/**
 * @deprecated Use SchedulingService instead. This service will be removed in a future version.
 */
class AssignmentService
{
    public function getAvailablePeople(Event $event, Position $position): array
    {
        $ministryId = $event->ministry_id;
        $eventDate = $event->date;
        $eventTime = $event->time;

        // Get people in this ministry who have this position
        $people = Person::where('church_id', $event->church_id)
            ->whereHas('ministries', function ($q) use ($ministryId, $position) {
                $q->where('ministries.id', $ministryId)
                    ->whereJsonContains('ministry_person.position_ids', (string) $position->id);
            })
            ->with(['unavailableDates', 'assignments' => function ($q) use ($eventDate) {
                $q->whereHas('event', fn($eq) => $eq->whereDate('date', $eventDate));
            }])
            ->get();

        $available = [];
        $busy = [];
        $unavailable = [];

        foreach ($people as $person) {
            $conflict = $this->checkConflicts($person, $event);

            if ($conflict['type'] === 'unavailable') {
                $unavailable[] = [
                    'person' => $person,
                    'reason' => $conflict['reason'],
                ];
            } elseif ($conflict['type'] === 'busy') {
                $busy[] = [
                    'person' => $person,
                    'reason' => $conflict['reason'],
                ];
            } else {
                $available[] = [
                    'person' => $person,
                ];
            }
        }

        return [
            'available' => $available,
            'busy' => $busy,
            'unavailable' => $unavailable,
        ];
    }

    public function checkConflicts(Person $person, Event $event): array
    {
        // Check if person is marked as unavailable (legacy)
        $unavailable = UnavailableDate::where('person_id', $person->id)
            ->where('date_from', '<=', $event->date)
            ->where('date_to', '>=', $event->date)
            ->first();

        if ($unavailable) {
            return [
                'type' => 'unavailable',
                'reason' => $unavailable->reason ?? 'Недоступний',
            ];
        }

        // Check BlockoutDate (new system)
        $blockout = BlockoutDate::forPerson($person->id)
            ->active()
            ->forDate($event->date)
            ->first();

        if ($blockout) {
            return [
                'type' => 'unavailable',
                'reason' => $blockout->reason_label ?? 'Недоступний',
            ];
        }

        // Check if person has another assignment at the same time
        $existingAssignment = Assignment::where('person_id', $person->id)
            ->where('status', '!=', 'declined')
            ->whereHas('event', function ($q) use ($event) {
                $q->whereDate('date', $event->date)
                    ->where('id', '!=', $event->id);
            })
            ->with('event.ministry')
            ->first();

        if ($existingAssignment) {
            return [
                'type' => 'busy',
                'reason' => "Служить в " . ($existingAssignment->event->ministry?->name ?? 'іншому служінні'),
            ];
        }

        return ['type' => 'available'];
    }

    public function assign(Event $event, Position $position, Person $person, bool $notify = true): Assignment
    {
        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => 'pending',
        ]);

        if ($notify && $person->church?->isNotificationEnabled('notify_on_assignment') && $person->telegram_chat_id && config('services.telegram.bot_token')) {
            try {
                $telegram = TelegramService::make();
                $telegram->sendAssignmentNotification($assignment);
                $assignment->update(['notified_at' => now()]);
            } catch (\Exception $e) {
                // Log error but don't fail
            }
        }

        return $assignment;
    }

    public function notifyAll(Event $event): int
    {
        $notified = 0;

        $assignments = $event->assignments()
            ->with(['person.church', 'event.ministry', 'position'])
            ->where('status', 'pending')
            ->whereNull('notified_at')
            ->get();

        foreach ($assignments as $assignment) {
            $person = $assignment->person;
            $church = $person->church;

            if ($church?->isNotificationEnabled('notify_on_assignment') && $person->telegram_chat_id && config('services.telegram.bot_token')) {
                try {
                    $telegram = TelegramService::make();
                    $telegram->sendAssignmentNotification($assignment);
                    $assignment->update(['notified_at' => now()]);
                    $notified++;
                } catch (\Exception $e) {
                    // Continue with others
                }
            }
        }

        return $notified;
    }
}
