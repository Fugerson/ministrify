<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\MinistryType;
use Illuminate\Http\Request;

class MinistryTypeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $church = $this->getCurrentChurch();

        $maxOrder = $church->ministryTypes()->max('sort_order') ?? 0;

        $church->ministryTypes()->create([
            'name' => $validated['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Тип служіння додано.');
    }

    public function update(Request $request, MinistryType $ministryType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $ministryType->update($validated);

        return back()->with('success', 'Тип служіння оновлено.');
    }

    public function destroy(MinistryType $ministryType)
    {
        // Check if any ministries are using this type
        if ($ministryType->ministries()->exists()) {
            return back()->with('error', 'Неможливо видалити тип, який використовується служіннями.');
        }

        $ministryType->delete();

        return back()->with('success', 'Тип служіння видалено.');
    }

    public function updateMinistryType(Request $request, Ministry $ministry)
    {
        $validated = $request->validate([
            'type_id' => 'nullable|exists:ministry_types,id',
        ]);

        $ministry->update(['type_id' => $validated['type_id']]);

        return back()->with('success', 'Тип служіння оновлено.');
    }

    public function destroyMinistry(Ministry $ministry)
    {
        $ministry->delete();

        return back()->with('success', 'Служіння видалено.');
    }
}
