<?php

namespace App\Http\Controllers;

use App\Models\BlockoutDate;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Http\Request;

class BlockoutDateController extends Controller
{
    /**
     * Display user's blockout dates (My Profile section)
     */
    public function index()
    {
        $person = auth()->user()->person;

        if (!$person) {
            return redirect()->route('dashboard')
                ->with('error', 'Профіль не знайдено');
        }

        $blockouts = $person->blockoutDates()
            ->orderBy('start_date')
            ->get()
            ->groupBy(fn($b) => $b->status);

        $ministries = $person->ministries;

        return view('blockouts.index', compact('blockouts', 'ministries', 'person'));
    }

    /**
     * Show form for creating new blockout
     */
    public function create()
    {
        $person = auth()->user()->person;

        if (!$person) {
            return redirect()->route('dashboard')
                ->with('error', 'Профіль не знайдено');
        }

        $ministries = $person->ministries;

        return view('blockouts.create', compact('ministries', 'person'));
    }

    /**
     * Store new blockout date
     */
    public function store(Request $request)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return back()->with('error', 'Профіль не знайдено');
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'start_time' => 'nullable|required_if:all_day,false|date_format:H:i',
            'end_time' => 'nullable|required_if:all_day,false|date_format:H:i|after:start_time',
            'reason' => 'required|in:vacation,travel,sick,family,work,other',
            'reason_note' => 'nullable|string|max:255',
            'applies_to_all' => 'boolean',
            'ministry_ids' => 'nullable|array',
            'ministry_ids.*' => 'exists:ministries,id',
            'recurrence' => 'required|in:none,weekly,biweekly,monthly,custom',
            'recurrence_end_date' => 'nullable|date|after:end_date',
        ]);

        $blockout = BlockoutDate::create([
            'person_id' => $person->id,
            'church_id' => $person->church_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'all_day' => $validated['all_day'] ?? true,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'reason' => $validated['reason'],
            'reason_note' => $validated['reason_note'] ?? null,
            'applies_to_all' => $validated['applies_to_all'] ?? true,
            'recurrence' => $validated['recurrence'],
            'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
            'status' => 'active',
        ]);

        // Attach specific ministries if not applies_to_all
        if (!($validated['applies_to_all'] ?? true) && !empty($validated['ministry_ids'])) {
            $blockout->ministries()->sync($validated['ministry_ids']);
        }

        // Auto-decline pending assignments that conflict
        $this->handleConflictingAssignments($person, $blockout);

        return redirect()->route('blockouts.index')
            ->with('success', 'Період недоступності додано');
    }

    /**
     * Show form for editing blockout
     */
    public function edit(BlockoutDate $blockout)
    {
        $person = auth()->user()->person;

        if (!$person || $blockout->person_id !== $person->id) {
            abort(404);
        }

        $ministries = $person->ministries;

        return view('blockouts.edit', compact('blockout', 'ministries', 'person'));
    }

    /**
     * Update blockout date
     */
    public function update(Request $request, BlockoutDate $blockout)
    {
        $person = auth()->user()->person;

        if (!$person || $blockout->person_id !== $person->id) {
            abort(404);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'start_time' => 'nullable|required_if:all_day,false|date_format:H:i',
            'end_time' => 'nullable|required_if:all_day,false|date_format:H:i|after:start_time',
            'reason' => 'required|in:vacation,travel,sick,family,work,other',
            'reason_note' => 'nullable|string|max:255',
            'applies_to_all' => 'boolean',
            'ministry_ids' => 'nullable|array',
            'ministry_ids.*' => 'exists:ministries,id',
            'recurrence' => 'required|in:none,weekly,biweekly,monthly,custom',
            'recurrence_end_date' => 'nullable|date|after:end_date',
        ]);

        $blockout->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'all_day' => $validated['all_day'] ?? true,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'reason' => $validated['reason'],
            'reason_note' => $validated['reason_note'] ?? null,
            'applies_to_all' => $validated['applies_to_all'] ?? true,
            'recurrence' => $validated['recurrence'],
            'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
        ]);

        // Update ministries
        if (!($validated['applies_to_all'] ?? true) && !empty($validated['ministry_ids'])) {
            $blockout->ministries()->sync($validated['ministry_ids']);
        } else {
            $blockout->ministries()->detach();
        }

        return redirect()->route('blockouts.index')
            ->with('success', 'Період недоступності оновлено');
    }

    /**
     * Delete blockout date
     */
    public function destroy(BlockoutDate $blockout)
    {
        $person = auth()->user()->person;

        if (!$person || $blockout->person_id !== $person->id) {
            abort(404);
        }

        $blockout->delete();

        return redirect()->route('blockouts.index')
            ->with('success', 'Період недоступності видалено');
    }

    /**
     * Cancel a blockout (mark as cancelled instead of deleting)
     */
    public function cancel(BlockoutDate $blockout)
    {
        $person = auth()->user()->person;

        if (!$person || $blockout->person_id !== $person->id) {
            abort(404);
        }

        $blockout->update(['status' => 'cancelled']);

        return back()->with('success', 'Період недоступності скасовано');
    }

    /**
     * Quick add blockout (AJAX)
     */
    public function quickStore(Request $request)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return response()->json(['error' => 'Профіль не знайдено'], 404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'reason' => 'required|in:vacation,travel,sick,family,work,other',
        ]);

        $blockout = BlockoutDate::create([
            'person_id' => $person->id,
            'church_id' => $person->church_id,
            'start_date' => $validated['date'],
            'end_date' => $validated['date'],
            'all_day' => true,
            'reason' => $validated['reason'],
            'applies_to_all' => true,
            'recurrence' => 'none',
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'blockout' => $blockout,
            'message' => 'Дату недоступності додано',
        ]);
    }

    /**
     * Get blockouts for calendar display (AJAX)
     */
    public function calendar(Request $request)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return response()->json([]);
        }

        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());

        $blockouts = $person->blockoutDates()
            ->active()
            ->overlapping($start, $end)
            ->get();

        $events = [];
        foreach ($blockouts as $blockout) {
            $events[] = [
                'id' => $blockout->id,
                'title' => $blockout->reason_label,
                'start' => $blockout->start_date->format('Y-m-d'),
                'end' => $blockout->end_date->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'color' => '#ef4444',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'reason' => $blockout->reason,
                    'note' => $blockout->reason_note,
                    'allDay' => $blockout->all_day,
                    'recurrence' => $blockout->recurrence,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Handle conflicting assignments when blockout is created
     */
    protected function handleConflictingAssignments(Person $person, BlockoutDate $blockout)
    {
        // Find pending assignments that conflict with this blockout
        $conflicting = $person->assignments()
            ->where('status', 'pending')
            ->whereHas('event', function ($q) use ($blockout) {
                $q->whereBetween('date', [$blockout->start_date, $blockout->end_date]);

                if (!$blockout->applies_to_all) {
                    $ministryIds = $blockout->ministries->pluck('id');
                    $q->whereIn('ministry_id', $ministryIds);
                }
            })
            ->get();

        // Auto-decline these assignments
        foreach ($conflicting as $assignment) {
            $assignment->update([
                'status' => 'declined',
                'declined_reason' => 'Автоматично відхилено через blockout: ' . $blockout->reason_label,
                'responded_at' => now(),
            ]);
        }

        // Return count for notification
        return $conflicting->count();
    }

    /**
     * Admin: View blockouts for all volunteers
     */
    public function adminIndex(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = BlockoutDate::where('church_id', $church->id)
            ->with(['person', 'ministries'])
            ->orderByDesc('start_date');

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($start = $request->get('start_date')) {
            $query->where('end_date', '>=', $start);
        }
        if ($end = $request->get('end_date')) {
            $query->where('start_date', '<=', $end);
        }

        // Filter by ministry
        if ($ministryId = $request->get('ministry_id')) {
            $query->where(function ($q) use ($ministryId) {
                $q->where('applies_to_all', true)
                  ->orWhereHas('ministries', fn($m) => $m->where('ministries.id', $ministryId));
            });
        }

        $blockouts = $query->paginate(20);
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();

        return view('admin.blockouts.index', compact('blockouts', 'ministries'));
    }
}
