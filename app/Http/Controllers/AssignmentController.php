<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Event;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $this->authorizeChurch($event);
        Gate::authorize('manage-ministry', $event->ministry);

        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'person_id' => 'required|exists:people,id',
        ]);

        // Check if position belongs to the ministry
        $position = $event->ministry->positions()->findOrFail($validated['position_id']);

        // Check for duplicate assignment
        $exists = Assignment::where('event_id', $event->id)
            ->where('position_id', $validated['position_id'])
            ->where('person_id', $validated['person_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ця людина вже призначена на цю позицію.');
        }

        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $validated['position_id'],
            'person_id' => $validated['person_id'],
            'status' => 'pending',
        ]);

        // Send Telegram notification if configured
        $this->sendNotification($assignment);

        return back()->with('success', 'Людину призначено на позицію.');
    }

    public function update(Request $request, Assignment $assignment)
    {
        $event = $assignment->event;
        $this->authorizeChurch($event);
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
        Gate::authorize('manage-ministry', $event->ministry);

        $assignment->decline();

        return back()->with('success', 'Призначення відхилено.');
    }

    public function notifyAll(Event $event)
    {
        $this->authorizeChurch($event);
        Gate::authorize('manage-ministry', $event->ministry);

        $assignments = $event->assignments()->notNotified()->with('person')->get();

        foreach ($assignments as $assignment) {
            $this->sendNotification($assignment);
        }

        return back()->with('success', "Сповіщення надіслано {$assignments->count()} учасникам.");
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
        $church = $assignment->event->church;
        $settings = $church->settings ?? [];

        if (empty($settings['notifications']['notify_leader_on_decline'])) {
            return;
        }

        $leader = $assignment->event->ministry->leader;
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

    private function authorizeChurch(Event $event): void
    {
        if ($event->church_id !== auth()->user()->church_id) {
            abort(404);
        }
    }
}
