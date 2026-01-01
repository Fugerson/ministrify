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
        ]);

        $event->responsibilities()->create([
            'name' => $validated['name'],
            'status' => EventResponsibility::STATUS_OPEN,
        ]);

        return back()->with('success', 'Відповідальність додано.');
    }

    public function assign(Request $request, EventResponsibility $responsibility)
    {
        $event = $responsibility->event;
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
        ]);

        $person = Person::find($validated['person_id']);

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

        // Check if this is the assigned person
        if ($user->person && $user->person->id === $responsibility->person_id) {
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

        // Check if this is the assigned person
        if ($user->person && $user->person->id === $responsibility->person_id) {
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

        $this->sendNotification($responsibility);

        return back()->with('success', 'Сповіщення надіслано повторно.');
    }

    private function sendNotification(EventResponsibility $responsibility): void
    {
        $person = $responsibility->person;
        $event = $responsibility->event;
        $church = $event->church;

        if (!$person->telegram_chat_id || !$church->telegram_bot_token) {
            return;
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);

            $message = "🔔 <b>Нова відповідальність!</b>\n\n"
                . "📅 {$event->date->format('d.m.Y')}, {$event->time->format('H:i')}\n"
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
