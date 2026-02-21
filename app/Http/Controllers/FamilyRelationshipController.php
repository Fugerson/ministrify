<?php

namespace App\Http\Controllers;

use App\Models\FamilyRelationship;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FamilyRelationshipController extends Controller
{
    /**
     * Store a new family relationship
     */
    public function store(Request $request, Person $person)
    {
        abort_unless(auth()->user()->canEdit('people'), 403);

        $church = $this->getCurrentChurch();

        if ($person->church_id !== $church->id) {
            abort(403);
        }

        $validated = $request->validate([
            'related_person_id' => 'required|exists:people,id',
            'relationship_type' => 'required|in:spouse,child,parent,sibling',
        ]);

        // Ensure related person is from same church
        $relatedPerson = Person::where('id', $validated['related_person_id'])
            ->where('church_id', $church->id)
            ->firstOrFail();

        // Prevent self-relationship
        if ($person->id === $relatedPerson->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Не можна створити зв\'язок з самим собою'], 422);
            }
            return back()->with('error', 'Не можна створити зв\'язок з самим собою');
        }

        // Check if relationship already exists (in either direction)
        $existingRelationship = FamilyRelationship::where('church_id', $church->id)
            ->where(function ($query) use ($person, $relatedPerson, $validated) {
                $query->where(function ($q) use ($person, $relatedPerson, $validated) {
                    $q->where('person_id', $person->id)
                      ->where('related_person_id', $relatedPerson->id)
                      ->where('relationship_type', $validated['relationship_type']);
                })->orWhere(function ($q) use ($person, $relatedPerson, $validated) {
                    $inverseType = FamilyRelationship::getInverseType($validated['relationship_type']);
                    $q->where('person_id', $relatedPerson->id)
                      ->where('related_person_id', $person->id)
                      ->where('relationship_type', $inverseType);
                });
            })
            ->exists();

        if ($existingRelationship) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Цей зв\'язок вже існує'], 422);
            }
            return back()->with('error', 'Цей зв\'язок вже існує');
        }

        // For spouse relationship, check if either person already has a spouse
        if ($validated['relationship_type'] === FamilyRelationship::TYPE_SPOUSE) {
            $personHasSpouse = FamilyRelationship::where('church_id', $church->id)
                ->where('relationship_type', FamilyRelationship::TYPE_SPOUSE)
                ->where(function ($q) use ($person) {
                    $q->where('person_id', $person->id)
                      ->orWhere('related_person_id', $person->id);
                })
                ->exists();

            $relatedHasSpouse = FamilyRelationship::where('church_id', $church->id)
                ->where('relationship_type', FamilyRelationship::TYPE_SPOUSE)
                ->where(function ($q) use ($relatedPerson) {
                    $q->where('person_id', $relatedPerson->id)
                      ->orWhere('related_person_id', $relatedPerson->id);
                })
                ->exists();

            if ($personHasSpouse) {
                $error = $person->full_name . ' вже має чоловіка/дружину';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => $error], 422);
                }
                return back()->with('error', $error);
            }

            if ($relatedHasSpouse) {
                $error = $relatedPerson->full_name . ' вже має чоловіка/дружину';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => $error], 422);
                }
                return back()->with('error', $error);
            }
        }

        $relationship = FamilyRelationship::create([
            'church_id' => $church->id,
            'person_id' => $person->id,
            'related_person_id' => $relatedPerson->id,
            'relationship_type' => $validated['relationship_type'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'member' => [
                    'relationship_id' => $relationship->id,
                    'person_id' => $relatedPerson->id,
                    'full_name' => $relatedPerson->full_name,
                    'first_name' => $relatedPerson->first_name,
                    'photo' => $relatedPerson->photo ? Storage::url($relatedPerson->photo) : null,
                    'relationship_label' => FamilyRelationship::getTypes()[$validated['relationship_type']] ?? $validated['relationship_type'],
                ],
            ]);
        }

        return back()->with('success', 'Зв\'язок успішно створено');
    }

    /**
     * Remove a family relationship
     */
    public function destroy(Request $request, FamilyRelationship $familyRelationship)
    {
        abort_unless(auth()->user()->canEdit('people'), 403);

        $church = $this->getCurrentChurch();

        if ($familyRelationship->church_id !== $church->id) {
            abort(403);
        }

        $familyRelationship->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Зв\'язок успішно видалено');
    }

    /**
     * Search people for adding family members (AJAX)
     */
    public function search(Request $request, Person $person)
    {
        abort_unless(auth()->user()->canView('people'), 403);

        $church = $this->getCurrentChurch();

        if ($person->church_id !== $church->id) {
            abort(403);
        }

        $search = addcslashes($request->get('q', ''), '%_');

        $people = Person::where('church_id', $church->id)
            ->where('id', '!=', $person->id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->select('id', 'first_name', 'last_name', 'photo')
            ->orderBy('first_name')
            ->limit(20)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->full_name,
                    'photo' => $p->photo ? Storage::url($p->photo) : null,
                ];
            });

        return response()->json($people);
    }
}
