<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResponsibility;
use App\Models\Person;
use App\Models\TelegramMessage;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class EventResponsibilityController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $event->responsibilities()->create([
            'name' => $validated['name'],
            'notes' => $validated['notes'] ?? null,
            'status' => EventResponsibility::STATUS_OPEN,
        ]);

        return back()->with('success', 'Відповідальність додано.');
    }

    public function assign(Request $request, EventResponsibility $responsibility)
    {
        $event = $responsibility->event;
        $this->authorizeChurch($event);

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
        ]);

        // Ensure person belongs to this church
        $person = Person::where('id', $validated['person_id'])
            ->where('church_id', $church->id)
            ->firstOrFail();

        $responsibility->update([
            'person_id' => $person->id,
            'status' => EventResponsibility::STATUS_PENDING,
        ]);

        // Send Telegram notification
        $this->sendNotification($responsibility);

        return back()->with('success', "Призначено {$person->first_name}. Сповіщення надіслано.");
    }

    public function unassign(EventResponsibility $responsibility)
    {
        $event = $responsibility->event;
        $this->authorizeChurch($event);

        $responsibility->update([
            'person_id' => null,
            'status' => EventResponsibility::STATUS_OPEN,
            'notified_at' => null,
            'responded_at' => null,
        ]);

        return back()->with('success', 'Призначення знято.');
    }

    public function update(Request $request, EventResponsibility $responsibility)
    {
        $event = $responsibility->event;
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $responsibility->update($validated);

        return back()->with('success', 'Відповідальність оновлено.');
    }

    public function destroy(EventResponsibility $responsibility)
    {
        $event = $responsibility->event;
        $this->authorizeChurch($event);

        $responsibility->delete();

        return back()->with('success', 'Відповідальність видалено.');
    }

    public function confirm(EventResponsibility $responsibility)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        // Check if this is the assigned person (must be same church)
        if ($user->person && $user->person->id === $responsibility->person_id
            && $responsibility->event && $responsibility->event->church_id === $church->id) {
            $responsibility->confirm();
            return back()->with('success', 'Ви підтвердили участь.');
        }

        // Or admin
        $this->authorizeChurch($responsibility->event);
        $responsibility->confirm();

        return back()->with('success', 'Підтверджено.');
    }

    public function decline(EventResponsibility $responsibility)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        // Check if this is the assigned person (must be same church)
        if ($user->person && $user->person->id === $responsibility->person_id
            && $responsibility->event && $responsibility->event->church_id === $church->id) {
            $responsibility->decline();
            return back()->with('success', 'Ви відхилили участь.');
        }

        // Or admin
        $this->authorizeChurch($responsibility->event);
        $responsibility->decline();

        return back()->with('success', 'Відхилено.');
    }

    public function resend(EventResponsibility $responsibility)
    {
        $event = $responsibility->event;
        $this->authorizeChurch($event);

        if (!$responsibility->person_id) {
            return back()->with('error', 'Немає призначеної людини.');
        }

        // Reset status to pending
        $responsibility->update([
            'status' => EventResponsibility::STATUS_PENDING,
            'responded_at' => null,
        ]);

        $this->sendNotification($responsibility);

        return back()->with('success', 'Сповіщення надіслано повторно.');
    }

    /**
     * Poll for responsibility status updates (AJAX endpoint)
     */
    public function poll(Event $event, Request $request)
    {
        $this->authorizeChurch($event);

        $lastCheck = $request->get('since');
        $lastCheckTime = $lastCheck ? \Carbon\Carbon::parse($lastCheck) : now()->subMinutes(5);

        $responsibilities = $event->responsibilities()
            ->with('person:id,first_name,last_name')
            ->orderBy('id')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'status' => $r->status,
                'status_label' => $r->status_label,
                'person_name' => $r->person?->full_name,
                'responded_at' => $r->responded_at?->toIso8601String(),
                'is_new_response' => $r->responded_at && $r->responded_at->gt($lastCheckTime),
            ]);

        // Check for new responses to show notifications
        $newResponses = $responsibilities->filter(fn($r) => $r['is_new_response']);

        return response()->json([
            'responsibilities' => $responsibilities,
            'new_responses' => $newResponses->values(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function sendNotification(EventResponsibility $responsibility): void
    {
        $person = $responsibility->person;
        $event = $responsibility->event;
        $church = $event?->church;

        // Null checks before accessing properties
        if (!$person || !$event || !$church) {
            return;
        }

        if (!$church->isNotificationEnabled('notify_on_responsibility')) {
            return;
        }

        if (!$person->telegram_chat_id || !config('services.telegram.bot_token')) {
            return;
        }

        try {
            $telegram = TelegramService::make();

            $timeStr = $event->time ? $event->time->format('H:i') : 'весь день';
            $message = "🔔 <b>Нова відповідальність!</b>\n\n"
                . "📅 {$event->date->format('d.m.Y')}, {$timeStr}\n"
                . "📍 {$event->title}\n"
                . "🎯 <b>{$responsibility->name}</b>\n\n"
                . "Ви можете взяти це на себе?";

            $keyboard = [
                [
                    ['text' => '✅ Так, візьму', 'callback_data' => "resp_confirm_{$responsibility->id}"],
                    ['text' => '❌ Не можу', 'callback_data' => "resp_decline_{$responsibility->id}"],
                ],
            ];

            $telegram->sendMessage($person->telegram_chat_id, $message, $keyboard);

            $responsibility->update(['notified_at' => now()]);
        } catch (\Exception $e) {
            logger()->error('Responsibility notification failed', [
                'responsibility_id' => $responsibility->id,
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
}
