<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class ShepherdController extends Controller
{
    /**
     * Display list of shepherds for management
     */
    public function index()
    {
        $church = $this->getCurrentChurch();

        // Redirect if feature is disabled
        if (!$church->shepherds_enabled) {
            return redirect()->route('settings.index')
                ->with('error', 'Функція опікунів вимкнена');
        }

        $shepherds = Person::where('church_id', $church->id)
            ->where('is_shepherd', true)
            ->with('churchRoleRelation')
            ->withCount('sheep')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $availablePeople = Person::where('church_id', $church->id)
            ->where('is_shepherd', false)
            ->with('churchRoleRelation')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('settings.shepherds.index', compact('shepherds', 'availablePeople'));
    }

    /**
     * Add a person as shepherd
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
        ]);

        $church = $this->getCurrentChurch();

        if (!$church->shepherds_enabled) {
            return response()->json(['message' => 'Функція опікунів вимкнена'], 400);
        }

        $person = Person::findOrFail($validated['person_id']);

        if ($person->church_id !== $church->id) {
            abort(404);
        }

        $person->update(['is_shepherd' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'shepherd' => [
                    'id' => $person->id,
                    'full_name' => $person->full_name,
                    'photo' => $person->photo,
                    'sheep_count' => 0,
                ],
            ]);
        }

        return back()->with('success', 'Опікуна додано');
    }

    /**
     * Remove shepherd status from a person
     */
    public function destroy(Person $person)
    {
        $this->authorizeChurch($person);

        // Remove all sheep assignments before removing shepherd status
        Person::where('shepherd_id', $person->id)->update(['shepherd_id' => null]);

        $person->update(['is_shepherd' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle shepherds feature for church
     */
    public function toggleFeature(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $church->update(['shepherds_enabled' => $validated['enabled']]);

        return response()->json(['success' => true]);
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
