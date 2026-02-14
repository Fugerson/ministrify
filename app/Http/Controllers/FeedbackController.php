<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->canView('settings')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу.');
        }

        $church = $this->getCurrentChurch();

        $query = Feedback::where('church_id', $church->id);

        // Filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $feedbacks = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => Feedback::where('church_id', $church->id)->count(),
            'new' => Feedback::where('church_id', $church->id)->where('status', 'new')->count(),
            'avg_rating' => Feedback::where('church_id', $church->id)->whereNotNull('rating')->avg('rating'),
            'by_category' => Feedback::where('church_id', $church->id)
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_rating' => Feedback::where('church_id', $church->id)
                ->whereNotNull('rating')
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
            'weekly' => Feedback::where('church_id', $church->id)
                ->where('created_at', '>=', now()->subWeeks(12))
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%u') as week, AVG(rating) as avg_rating, COUNT(*) as count")
                ->groupBy('week')
                ->orderBy('week')
                ->get()
                ->toArray(),
        ];

        return view('feedback.index', compact('feedbacks', 'stats'));
    }

    public function updateStatus(Request $request, Feedback $feedback)
    {
        if (!auth()->user()->canView('settings')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($feedback->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:new,read,archived',
        ]);

        $feedback->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Статус оновлено');
    }

    public function updateNotes(Request $request, Feedback $feedback)
    {
        if (!auth()->user()->canView('settings')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($feedback->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $feedback->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Нотатки збережено');
    }

    public function destroy(Feedback $feedback)
    {
        if (!auth()->user()->canView('settings')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($feedback->church_id !== $church->id) {
            abort(404);
        }

        $feedback->delete();

        return back()->with('success', 'Відгук видалено');
    }
}
