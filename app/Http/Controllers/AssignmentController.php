<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Event;
use App\Models\Person;
use App\Models\SchedulingConflict;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        // Check if event has a ministry
        if (!$event->ministry) {
            return back()->with('error', 'Подія не має служіння.');
        }

        Gate::authorize('manage-ministry', $event->ministry);

        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'person_id' => 'required|exists:people,id',
            'blockout_override' => 'boolean',
        ]);

        // Check if event date has passed (compare dates, not datetime)
        if ($event->date && $event->date->lt(today())) {
            return back()->with('error', 'Неможливо призначити на минулу подію.');
        }
        $position = $event->ministry->positions()->findOrFail($validated['position_id']);

        // Check if person is already assigned to ANY position in this event
        $existingAssignment = Assignment::where('event_id', $event->id)
            ->where('person_id', $validated['person_id'])
            ->with('position')
            ->first();

        if ($existingAssignment) {
            $positionName = $existingAssignment->position?->name ?? 'іншу позицію';
            return back()->with('error', "Ця людина вже призначена на {$positionName} для цієї події.");
        }

        // Check if position is already filled
        $positionFilled = Assignment::where('event_id', $event->id)
            ->where('position_id', $validated['position_id'])
            ->exists();

        if ($positionFilled) {
            return back()->with('error', 'Ця позиція вже зайнята.');
        }

        // Get person and check for blockouts
        $person = Person::find($validated['person_id']);
        $hasBlockout = $person->hasBlockoutOn($event->date, $event->ministry_id);
        $blockoutOverride = $request->boolean('blockout_override');

        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $validated['position_id'],
            'person_id' => $validated['person_id'],
            'status' => Assignment::STATUS_PENDING,
            'blockout_override' => $hasBlockout && $blockoutOverride,
        ]);

        // Log conflict if blockout was overridden
        if ($hasBlockout && $blockoutOverride) {
            SchedulingConflict::create([
                'assignment_id' => $assignment->id,
                'conflict_type' => 'blockout',
                'conflict_details' => $person->getBlockoutReasonFor($event->date, $event->ministry_id),
                'was_overridden' => true,
                'overridden_by' => auth()->id(),
            ]);
        }

        // Update person's scheduling stats
        $person->update([
            'last_scheduled_at' => now(),
            'times_scheduled_this_month' => $person->times_scheduled_this_month + 1,
            'times_scheduled_this_year' => $person->times_scheduled_this_year + 1,
        ]);

        // Send Telegram notification if configured
        $this->sendNotification($assignment);

        $message = 'Людину призначено на позицію.';
        if ($hasBlockout && $blockoutOverride) {
            $message .= ' (Увага: волонтер недоступний у цю дату)';
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $event = $assignment->event;
        $this->authorizeChurch($event);

        if (!$event->ministry) {
            return back()->with('error', 'Подія не має служіння.');
        }

        Gate::authorize('manage-ministry', $event->ministry);

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
        ]);

        $assignment->update([
            'person_id' => $validated['person_id'],
            'status' => 'pending',
            'notified_at' => null,
            'responded_at' => null,
        ]);

        // Send Telegram notification to new person
        $this->sendNotification($assignment->fresh());

        return back()->with('success', 'Призначення оновлено.');
    }

    public function destroy(Assignment $assignment)
    {
        $event = $assignment->event;
        $this->authorizeChurch($event);

        if (!$event->ministry) {
            return back()->with('error', 'Подія не має служіння.');
        }

        Gate::authorize('manage-ministry', $event->ministry);

        $assignment->delete();

        return back()->with('success', 'Призначення видалено.');
    }

    public function confirm(Assignment $assignment)
    {
        $user = auth()->user();

        // Check if this is the assigned person
        if ($user->person && $user->person->id === $assignment->person_id) {
            $assignment->confirm();
            return back()->with('success', 'Ви підтвердили участь.');
        }

        // Or if this is a leader/admin
        $event = $assignment->event;
        $this->authorizeChurch($event);

        if (!$event->ministry) {
            abort(404);
        }

        Gate::authorize('manage-ministry', $event->ministry);

        $assignment->confirm();

        return back()->with('success', 'Призначення підтверджено.');
    }

    public function decline(Assignment $assignment)
    {
        $user = auth()->user();

        // Check if this is the assigned person
        if ($user->person && $user->person->id === $assignment->person_id) {
            $assignment->decline();

            // Notify leader if configured
            $this->notifyLeaderOfDecline($assignment);

            return back()->with('success', 'Ви відхилили участь.');
        }

        // Or if this is a leader/admin
        $event = $assignment->event;
        $this->authorizeChurch($event);

        if (!$event->ministry) {
            abort(404);
        }

        Gate::authorize('manage-ministry', $event->ministry);

        $assignment->decline();

        return back()->with('success', 'Призначення відхилено.');
    }

    public function notifyAll(Event $event)
    {
        $this->authorizeChurch($event);
        $this->requireMinistry($event);

        $assignments = $event->assignments()->notNotified()->with('person')->get();

        foreach ($assignments as $assignment) {
            $this->sendNotification($assignment);
        }

        return back()->with('success', "Сповіщення надіслано {$assignments->count()} учасникам.");
    }

    /**
     * Bulk confirm all pending assignments for an event
     */
    public function confirmAll(Event $event)
    {
        $this->authorizeChurch($event);
        $this->requireMinistry($event);

        $count = 0;
        $assignments = $event->assignments()->pending()->get();

        foreach ($assignments as $assignment) {
            if ($assignment->confirm()) {
                $count++;
            }
        }

        if ($count === 0) {
            return back()->with('info', 'Немає призначень для підтвердження.');
        }

        return back()->with('success', "Підтверджено {$count} призначень.");
    }

    /**
     * Bulk mark all confirmed assignments as attended (for past events)
     */
    public function markAllAttended(Event $event)
    {
        $this->authorizeChurch($event);
        $this->requireMinistry($event);

        // Only allow for past events
        if ($event->date && $event->date->isFuture()) {
            return back()->with('error', 'Можна позначати присутність лише для минулих подій.');
        }

        $count = 0;
        $assignments = $event->assignments()->confirmed()->get();

        foreach ($assignments as $assignment) {
            if ($assignment->markAsAttended()) {
                $count++;
            }
        }

        if ($count === 0) {
            return back()->with('info', 'Немає призначень для позначення.');
        }

        return back()->with('success', "Позначено присутність для {$count} осіб.");
    }

    /**
     * Update assignment status via AJAX
     */
    public function updateStatus(Request $request, Assignment $assignment)
    {
        $event = $assignment->event;
        $this->authorizeChurch($event);

        if (!$event->ministry) {
            return response()->json(['success' => false, 'message' => 'Подія не має служіння.'], 404);
        }

        Gate::authorize('manage-ministry', $event->ministry);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,declined,attended',
        ]);

        $newStatus = $validated['status'];

        // Check if transition is allowed
        if (!$assignment->canTransitionTo($newStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Неможливо змінити статус на ' . Assignment::STATUSES[$newStatus],
            ], 422);
        }

        $assignment->transitionTo($newStatus);

        return response()->json([
            'success' => true,
            'status' => $assignment->status,
            'status_label' => $assignment->status_label,
            'status_color' => $assignment->status_color,
        ]);
    }

    private function sendNotification(Assignment $assignment): void
    {
        $person = $assignment->person;
        $church = $assignment->event->church;

        if (!$person->telegram_chat_id || !$church->telegram_bot_token) {
            return;
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);
            $telegram->sendAssignmentNotification($assignment);
            $assignment->markAsNotified();
        } catch (\Exception $e) {
            // Log error but don't fail
            logger()->error('Telegram notification failed', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyLeaderOfDecline(Assignment $assignment): void
    {
        $event = $assignment->event;
        $church = $event->church;

        if (!$church) {
            return;
        }

        $settings = $church->settings ?? [];

        if (empty($settings['notifications']['notify_leader_on_decline'])) {
            return;
        }

        // Null-safe ministry and leader access
        $leader = $event->ministry?->leader;
        if (!$leader || !$leader->telegram_chat_id || !$church->telegram_bot_token) {
            return;
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);
            $telegram->sendDeclineNotification($assignment, $leader);
        } catch (\Exception $e) {
            logger()->error('Leader decline notification failed', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }

    /**
     * Check that event has a ministry and authorize access to it
     */
    private function requireMinistry(Event $event): void
    {
        if (!$event->ministry) {
            abort(404, 'Подія не має служіння.');
        }

        Gate::authorize('manage-ministry', $event->ministry);
    }
}
