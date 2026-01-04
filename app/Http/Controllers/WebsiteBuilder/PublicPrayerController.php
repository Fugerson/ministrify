<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\PrayerRequest;
use Illuminate\Http\Request;

class PublicPrayerController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        $church = $this->getChurchOrFail();
        $prayerRequests = PrayerRequest::where('church_id', $church->id)
            ->where('is_from_public', true)
            ->latest()
            ->paginate(20);

        return view('website-builder.prayer-inbox.index', compact('church', 'prayerRequests'));
    }

    public function show(PrayerRequest $prayerRequest)
    {
        $this->authorize('view', $prayerRequest);

        return view('website-builder.prayer-inbox.show', compact('prayerRequest'));
    }

    public function updateStatus(Request $request, PrayerRequest $prayerRequest)
    {
        $this->authorize('update', $prayerRequest);

        $validated = $request->validate([
            'status' => 'required|in:active,answered,closed',
            'answer_testimony' => 'nullable|string|max:2000',
        ]);

        $prayerRequest->update($validated);

        if ($validated['status'] === 'answered') {
            $prayerRequest->update(['answered_at' => now()]);
        }

        return redirect()->back()->with('success', 'Статус оновлено');
    }

    public function destroy(PrayerRequest $prayerRequest)
    {
        $this->authorize('delete', $prayerRequest);

        $prayerRequest->delete();

        return redirect()->route('website-builder.prayer-inbox.index')->with('success', 'Запит видалено');
    }
}
