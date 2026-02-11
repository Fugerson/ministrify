<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\Ministry;
use App\Models\MinistryRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceTeamController extends Controller
{
    /**
     * List sunday service events for a ministry with is_sunday_service_part
     */
    public function events(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);

        if (!$ministry->is_sunday_service_part) {
            abort(404);
        }

        $events = Event::where('church_id', $this->getCurrentChurch()->id)
            ->where('service_type', 'sunday_service')
            ->where('date', '>=', now()->subDays(7))
            ->with(['ministryTeams' => fn($q) => $q->where('ministry_id', $ministry->id)->with('person', 'ministryRole')])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $ministryRoles = $ministry->ministryRoles()->orderBy('sort_order')->get();

        return view('ministries.service-events', compact('ministry', 'events', 'ministryRoles'));
    }

    /**
     * Show service event detail for a ministry — team assignments
     */
    public function eventShow(Ministry $ministry, Event $event)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($event);

        if (!$ministry->is_sunday_service_part) {
            abort(404);
        }

        $event->load(['ministryTeams' => fn($q) => $q->where('ministry_id', $ministry->id)->with('person', 'ministryRole')]);

        $ministryRoles = $ministry->ministryRoles()->orderBy('sort_order')->get();

        // Get ministry members
        $members = $ministry->members()->get();
        if ($ministry->leader && !$members->contains('id', $ministry->leader->id)) {
            $members->prepend($ministry->leader);
        }

        return view('ministries.service-event-detail', compact('ministry', 'event', 'ministryRoles', 'members'));
    }

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

        // Verify ministry belongs to same church and has the flag
        $ministry = Ministry::find($validated['ministry_id']);
        if (!$ministry || $ministry->church_id !== $this->getCurrentChurch()->id || !$ministry->is_sunday_service_part) {
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
