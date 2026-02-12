<?php

namespace App\Http\Controllers;

use App\Models\MeetingAgendaItem;
use App\Models\MeetingAttendee;
use App\Models\MeetingMaterial;
use App\Models\Ministry;
use App\Models\MinistryMeeting;
use App\Models\Person;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Ministry $ministry)
    {
        $this->authorizeMinistry($ministry);

        $meetings = $ministry->meetings()
            ->with(['agendaItems', 'attendees.person'])
            ->paginate(20);

        return view('meetings.index', compact('ministry', 'meetings'));
    }

    public function create(Ministry $ministry)
    {
        $this->authorizeMinistry($ministry);

        $members = $ministry->members()->orderBy('first_name')->get();
        $previousMeetings = $ministry->meetings()->latest('date')->limit(10)->get();

        return view('meetings.create', compact('ministry', 'members', 'previousMeetings'));
    }

    public function store(Request $request, Ministry $ministry)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'copy_from_id' => 'nullable|exists:ministry_meetings,id',
        ]);

        $validated['ministry_id'] = $ministry->id;
        $validated['created_by'] = auth()->id();

        // If copying from previous meeting
        if ($request->copy_from_id) {
            $sourceMeeting = MinistryMeeting::where('ministry_id', $ministry->id)->findOrFail($request->copy_from_id);
            $meeting = $sourceMeeting->copyToNewMeeting($validated['date']);
            $meeting->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'theme' => $validated['theme'],
            ]);
        } else {
            $meeting = MinistryMeeting::create($validated);
        }

        // Auto-invite all ministry members
        foreach ($ministry->members as $member) {
            MeetingAttendee::create([
                'meeting_id' => $meeting->id,
                'person_id' => $member->id,
                'status' => 'invited',
            ]);
        }

        return redirect()->route('meetings.show', [$ministry, $meeting])
            ->with('success', 'Зустріч створено');
    }

    public function show(Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($meeting->ministry_id === $ministry->id, 404);

        $meeting->load(['agendaItems.responsible', 'materials', 'attendees.person', 'copiedFrom']);

        $availableMembers = $ministry->members()
            ->whereNotIn('people.id', $meeting->attendees->pluck('person_id'))
            ->orderBy('first_name')
            ->get();

        return view('meetings.show', compact('ministry', 'meeting', 'availableMembers'));
    }

    public function edit(Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($meeting->ministry_id === $ministry->id, 404);

        $members = $ministry->members()->orderBy('first_name')->get();

        return view('meetings.edit', compact('ministry', 'meeting', 'members'));
    }

    public function update(Request $request, Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($meeting->ministry_id === $ministry->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'summary' => 'nullable|string',
        ]);

        $meeting->update($validated);

        return redirect()->route('meetings.show', [$ministry, $meeting])
            ->with('success', 'Зустріч оновлено');
    }

    public function destroy(Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($meeting->ministry_id === $ministry->id, 404);

        $meeting->delete();

        return redirect()->route('meetings.index', $ministry)
            ->with('success', 'Зустріч видалено');
    }

    public function copy(Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($meeting->ministry_id === $ministry->id, 404);

        return view('meetings.copy', compact('ministry', 'meeting'));
    }

    public function storeCopy(Request $request, Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'nullable|string|max:255',
        ]);

        $newMeeting = $meeting->copyToNewMeeting($validated['date']);

        if ($request->filled('title')) {
            $newMeeting->update(['title' => $validated['title']]);
        }

        return redirect()->route('meetings.show', [$ministry, $newMeeting])
            ->with('success', 'Зустріч скопійовано');
    }

    // Agenda Items
    public function storeAgendaItem(Request $request, Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'responsible_id' => ['nullable', \Illuminate\Validation\Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $validated['meeting_id'] = $meeting->id;
        $validated['sort_order'] = $meeting->agendaItems()->max('sort_order') + 1;

        MeetingAgendaItem::create($validated);

        return back()->with('success', 'Пункт додано');
    }

    public function updateAgendaItem(Request $request, MeetingAgendaItem $item)
    {
        $this->authorizeAgendaItem($item);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'responsible_id' => ['nullable', \Illuminate\Validation\Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $item->update($validated);

        return back()->with('success', 'Пункт оновлено');
    }

    public function toggleAgendaItem(MeetingAgendaItem $item)
    {
        $this->authorizeAgendaItem($item);
        $item->update(['is_completed' => !$item->is_completed]);

        return back();
    }

    public function destroyAgendaItem(MeetingAgendaItem $item)
    {
        $this->authorizeAgendaItem($item);
        $item->delete();

        return back()->with('success', 'Пункт видалено');
    }

    public function reorderAgendaItems(Request $request, MinistryMeeting $meeting)
    {
        abort_unless($meeting->ministry, 404);
        $this->authorizeMinistry($meeting->ministry);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:meeting_agenda_items,id',
        ]);

        foreach ($validated['items'] as $index => $itemId) {
            MeetingAgendaItem::where('id', $itemId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // Materials
    public function storeMaterial(Request $request, Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:link,file,note,video,audio,document',
            'content' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $validated['meeting_id'] = $meeting->id;
        $validated['sort_order'] = $meeting->materials()->max('sort_order') + 1;

        MeetingMaterial::create($validated);

        return back()->with('success', 'Матеріал додано');
    }

    public function destroyMaterial(MeetingMaterial $material)
    {
        $meeting = $material->meeting;
        abort_unless($meeting?->ministry, 404);
        $this->authorizeMinistry($meeting->ministry);
        $material->delete();

        return back()->with('success', 'Матеріал видалено');
    }

    // Attendees
    public function storeAttendee(Request $request, Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'person_id' => ['required', \Illuminate\Validation\Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        MeetingAttendee::updateOrCreate(
            [
                'meeting_id' => $meeting->id,
                'person_id' => $validated['person_id'],
            ],
            ['status' => 'invited']
        );

        return back()->with('success', 'Учасника додано');
    }

    public function updateAttendee(Request $request, MeetingAttendee $attendee)
    {
        $this->authorizeAttendee($attendee);

        $validated = $request->validate([
            'status' => 'required|in:invited,confirmed,attended,absent',
        ]);

        $attendee->update($validated);

        return back();
    }

    public function destroyAttendee(MeetingAttendee $attendee)
    {
        $this->authorizeAttendee($attendee);
        $attendee->delete();

        return back()->with('success', 'Учасника видалено');
    }

    public function markAllAttended(Ministry $ministry, MinistryMeeting $meeting)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($meeting->ministry_id === $ministry->id, 404);

        $meeting->attendees()->where('status', '!=', 'absent')->update(['status' => 'attended']);

        return back()->with('success', 'Всіх відмічено як присутніх');
    }

    private function authorizeMinistry(Ministry $ministry): void
    {
        if ($ministry->church_id !== $this->getCurrentChurch()->id) {
            abort(403);
        }
    }

    private function authorizeAgendaItem(MeetingAgendaItem $item): void
    {
        $meeting = $item->meeting;
        abort_unless($meeting?->ministry, 404);
        $this->authorizeMinistry($meeting->ministry);
    }

    private function authorizeAttendee(MeetingAttendee $attendee): void
    {
        $meeting = $attendee->meeting;
        abort_unless($meeting?->ministry, 404);
        $this->authorizeMinistry($meeting->ministry);
    }
}
