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
     * Link a ministry to an event
     */
    public function linkMinistry(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
        ]);

        $churchId = $this->getCurrentChurch()->id;
        $ministry = Ministry::find($validated['ministry_id']);
        if (!$ministry || $ministry->church_id !== $churchId) {
            abort(404);
        }

        $event->linkedMinistries()->syncWithoutDetaching([$ministry->id]);

        if ($request->wantsJson()) {
            $roles = $ministry->ministryRoles()->orderBy('name')->get(['id', 'name'])->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values();
            $members = $ministry->members()->orderBy('last_name')->get(['people.id', 'first_name', 'last_name', 'telegram_chat_id'])->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->full_name,
                'has_telegram' => (bool) $p->telegram_chat_id,
            ])->values();

            return response()->json([
                'success' => true,
                'message' => __('messages.item_added'),
                'roles' => $roles,
                'members' => $members,
            ]);
        }

        return back()->with('success', __('messages.item_added'));
    }

    /**
     * Unlink a ministry from an event (also removes all team assignments for it)
     */
    public function unlinkMinistry(Request $request, Event $event, Ministry $ministry)
    {
        $this->authorizeChurch($event);

        // Delete cell notes for all roles of this ministry
        $roleIds = $ministry->ministryRoles()->pluck('id')->toArray();
        if (!empty($roleIds)) {
            \App\Models\EventCellNote::where('event_id', $event->id)
                ->where('role_type', 'ministry_role')
                ->whereIn('role_id', $roleIds)
                ->delete();
        }

        $event->linkedMinistries()->detach($ministry->id);

        // Remove all team assignments for this ministry on this event
        EventMinistryTeam::where('event_id', $event->id)
            ->where('ministry_id', $ministry->id)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('messages.item_deleted'));
    }

    /**
     * Update visible roles for a linked ministry
     */
    public function updateVisibleRoles(Request $request, Event $event, Ministry $ministry)
    {
        $this->authorizeChurch($event);

        $visibleRoles = array_map('intval', $request->input('visible_roles', []));

        // Get previous visible roles to detect removed ones
        $pivot = $event->linkedMinistries()->where('ministry_id', $ministry->id)->first()?->pivot;
        $previousRoles = $pivot && $pivot->visible_roles ? json_decode($pivot->visible_roles, true) : [];
        $removedRoles = array_diff($previousRoles, $visibleRoles);

        // Delete cell notes for removed roles
        if (!empty($removedRoles)) {
            \App\Models\EventCellNote::where('event_id', $event->id)
                ->where('role_type', 'ministry_role')
                ->whereIn('role_id', $removedRoles)
                ->delete();
        }

        $event->linkedMinistries()->updateExistingPivot($ministry->id, [
            'visible_roles' => json_encode($visibleRoles),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reorder linked ministries
     */
    public function reorderMinistries(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $order = $request->input('order', []);
        foreach ($order as $index => $ministryId) {
            $event->linkedMinistries()->updateExistingPivot((int) $ministryId, [
                'sort_order' => $index,
            ]);
        }

        return response()->json(['success' => true]);
    }

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

        // Verify ministry belongs to same church
        $ministry = Ministry::find($validated['ministry_id']);
        if (!$ministry || $ministry->church_id !== $churchId) {
            abort(404);
        }

        // Auto-link ministry to event if not yet linked
        $event->linkedMinistries()->syncWithoutDetaching([$ministry->id]);

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
            return response()->json([
                'success' => true,
                'id' => $member->id,
                'person_name' => $person->full_name,
                'status' => $member->status,
            ]);
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
            . "🏛 {$event->title}\n"
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
            \App\Models\TelegramMessage::create([
                'church_id' => $event->church_id,
                'person_id' => $person->id,
                'direction' => 'outgoing',
                'message' => strip_tags($message),
                'is_read' => true,
            ]);

            $member->update(['status' => 'pending']);
            return response()->json(['success' => true, 'message' => __('messages.telegram_request_sent')]);
        }

        return response()->json(['success' => false, 'message' => __('messages.telegram_send_failed')], 500);
    }

    /**
     * Self-signup: team member signs up for a role
     */
    public function selfSignup(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'ministry_role_id' => 'required|exists:ministry_roles,id',
        ]);

        $person = auth()->user()->person;
        if (!$person) {
            return $this->errorResponse($request, __('app.not_team_member'));
        }

        $churchId = $this->getCurrentChurch()->id;

        $ministry = Ministry::find($validated['ministry_id']);
        if (!$ministry || $ministry->church_id !== $churchId) {
            abort(404);
        }

        // Verify ministry is linked to this event
        if (!$event->linkedMinistries()->where('ministries.id', $ministry->id)->exists()) {
            return $this->errorResponse($request, __('app.ministry_not_linked'));
        }

        // Check user is a member of this ministry
        if (!$ministry->members()->where('people.id', $person->id)->exists()) {
            return $this->errorResponse($request, __('app.not_team_member'));
        }

        // Verify role belongs to the ministry
        $role = MinistryRole::find($validated['ministry_role_id']);
        if (!$role || $role->ministry_id !== $ministry->id) {
            abort(404);
        }

        // Check duplicate
        $exists = EventMinistryTeam::where('event_id', $event->id)
            ->where('ministry_id', $ministry->id)
            ->where('person_id', $person->id)
            ->where('ministry_role_id', $role->id)
            ->exists();

        if ($exists) {
            return $this->errorResponse($request, __('messages.already_signed_up'));
        }

        $member = EventMinistryTeam::create([
            'event_id' => $event->id,
            'ministry_id' => $ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
            'status' => 'confirmed',
        ]);

        return $this->successResponse($request, __('messages.signed_up_success'));
    }

    /**
     * Self-unsubscribe: team member removes their own signup
     */
    public function selfUnsubscribe(Request $request, Event $event, EventMinistryTeam $member)
    {
        $this->authorizeChurch($event);

        if ($member->event_id !== $event->id) {
            abort(404);
        }

        $person = auth()->user()->person;
        if (!$person || $member->person_id !== $person->id) {
            return $this->errorResponse($request, __('messages.can_only_delete_own'));
        }

        $member->delete();

        return $this->successResponse($request, __('messages.unsubscribed_success'));
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
