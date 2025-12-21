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
        Gate::authorize('manage-ministry', $ministry);

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
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $position->update($validated);

        return back()->with('success', 'Позицію оновлено.');
    }

    public function destroy(Position $position)
    {
        $ministry = $position->ministry;
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

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

        foreach ($validated['positions'] as $item) {
            Position::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    private function authorizeChurch(Ministry $ministry): void
    {
        if ($ministry->church_id !== auth()->user()->church_id) {
            abort(404);
        }
    }
}
