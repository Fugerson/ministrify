<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\Ministry;
use App\Models\MinistryRole;
use Illuminate\Http\Request;

class ServiceTeamController extends Controller
{
    /**
     * Add a team member to a service event
     */
    public function addTeamMember(Request $request, Event $event)
    {
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

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
