<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        return redirect()->route('settings.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        Tag::create($validated);

        return back()->with('success', 'Тег створено.');
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorizeChurch($tag);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $tag->update($validated);

        return back()->with('success', 'Тег оновлено.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorizeChurch($tag);

        $tag->delete();

        return back()->with('success', 'Тег видалено.');
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
