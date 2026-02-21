<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PositionController extends Controller
{
    public function store(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxOrder = $ministry->positions()->max('sort_order') ?? -1;

        $ministry->positions()->create([
            'name' => $validated['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Позицію створено.');
    }

    public function update(Request $request, Position $position)
    {
        $ministry = $position->ministry;
        abort_unless($ministry, 404);
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $position->update($validated);

        return back()->with('success', 'Позицію оновлено.');
    }

    public function destroy(Position $position)
    {
        $ministry = $position->ministry;
        abort_unless($ministry, 404);
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $position->delete();

        return back()->with('success', 'Позицію видалено.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:positions,id',
            'positions.*.sort_order' => 'required|integer|min:0',
        ]);

        $church = $this->getCurrentChurch();

        // Validate all positions belong to ministries in the current church
        $positionIds = collect($validated['positions'])->pluck('id');
        $positions = Position::whereIn('id', $positionIds)
            ->with('ministry')
            ->get();

        foreach ($positions as $position) {
            if (!$position->ministry || $position->ministry->church_id !== $church->id) {
                abort(403, 'Немає доступу до цієї позиції.');
            }
        }

        // Verify user can manage at least one of the ministries
        $ministryIds = $positions->pluck('ministry.id')->unique();
        $user = auth()->user();

        if (!$user->isAdmin()) {
            foreach ($ministryIds as $ministryId) {
                $ministry = Ministry::find($ministryId);
                if ($ministry) {
                    Gate::authorize('contribute-ministry', $ministry);
                }
            }
        }

        // Update sort order
        foreach ($validated['positions'] as $item) {
            Position::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
