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

        $person->load(['tags', 'ministries.positions', 'groups', 'assignments' => function ($q) {
            $q->whereHas('event', fn($eq) => $eq->where('date', '>=', now()->subMonths(3)))
              ->with(['event.ministry', 'position'])
              ->orderByDesc('created_at');
        }]);

        // Stats
        $stats = [
            'services_this_month' => $person->assignments()
                ->where('status', 'confirmed')
                ->whereHas('event', fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year))
                ->count(),
            'services_total' => $person->assignments()
                ->where('status', 'confirmed')
                ->count(),
            'attendance_30_days' => $person->attendanceRecords()
                ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subDays(30)))
                ->where('present', true)
                ->count(),
            'attendance_rate' => $this->calculateAttendanceRate($person),
            'last_attended' => $person->attendanceRecords()
                ->whereHas('attendance')
                ->where('present', true)
                ->with('attendance')
                ->orderByDesc('created_at')
                ->first()?->attendance?->date,
            'membership_days' => $person->joined_date ? now()->diffInDays($person->joined_date) : null,
        ];

        // Activity history (last 3 months)
        $activities = collect();

        // Get attendance records
        $attendanceRecords = $person->attendanceRecords()
            ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
            ->with('attendance')
            ->get()
            ->map(fn($r) => [
                'type' => 'attendance',
                'date' => $r->attendance->date,
                'title' => $r->present ? 'Відвідав(ла) служіння' : 'Пропустив(ла) служіння',
                'icon' => $r->present ? '✅' : '❌',
                'color' => $r->present ? 'green' : 'red',
            ]);

        // Get assignments
        $assignmentRecords = $person->assignments()
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
            ->with(['event.ministry', 'position'])
            ->get()
            ->map(fn($a) => [
                'type' => 'assignment',
                'date' => $a->event->date,
                'title' => $a->event->title . ' - ' . $a->position->name,
                'subtitle' => $a->event->ministry->icon . ' ' . $a->event->ministry->name,
                'icon' => $a->status === 'confirmed' ? '🎯' : ($a->status === 'pending' ? '⏳' : '❌'),
                'color' => $a->status === 'confirmed' ? 'green' : ($a->status === 'pending' ? 'yellow' : 'red'),
                'status' => $a->status,
            ]);

        $activities = $attendanceRecords->merge($assignmentRecords)
            ->sortByDesc('date')
            ->take(20)
            ->values();

        // Attendance chart data (last 12 weeks)
        $attendanceChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $attended = $person->attendanceRecords()
                ->whereHas('attendance', fn($q) => $q->whereBetween('date', [$weekStart, $weekEnd]))
                ->where('present', true)
                ->count();

            $attendanceChartData[] = [
                'week' => $weekStart->format('d.m'),
                'count' => $attended,
            ];
        }

        return view('people.show', compact('person', 'stats', 'activities', 'attendanceChartData'));
    }

    private function calculateAttendanceRate(Person $person): ?int
    {
        $totalEvents = $person->attendanceRecords()
            ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
            ->count();

        if ($totalEvents === 0) {
            return null;
        }

        $attended = $person->attendanceRecords()
            ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
            ->where('present', true)
            ->count();

        return round(($attended / $totalEvents) * 100);
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
        } elseif ($request->input('remove_photo') === '1') {
            // Remove photo if requested
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }
            $validated['photo'] = null;
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

    public function generateTelegramCode()
    {
        $user = auth()->user();

        if (!$user->person) {
            return response()->json(['error' => 'Профіль не знайдено'], 404);
        }

        $church = $user->church;

        if (!$church->telegram_bot_token) {
            return response()->json(['error' => 'Telegram бот не налаштовано церквою'], 400);
        }

        $code = \App\Http\Controllers\Api\TelegramController::generateLinkingCode($user->person);

        // Get bot username for link
        try {
            $telegram = new \App\Services\TelegramService($church->telegram_bot_token);
            $botInfo = $telegram->getMe();
            $botUsername = $botInfo['username'];
        } catch (\Exception $e) {
            $botUsername = null;
        }

        return response()->json([
            'code' => $code,
            'bot_username' => $botUsername,
            'expires_in' => 10, // minutes
        ]);
    }

    public function unlinkTelegram()
    {
        $user = auth()->user();

        if (!$user->person) {
            return back()->with('error', 'Профіль не знайдено');
        }

        $user->person->update(['telegram_chat_id' => null]);

        return back()->with('success', 'Telegram від\'єднано');
    }

    private function authorizeChurch(Person $person): void
    {
        if ($person->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
