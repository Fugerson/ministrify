<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Tag;
use App\Models\UnavailableDate;
use App\Exports\PeopleExport;
use App\Imports\PeopleImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = Person::where('church_id', $church->id)
            ->with(['tags', 'ministries']);

        // Search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Filter by tag
        if ($tagId = $request->get('tag')) {
            $query->withTag($tagId);
        }

        // Filter by ministry
        if ($ministryId = $request->get('ministry')) {
            $query->inMinistry($ministryId);
        }

        $people = $query->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $tags = Tag::where('church_id', $church->id)->get();
        $ministries = $church->ministries;

        return view('people.index', compact('people', 'tags', 'ministries'));
    }

    public function create()
    {
        $church = $this->getCurrentChurch();
        $tags = Tag::where('church_id', $church->id)->get();
        $ministries = $church->ministries()->with('positions')->get();

        return view('people.create', compact('tags', 'ministries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'joined_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'ministries' => 'nullable|array',
        ]);

        $church = $this->getCurrentChurch();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('people', 'public');
        }

        $validated['church_id'] = $church->id;
        $person = Person::create($validated);

        // Attach tags
        if ($request->has('tags')) {
            $person->tags()->sync($request->tags);
        }

        // Attach ministries with positions
        if ($request->has('ministries')) {
            foreach ($request->ministries as $ministryId => $data) {
                if (!empty($data['selected'])) {
                    $person->ministries()->attach($ministryId, [
                        'position_ids' => json_encode($data['positions'] ?? []),
                    ]);
                }
            }
        }

        return redirect()->route('people.show', $person)
            ->with('success', 'Людину успішно додано.');
    }

    public function show(Person $person)
    {
        $this->authorizeChurch($person);

        $person->load(['tags', 'ministries.positions', 'assignments' => function ($q) {
            $q->whereHas('event', fn($eq) => $eq->where('date', '>=', now()->subMonths(3)))
              ->with(['event.ministry', 'position'])
              ->orderByDesc('created_at');
        }]);

        // Stats
        $stats = [
            'services_this_month' => $person->assignments()
                ->where('status', 'confirmed')
                ->whereHas('event', fn($q) => $q->whereMonth('date', now()->month))
                ->count(),
            'attendance_30_days' => $person->attendanceRecords()
                ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subDays(30)))
                ->where('present', true)
                ->count(),
        ];

        return view('people.show', compact('person', 'stats'));
    }

    public function edit(Person $person)
    {
        $this->authorizeChurch($person);

        $church = $this->getCurrentChurch();
        $tags = Tag::where('church_id', $church->id)->get();
        $ministries = $church->ministries()->with('positions')->get();

        $person->load(['tags', 'ministries']);

        return view('people.edit', compact('person', 'tags', 'ministries'));
    }

    public function update(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'joined_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'ministries' => 'nullable|array',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }
            $validated['photo'] = $request->file('photo')->store('people', 'public');
        }

        $person->update($validated);

        // Sync tags
        $person->tags()->sync($request->tags ?? []);

        // Sync ministries with positions
        $person->ministries()->detach();
        if ($request->has('ministries')) {
            foreach ($request->ministries as $ministryId => $data) {
                if (!empty($data['selected'])) {
                    $person->ministries()->attach($ministryId, [
                        'position_ids' => json_encode($data['positions'] ?? []),
                    ]);
                }
            }
        }

        return redirect()->route('people.show', $person)
            ->with('success', 'Дані успішно оновлено.');
    }

    public function destroy(Person $person)
    {
        $this->authorizeChurch($person);

        $person->delete();

        return redirect()->route('people.index')
            ->with('success', 'Людину видалено.');
    }

    public function restore(Person $person)
    {
        $this->authorizeChurch($person);

        $person->restore();

        return redirect()->route('people.show', $person)
            ->with('success', 'Людину відновлено.');
    }

    public function export()
    {
        $church = $this->getCurrentChurch();
        $filename = 'people_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PeopleExport($church->id), $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $church = $this->getCurrentChurch();

        try {
            Excel::import(new PeopleImport($church->id), $request->file('file'));
            return back()->with('success', 'Людей успішно імпортовано.');
        } catch (\Exception $e) {
            return back()->with('error', 'Помилка імпорту: ' . $e->getMessage());
        }
    }

    public function myProfile()
    {
        $user = auth()->user();

        if (!$user->person) {
            return redirect()->route('dashboard')
                ->with('error', 'Ваш профіль не знайдено.');
        }

        $person = $user->person->load(['tags', 'ministries', 'unavailableDates' => function ($q) {
            $q->where('date_to', '>=', now())->orderBy('date_from');
        }]);

        $upcomingAssignments = $person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
            ->orderBy('created_at')
            ->get();

        return view('people.my-profile', compact('person', 'upcomingAssignments'));
    }

    public function updateMyProfile(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            abort(404);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $user->person->update($validated);

        return back()->with('success', 'Профіль оновлено.');
    }

    public function addUnavailableDate(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            abort(404);
        }

        $validated = $request->validate([
            'date_from' => 'required|date|after_or_equal:today',
            'date_to' => 'required|date|after_or_equal:date_from',
            'reason' => 'nullable|string|max:255',
        ]);

        $validated['person_id'] = $user->person->id;
        UnavailableDate::create($validated);

        return back()->with('success', 'Дати недоступності додано.');
    }

    public function removeUnavailableDate(UnavailableDate $unavailableDate)
    {
        $user = auth()->user();

        if (!$user->person || $unavailableDate->person_id !== $user->person->id) {
            abort(403);
        }

        $unavailableDate->delete();

        return back()->with('success', 'Дати видалено.');
    }

    private function authorizeChurch(Person $person): void
    {
        if ($person->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
