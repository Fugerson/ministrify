<?php

namespace App\Http\Controllers;

use App\Models\SchedulingPreference;
use App\Models\MinistryPreference;
use App\Models\PositionPreference;
use Illuminate\Http\Request;

class SchedulingPreferenceController extends Controller
{
    /**
     * Show scheduling preferences page
     */
    public function index()
    {
        $person = auth()->user()->person;

        if (!$person) {
            return redirect()->route('dashboard')
                ->with('error', 'Профіль не знайдено');
        }

        $preference = $person->getOrCreateSchedulingPreference();
        $preference->load(['ministryPreferences.ministry', 'positionPreferences.position']);

        $ministries = $person->ministries()->with('positions')->get();

        // Get other people from the same church for "prefer with" option
        $otherPeople = \App\Models\Person::where('church_id', $person->church_id)
            ->where('id', '!=', $person->id)
            ->whereHas('ministries')
            ->orderBy('first_name')
            ->get();

        return view('scheduling-preferences.index', compact('preference', 'ministries', 'person', 'otherPeople'));
    }

    /**
     * Update general scheduling preferences
     */
    public function update(Request $request)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return back()->with('error', 'Профіль не знайдено');
        }

        $validated = $request->validate([
            'max_times_per_month' => 'nullable|integer|min:1|max:30',
            'preferred_times_per_month' => 'nullable|integer|min:0|max:30',
            'prefer_with_person_id' => 'nullable|exists:people,id',
            'household_preference' => 'required|in:none,together,separate',
            'scheduling_notes' => 'nullable|string|max:500',
        ]);

        $preference = $person->getOrCreateSchedulingPreference();
        $preference->update($validated);

        return back()->with('success', 'Налаштування збережено');
    }

    /**
     * Update ministry-specific preferences
     */
    public function updateMinistry(Request $request, $ministryId)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return response()->json(['error' => 'Профіль не знайдено'], 404);
        }

        // Verify person belongs to this ministry
        if (!$person->ministries()->where('ministries.id', $ministryId)->exists()) {
            return response()->json(['error' => 'Ви не належите до цього служіння'], 403);
        }

        $validated = $request->validate([
            'max_times_per_month' => 'nullable|integer|min:1|max:30',
            'preferred_times_per_month' => 'nullable|integer|min:0|max:30',
        ]);

        $preference = $person->getOrCreateSchedulingPreference();

        MinistryPreference::updateOrCreate(
            [
                'scheduling_preference_id' => $preference->id,
                'ministry_id' => $ministryId,
            ],
            $validated
        );

        return response()->json(['success' => true, 'message' => 'Збережено']);
    }

    /**
     * Update position-specific preferences
     */
    public function updatePosition(Request $request, $positionId)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return response()->json(['error' => 'Профіль не знайдено'], 404);
        }

        // Verify position belongs to current church
        $positionExists = \App\Models\Position::where('id', $positionId)
            ->whereHas('ministry', fn($q) => $q->where('church_id', $this->getCurrentChurch()->id))
            ->exists();
        abort_unless($positionExists, 404);

        $validated = $request->validate([
            'max_times_per_month' => 'nullable|integer|min:1|max:30',
            'preferred_times_per_month' => 'nullable|integer|min:0|max:30',
        ]);

        $preference = $person->getOrCreateSchedulingPreference();

        PositionPreference::updateOrCreate(
            [
                'scheduling_preference_id' => $preference->id,
                'position_id' => $positionId,
            ],
            $validated
        );

        return response()->json(['success' => true, 'message' => 'Збережено']);
    }

    /**
     * Delete ministry preference (reset to default)
     */
    public function deleteMinistry($ministryId)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return response()->json(['error' => 'Профіль не знайдено'], 404);
        }

        $preference = $person->schedulingPreference;
        if ($preference) {
            MinistryPreference::where('scheduling_preference_id', $preference->id)
                ->where('ministry_id', $ministryId)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete position preference (reset to default)
     */
    public function deletePosition($positionId)
    {
        $person = auth()->user()->person;

        if (!$person) {
            return response()->json(['error' => 'Профіль не знайдено'], 404);
        }

        $preference = $person->schedulingPreference;
        if ($preference) {
            PositionPreference::where('scheduling_preference_id', $preference->id)
                ->where('position_id', $positionId)
                ->delete();
        }

        return response()->json(['success' => true]);
    }
}
