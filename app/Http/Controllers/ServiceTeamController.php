<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\Ministry;
use App\Models\MinistryRole;
use App\Models\Person;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class ServiceTeamController extends Controller
{
    /**
     * Add a team member to a service event
     */
    public function addTeamMember(Request $request, Event $event)
    {
        abort_unless($this->canEditServiceEvent($request->input('ministry_id')), 403);
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'person_id' => 'required|exists:people,id',
            'ministry_role_id' => 'required|exists:ministry_roles,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $churchId = $this->getCurrentChurch()->id;

        // Verify ministry belongs to same church and has the flag
        $ministry = Ministry::find($validated['ministry_id']);
        if (!$ministry || $ministry->church_id !== $churchId || (!$ministry->is_sunday_service_part && !$ministry->is_worship_ministry)) {
            abort(404);
        }

        // Verify person belongs to same church
        $person = \App\Models\Person::find($validated['person_id']);
        if (!$person || $person->church_id !== $churchId) {
            abort(404);
        }

        // Verify role belongs to the ministry
        $role = MinistryRole::find($validated['ministry_role_id']);
        if (!$role || $role->ministry_id !== $ministry->id) {
            abort(404);
        }

        // Check if already exists
        $exists = EventMinistryTeam::where('event_id', $event->id)
            ->where('ministry_id', $validated['ministry_id'])
            ->where('person_id', $validated['person_id'])
            ->where('ministry_role_id', $validated['ministry_role_id'])
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Ця людина вже призначена на цю роль'], 422);
            }
            return back()->with('error', 'Ця людина вже призначена на цю роль');
        }

        $member = EventMinistryTeam::create([
            'event_id' => $event->id,
            'ministry_id' => $validated['ministry_id'],
            'person_id' => $validated['person_id'],
            'ministry_role_id' => $validated['ministry_role_id'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $member->id]);
        }

        return back()->with('success', 'Учасника додано');
    }

    /**
     * Remove a team member from a service event
     */
    public function removeTeamMember(Request $request, Event $event, EventMinistryTeam $member)
    {
        abort_unless($this->canEditServiceEvent($member->ministry_id), 403);
        $this->authorizeChurch($event);

        if ($member->event_id !== $event->id) {
            abort(404);
        }

        $member->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Учасника видалено');
    }

    /**
     * Update notes on a ministry team member entry
     */
    public function updateNotes(Request $request, Event $event, EventMinistryTeam $member)
    {
        abort_unless($this->canEditServiceEvent($member->ministry_id), 403);
        $this->authorizeChurch($event);

        if ($member->event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        $member->update(['notes' => $validated['notes']]);

        return response()->json(['success' => true]);
    }

    public function sendNotification(Request $request, Event $event, EventMinistryTeam $member)
    {
        abort_unless($this->canEditServiceEvent($member->ministry_id), 403);
        $this->authorizeChurch($event);

        if ($member->event_id !== $event->id) {
            abort(404);
        }

        $person = $member->person;
        if (!$person) {
            return response()->json(['success' => false, 'message' => 'Людину не знайдено'], 422);
        }

        if (!$person->telegram_chat_id) {
            return response()->json(['success' => false, 'message' => 'У цієї людини не підключений Telegram'], 422);
        }

        if (!config('services.telegram.bot_token')) {
            return response()->json(['success' => false, 'message' => 'Telegram бот не налаштований'], 500);
        }

        try {
            $telegram = TelegramService::make();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Telegram бот не налаштований'], 500);
        }

        $roleName = $member->ministryRole?->name ?? 'Служіння';
        $days = ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'];
        $dayName = $days[$event->date->format('w')];
        $timeStr = $event->time ? $event->time->format('H:i') : 'час уточнюється';

        $message = "📋 <b>Запит на участь</b>\n\n"
            . "📅 {$event->date->format('d.m.Y')} ({$dayName})\n"
            . "⏰ {$timeStr}\n"
            . "🎯 {$roleName}\n\n"
            . "Чи можете ви взяти участь?";

        $keyboard = [
            [
                ['text' => '✅ Так, зможу', 'callback_data' => "mteam_confirm_{$member->id}"],
                ['text' => '❌ Не можу', 'callback_data' => "mteam_decline_{$member->id}"],
            ],
        ];

        $sent = $telegram->sendMessage($person->telegram_chat_id, $message, $keyboard);

        if ($sent) {
            $member->update(['status' => 'pending']);
            return response()->json(['success' => true, 'message' => 'Запит надіслано в Telegram']);
        }

        return response()->json(['success' => false, 'message' => 'Не вдалося надіслати повідомлення'], 500);
    }

    /**
     * Check if user can edit service events (has permission OR is a ministry member)
     */
    protected function canEditServiceEvent(?int $ministryId = null): bool
    {
        if (auth()->user()->canEdit('events')) {
            return true;
        }

        if ($ministryId) {
            $ministry = Ministry::find($ministryId);
            return $ministry && $ministry->isMember();
        }

        return false;
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
