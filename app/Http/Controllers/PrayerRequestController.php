<?php

namespace App\Http\Controllers;

use App\Models\PrayerRequest;
use Illuminate\Http\Request;

class PrayerRequestController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();
        $query = PrayerRequest::where('church_id', $church->id)
            ->with(['person', 'user'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        // Filter: show all or only public
        if (! auth()->user()->hasRole(['admin', 'leader'])) {
            $query->where(function ($q) {
                $q->where('is_public', true)
                    ->orWhere('user_id', auth()->id())
                    ->orWhere('person_id', auth()->user()->person?->id);
            });
        }

        $prayerRequests = $query->paginate(20);

        // Stats
        $stats = [
            'active' => PrayerRequest::where('church_id', $church->id)->active()->count(),
            'answered' => PrayerRequest::where('church_id', $church->id)->where('status', 'answered')->count(),
            'total_prayers' => PrayerRequest::where('church_id', $church->id)->sum('prayer_count'),
        ];

        return view('prayer-requests.index', compact('prayerRequests', 'stats'));
    }

    public function create()
    {
        return view('prayer-requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'is_anonymous' => 'boolean',
            'is_public' => 'boolean',
            'is_urgent' => 'boolean',
        ]);

        $church = $this->getCurrentChurch();

        PrayerRequest::create([
            'church_id' => $church->id,
            'user_id' => auth()->id(),
            'person_id' => auth()->user()->person?->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_anonymous' => $request->boolean('is_anonymous'),
            'is_public' => $request->boolean('is_public', true),
            'is_urgent' => $request->boolean('is_urgent'),
        ]);

        return $this->successResponse($request, 'Молитовне прохання додано.', 'prayer-requests.index');
    }

    public function show(PrayerRequest $prayerRequest)
    {
        $this->authorizeChurch($prayerRequest);

        // Check if user can view private request
        if (! $prayerRequest->is_public) {
            $user = auth()->user();
            if (! $user->hasRole(['admin', 'leader'])
                && $prayerRequest->user_id !== $user->id
                && $prayerRequest->person_id !== $user->person?->id) {
                abort(403);
            }
        }

        $prayerRequest->load(['person', 'user', 'prayedBy']);
        $hasPrayed = $prayerRequest->hasPrayed(auth()->user());

        return view('prayer-requests.show', compact('prayerRequest', 'hasPrayed'));
    }

    public function edit(PrayerRequest $prayerRequest)
    {
        $this->authorizeChurch($prayerRequest);
        $this->authorizeOwner($prayerRequest);

        return view('prayer-requests.edit', compact('prayerRequest'));
    }

    public function update(Request $request, PrayerRequest $prayerRequest)
    {
        $this->authorizeChurch($prayerRequest);
        $this->authorizeOwner($prayerRequest);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'is_anonymous' => 'boolean',
            'is_public' => 'boolean',
            'is_urgent' => 'boolean',
        ]);

        $prayerRequest->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_anonymous' => $request->boolean('is_anonymous'),
            'is_public' => $request->boolean('is_public', true),
            'is_urgent' => $request->boolean('is_urgent'),
        ]);

        return $this->successResponse($request, 'Прохання оновлено.', 'prayer-requests.show', [$prayerRequest]);
    }

    public function destroy(Request $request, PrayerRequest $prayerRequest)
    {
        $this->authorizeChurch($prayerRequest);
        $this->authorizeOwner($prayerRequest);

        $prayerRequest->delete();

        return $this->successResponse($request, 'Прохання видалено.', 'prayer-requests.index');
    }

    public function pray(Request $request, PrayerRequest $prayerRequest)
    {
        $this->authorizeChurch($prayerRequest);

        $prayerRequest->markAsPrayed(auth()->user());

        return $this->successResponse($request, 'Дякуємо за молитву! 🙏');
    }

    public function markAnswered(Request $request, PrayerRequest $prayerRequest)
    {
        $this->authorizeChurch($prayerRequest);
        $this->authorizeOwner($prayerRequest);

        $validated = $request->validate([
            'answer_testimony' => 'nullable|string|max:2000',
        ]);

        $prayerRequest->markAsAnswered($validated['answer_testimony'] ?? null);

        return $this->successResponse($request, 'Слава Богу! Прохання відмічено як відповідь отримано.');
    }

    public function wall()
    {
        $church = $this->getCurrentChurch();

        $requests = PrayerRequest::where('church_id', $church->id)
            ->public()
            ->active()
            ->with(['person', 'user'])
            ->latest()
            ->take(20)
            ->get();

        $answered = PrayerRequest::where('church_id', $church->id)
            ->public()
            ->where('status', 'answered')
            ->whereNotNull('answer_testimony')
            ->with(['person', 'user'])
            ->latest('answered_at')
            ->take(5)
            ->get();

        return view('prayer-requests.wall', compact('requests', 'answered'));
    }

    private function authorizeOwner(PrayerRequest $prayerRequest): void
    {
        $user = auth()->user();
        if ($user->hasRole(['admin', 'leader'])) {
            return;
        }

        if ($prayerRequest->user_id !== $user->id && $prayerRequest->person_id !== $user->person?->id) {
            abort(403);
        }
    }
}
