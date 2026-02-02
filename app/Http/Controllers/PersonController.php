<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use App\Models\Person;
use App\Models\Tag;
use App\Models\User;
use App\Models\UnavailableDate;
use App\Exports\PeopleExport;
use App\Imports\PeopleImport;
use App\Rules\BelongsToChurch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ImageService;

class PersonController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index(Request $request)
    {
        if (!auth()->user()->canView('people')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();

        $query = Person::where('church_id', $church->id)
            ->with(['tags', 'ministries', 'churchRoleRelation', 'shepherd']);

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

        // Filter by age category
        if ($ageCategory = $request->get('age')) {
            $category = Person::AGE_CATEGORIES[$ageCategory] ?? null;
            if ($category) {
                $query->whereNotNull('birth_date')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$category['min']])
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$category['max']]);
            }
        }

        // Filter by church role
        if ($role = $request->get('role')) {
            $query->where('church_role', $role);
        }

        // Limit to prevent memory issues on large churches
        // Client-side filtering requires all data, so we limit instead of paginate
        $people = $query->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(1000)
            ->get();

        $peopleLimited = $people->count() >= 1000;

        $tags = Tag::where('church_id', $church->id)->get();
        $ministries = $church->ministries;
        $churchRoles = \App\Models\ChurchRole::where('church_id', $church->id)->orderBy('sort_order')->get();

        // Calculate statistics using database aggregation (optimized)
        $today = now();
        $statsQuery = Person::where('church_id', $church->id);

        // Total count
        $totalCount = (clone $statsQuery)->count();

        // Age statistics - calculated at DB level
        $ageStats = [];
        foreach (Person::AGE_CATEGORIES as $key => $category) {
            $count = (clone $statsQuery)->whereNotNull('birth_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) >= ?', [$today, $category['min']])
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) <= ?', [$today, $category['max']])
                ->count();
            $ageStats[$key] = [
                'label' => $category['label'],
                'count' => $count,
                'color' => $category['color'],
            ];
        }
        $ageStats['unknown'] = [
            'label' => 'Невідомо',
            'count' => (clone $statsQuery)->whereNull('birth_date')->count(),
            'color' => '#9ca3af',
        ];

        // Church role statistics - single query with groupBy
        $roleCountsRaw = Person::where('church_id', $church->id)
            ->selectRaw('church_role_id, COUNT(*) as count')
            ->groupBy('church_role_id')
            ->pluck('count', 'church_role_id');

        $roleStats = [];
        foreach ($churchRoles as $role) {
            $roleStats[$role->id] = [
                'label' => $role->name,
                'count' => $roleCountsRaw[$role->id] ?? 0,
                'color' => $role->color,
            ];
        }
        // Count those without role
        $noRoleCount = $roleCountsRaw[null] ?? $roleCountsRaw[''] ?? 0;
        if ($noRoleCount > 0) {
            $roleStats['none'] = [
                'label' => 'Не вказано',
                'count' => $noRoleCount,
                'color' => '#9ca3af',
            ];
        }

        // Ministry stats - single query
        $servingCount = \DB::table('ministry_person')
            ->whereIn('ministry_id', $ministries->pluck('id'))
            ->distinct('person_id')
            ->count('person_id');

        // New this month - single query
        $newThisMonth = (clone $statsQuery)
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->count();

        $stats = [
            'total' => $totalCount,
            'age' => $ageStats,
            'roles' => $roleStats,
            'serving' => $servingCount,
            'new_this_month' => $newThisMonth,
        ];

        // Get shepherds list if feature is enabled
        $shepherds = collect();
        if ($church->shepherds_enabled) {
            $shepherds = Person::where('church_id', $church->id)
                ->where('is_shepherd', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('people.index', compact('people', 'peopleLimited', 'tags', 'ministries', 'churchRoles', 'stats', 'shepherds', 'church'));
    }

    public function create()
    {
        $church = $this->getCurrentChurch();
        $tags = Tag::where('church_id', $church->id)->get();
        $ministries = $church->ministries()->with('positions')->get();
        $churchRoles = \App\Models\ChurchRole::where('church_id', $church->id)->orderBy('sort_order')->get();

        return view('people.create', compact('tags', 'ministries', 'churchRoles'));
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
            'baptism_date' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'joined_date' => 'nullable|date',
            'church_role' => 'nullable|in:member,servant,deacon,presbyter,pastor',
            'church_role_id' => 'nullable|exists:church_roles,id',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'ministries' => 'nullable|array',
        ]);

        $church = $this->getCurrentChurch();

        // Handle photo upload with WebP conversion
        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->imageService->storeProfilePhoto(
                $request->file('photo'),
                'people'
            );
        }

        // Handle empty church_role_id
        if (isset($validated['church_role_id']) && $validated['church_role_id'] === '') {
            $validated['church_role_id'] = null;
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

        // Allow viewing own profile without permission
        $isOwnProfile = auth()->user()->person && auth()->user()->person->id === $person->id;

        $person->load(['tags', 'ministries.positions', 'groups', 'user', 'churchRoleRelation', 'shepherd', 'sheep', 'assignments' => function ($q) {
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
                'date' => $a->event?->date,
                'title' => ($a->event?->title ?? 'Подія') . ' - ' . ($a->position?->name ?? 'Позиція'),
                'subtitle' => $a->event?->ministry?->name ?? 'Служіння',
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

        // For admin inline editing or own profile editing
        $tags = collect();
        $ministries = collect();
        $churchRoles = collect();
        $shepherds = collect();
        $church = $this->getCurrentChurch();
        $canEditProfile = auth()->user()->isAdmin() || $isOwnProfile;

        if ($canEditProfile) {
            $tags = Tag::where('church_id', $church->id)->get();
            $ministries = $church->ministries()->with('positions')->get();
            $churchRoles = \App\Models\ChurchRole::where('church_id', $church->id)->orderBy('sort_order')->get();
            if ($church->shepherds_enabled) {
                $shepherds = Person::where('church_id', $church->id)
                    ->where('is_shepherd', true)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        return view('people.show', compact('person', 'stats', 'activities', 'attendanceChartData', 'tags', 'ministries', 'churchRoles', 'shepherds', 'church', 'isOwnProfile', 'canEditProfile'));
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
        $churchRoles = \App\Models\ChurchRole::where('church_id', $church->id)->orderBy('sort_order')->get();

        $person->load(['tags', 'ministries']);

        return view('people.edit', compact('person', 'tags', 'ministries', 'churchRoles'));
    }

    public function update(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        $user = auth()->user();
        $isOwnProfile = $user->person && $user->person->id === $person->id;
        $isAdmin = $user->isAdmin();

        // If not admin and not own profile, deny access
        if (!$isAdmin && !$isOwnProfile) {
            abort(403, 'У вас немає дозволу редагувати цей профіль.');
        }

        // Different validation rules for admin vs own profile
        if ($isAdmin) {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'telegram_username' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'birth_date' => 'nullable|date',
                'baptism_date' => 'nullable|date',
                'anniversary' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'marital_status' => 'nullable|in:single,married,widowed,divorced',
                'joined_date' => 'nullable|date',
                'church_role' => 'nullable|in:member,servant,deacon,presbyter,pastor',
                'church_role_id' => 'nullable|exists:church_roles,id',
                'notes' => 'nullable|string',
                'photo' => 'nullable|image|max:2048',
                'tags' => 'nullable|array',
                'ministries' => 'nullable|array',
            ]);
        } else {
            // Own profile - limited fields
            $validated = $request->validate([
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'telegram_username' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'birth_date' => 'nullable|date',
                'photo' => 'nullable|image|max:2048',
            ]);
        }

        // Handle photo upload with WebP conversion
        if ($request->hasFile('photo')) {
            // Delete old photo
            $this->imageService->delete($person->photo);
            $validated['photo'] = $this->imageService->storeProfilePhoto(
                $request->file('photo'),
                'people'
            );
        } elseif ($request->input('remove_photo') === '1') {
            // Remove photo if requested
            $this->imageService->delete($person->photo);
            $validated['photo'] = null;
        }

        // Handle empty church_role_id (admin only)
        if ($isAdmin && isset($validated['church_role_id']) && $validated['church_role_id'] === '') {
            $validated['church_role_id'] = null;
        }

        $person->update($validated);

        // Sync tags (admin only)
        if ($isAdmin) {
            $person->tags()->sync($request->tags ?? []);
        }

        // Sync ministries with positions (admin only)
        if ($isAdmin) {
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
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Дані успішно оновлено.']);
        }

        return redirect()->route('people.show', $person)
            ->with('success', 'Дані успішно оновлено.');
    }

    public function destroy(Person $person)
    {
        $this->authorizeChurch($person);

        $person->delete();

        return back()->with('success', 'Людину видалено.');
    }

    public function restore(Person $person)
    {
        $this->authorizeChurch($person);

        $person->restore();

        return redirect()->route('people.show', $person)
            ->with('success', 'Людину відновлено.');
    }

    public function export(Request $request)
    {
        $church = $this->getCurrentChurch();
        $ids = $request->has('ids') ? explode(',', $request->get('ids')) : null;
        $filename = 'people_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PeopleExport($church->id, $ids), $filename);
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

    public function myGiving(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            return redirect()->route('dashboard')
                ->with('error', 'Ваш профіль не знайдено.');
        }

        $person = $user->person;
        $church = $this->getCurrentChurch();

        // Get transactions (donations) for this person
        $query = \App\Models\Transaction::where('church_id', $church->id)
            ->where('person_id', $person->id)
            ->incoming()
            ->completed()
            ->with('category')
            ->orderByDesc('date');

        // Filter by year
        $year = $request->get('year', now()->year);
        if ($year !== 'all') {
            $query->whereYear('date', $year);
        }

        $transactions = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'total_this_year' => \App\Models\Transaction::where('church_id', $church->id)
                ->where('person_id', $person->id)
                ->incoming()
                ->completed()
                ->whereYear('date', now()->year)
                ->sum('amount'),
            'total_this_month' => \App\Models\Transaction::where('church_id', $church->id)
                ->where('person_id', $person->id)
                ->incoming()
                ->completed()
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('amount'),
            'total_lifetime' => \App\Models\Transaction::where('church_id', $church->id)
                ->where('person_id', $person->id)
                ->incoming()
                ->completed()
                ->sum('amount'),
            'donations_count' => \App\Models\Transaction::where('church_id', $church->id)
                ->where('person_id', $person->id)
                ->incoming()
                ->completed()
                ->count(),
        ];

        // Get available years for filter
        $years = \App\Models\Transaction::where('church_id', $church->id)
            ->where('person_id', $person->id)
            ->incoming()
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Monthly breakdown for current year
        $monthlyData = \App\Models\Transaction::where('church_id', $church->id)
            ->where('person_id', $person->id)
            ->incoming()
            ->completed()
            ->whereYear('date', now()->year)
            ->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return view('people.my-giving', compact('person', 'transactions', 'stats', 'years', 'year', 'monthlyData'));
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
            'photo' => 'nullable|image|max:5120',
        ]);

        $person = $user->person;

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            $this->imageService->delete($person->photo);
            $validated['photo'] = $this->imageService->storeProfilePhoto(
                $request->file('photo'),
                'people'
            );
        } elseif ($request->input('remove_photo') === '1') {
            // Remove photo if requested
            $this->imageService->delete($person->photo);
            $validated['photo'] = null;
        } else {
            // Don't update photo field if not provided
            unset($validated['photo']);
        }

        $person->update($validated);

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

        $church = $this->getCurrentChurch();

        if (!config('services.telegram.bot_token')) {
            return response()->json(['error' => 'Telegram бот не налаштовано'], 400);
        }

        $code = \App\Http\Controllers\Api\TelegramController::generateLinkingCode($user->person);

        // Get bot username for link
        try {
            $telegram = \App\Services\TelegramService::make();
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

    public function updateRole(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'church_role_id' => ['nullable', \Illuminate\Validation\Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
        ]);

        if (!$person->user) {
            return response()->json(['message' => 'Користувач не має облікового запису'], 404);
        }

        // Prevent changing own role
        if ($person->user->id === auth()->id()) {
            return response()->json(['message' => 'Не можна змінювати власну роль'], 400);
        }

        $hadNoRole = $person->user->church_role_id === null;
        $newRoleId = $validated['church_role_id'] ?: null;

        $person->user->update(['church_role_id' => $newRoleId]);

        // Send notification if access was granted
        if ($hadNoRole && $newRoleId !== null) {
            $role = \App\Models\ChurchRole::find($newRoleId);
            $person->user->notify(new \App\Notifications\AccessGranted($role->name, $church->name));
        }

        return response()->json(['success' => true]);
    }

    public function updateEmail(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        if (!$person->user) {
            return response()->json(['message' => 'Користувач не має облікового запису'], 404);
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $person->user->id,
        ]);

        $person->user->update(['email' => $validated['email']]);

        return response()->json(['success' => true]);
    }

    public function createAccount(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'church_role_id' => 'required|exists:church_roles,id',
        ]);

        // Verify church role belongs to this church
        $church = $this->getCurrentChurch();
        $churchRole = ChurchRole::findOrFail($validated['church_role_id']);
        if ($churchRole->church_id !== $church->id) {
            return response()->json(['message' => 'Невірна роль'], 400);
        }

        if ($person->user) {
            return response()->json(['message' => 'Користувач вже має обліковий запис'], 400);
        }

        // Generate random password
        $password = Str::random(10);

        // Restore soft-deleted user or create new
        $trashedUser = User::onlyTrashed()->where('email', $validated['email'])->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'church_id' => $church->id,
                'name' => $person->full_name,
                'password' => Hash::make($password),
                'church_role_id' => $validated['church_role_id'],
                'onboarding_completed' => true,
            ]);
            $user = $trashedUser;

            Log::channel('security')->info('Soft-deleted user restored via createAccount', [
                'user_id' => $user->id,
                'email' => $validated['email'],
                'church_id' => $church->id,
            ]);
        } else {
            $user = User::create([
                'church_id' => $church->id,
                'name' => $person->full_name,
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'church_role_id' => $validated['church_role_id'],
                'onboarding_completed' => true,
            ]);
        }

        // Link person to user
        $person->update(['user_id' => $user->id]);

        // Send invitation email
        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token, isInvite: true);

        return response()->json([
            'success' => true,
            'message' => 'Обліковий запис створено. Лист для встановлення пароля надіслано на email.',
            'password' => $password,
            'email_sent' => true,
        ]);
    }

    public function resetPassword(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        if (!$person->user) {
            return response()->json(['message' => 'Користувач не має облікового запису'], 404);
        }

        // Generate new password
        $password = Str::random(10);
        $person->user->update(['password' => Hash::make($password)]);

        // Send password reset email
        $token = Password::createToken($person->user);
        $person->user->sendPasswordResetNotification($token);

        return response()->json([
            'success' => true,
            'password' => $password,
            'email_sent' => true,
        ]);
    }

    public function updateShepherd(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $church = $this->getCurrentChurch();

        if (!$church->shepherds_enabled) {
            return response()->json(['message' => 'Функція опікунів вимкнена'], 400);
        }

        $validated = $request->validate([
            'shepherd_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
        ]);

        // Handle empty string as null
        $shepherdId = $validated['shepherd_id'] ?? null;
        if ($shepherdId === '') {
            $shepherdId = null;
        }

        // Additional validations for shepherd
        if ($shepherdId) {
            $shepherd = Person::find($shepherdId);

            if (!$shepherd->is_shepherd) {
                return response()->json(['message' => 'Ця людина не є опікуном'], 400);
            }

            if ($shepherd->id === $person->id) {
                return response()->json(['message' => 'Людина не може бути своїм опікуном'], 400);
            }
        }

        $person->update(['shepherd_id' => $shepherdId]);

        return response()->json(['success' => true]);
    }

    /**
     * Quick edit view - Excel-like editing interface
     */
    public function quickEdit()
    {
        $church = $this->getCurrentChurch();

        $people = Person::where('church_id', $church->id)
            ->with('ministries')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $ministries = $church->ministries()->orderBy('name')->get();
        $churchRoles = $church->churchRoles()->orderBy('sort_order')->get();

        // Prepare rows data for JavaScript (avoid complex @json in Blade)
        $rows = $people->map(function ($p) {
            return [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'phone' => $p->phone,
                'email' => $p->email,
                'telegram_username' => $p->telegram_username,
                'birth_date' => $p->birth_date?->format('Y-m-d'),
                'gender' => $p->gender,
                'marital_status' => $p->marital_status,
                'membership_status' => $p->membership_status,
                'church_role' => $p->church_role,
                'ministry_id' => $p->ministries->first()?->id,
                'address' => $p->address,
                'first_visit_date' => $p->first_visit_date?->format('Y-m-d'),
                'joined_date' => $p->joined_date?->format('Y-m-d'),
                'baptism_date' => $p->baptism_date?->format('Y-m-d'),
                'anniversary' => $p->anniversary?->format('Y-m-d'),
                'notes' => $p->notes,
                'photo_url' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
                'user_id' => $p->user_id,
                'uploadingPhoto' => false,
                'isDirty' => false,
                'isNew' => false,
                'isDeleted' => false,
                'selected' => false,
            ];
        })->values();

        return view('people.quick-edit', compact('rows', 'ministries', 'churchRoles'));
    }

    /**
     * Save quick edit changes (create, update, delete)
     */
    public function quickSave(Request $request)
    {
        $church = $this->getCurrentChurch();

        $fieldRules = [
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telegram_username' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'membership_status' => 'nullable|in:guest,newcomer,member,active',
            'church_role' => 'nullable|in:member,servant,deacon,presbyter,pastor',
            'ministry_id' => 'nullable|exists:ministries,id',
            'address' => 'nullable|string|max:500',
            'first_visit_date' => 'nullable|date',
            'joined_date' => 'nullable|date',
            'baptism_date' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];

        $validated = $request->validate([
            'create' => 'array',
            ...collect($fieldRules)->mapWithKeys(fn($rule, $key) => ["create.*.{$key}" => $rule])->toArray(),

            'update' => 'array',
            'update.*.id' => 'required|exists:people,id',
            ...collect($fieldRules)->mapWithKeys(fn($rule, $key) => ["update.*.{$key}" => $rule])->toArray(),

            'delete' => 'array',
            'delete.*' => 'exists:people,id',
        ]);

        $stats = ['created' => 0, 'updated' => 0, 'deleted' => 0];
        $created = [];

        // Create new people
        foreach ($validated['create'] ?? [] as $data) {
            if (empty($data['first_name']) && empty($data['last_name'])) {
                continue;
            }

            $ministryId = $data['ministry_id'] ?? null;
            unset($data['ministry_id']);

            // Set default value for required field
            if (empty($data['membership_status'])) {
                $data['membership_status'] = 'guest';
            }

            $person = Person::create([
                ...$data,
                'church_id' => $church->id,
            ]);

            if ($ministryId) {
                $person->ministries()->attach($ministryId);
            }

            $created[] = ['id' => $person->id];
            $stats['created']++;
        }

        // Update existing people
        foreach ($validated['update'] ?? [] as $data) {
            $person = Person::where('church_id', $church->id)->find($data['id']);
            if (!$person) continue;

            $hasMinistryField = array_key_exists('ministry_id', $data);
            $ministryId = $data['ministry_id'] ?? null;
            unset($data['ministry_id'], $data['id']);

            $person->update($data);

            // Update ministry (single ministry for quick edit simplicity)
            if ($hasMinistryField) {
                if ($ministryId) {
                    $person->ministries()->sync([$ministryId]);
                } else {
                    $person->ministries()->detach();
                }
            }

            $stats['updated']++;
        }

        // Delete people
        foreach ($validated['delete'] ?? [] as $id) {
            $person = Person::where('church_id', $church->id)->find($id);
            if ($person) {
                $person->delete();
                $stats['deleted']++;
            }
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'created' => $created,
        ]);
    }

    /**
     * Upload photo for a person (AJAX)
     */
    public function uploadPhoto(Request $request, Person $person)
    {
        $church = $this->getCurrentChurch();

        // Ensure person belongs to current church
        if ($person->church_id !== $church->id) {
            return response()->json(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }

        $request->validate([
            'photo' => 'required|image|max:5120', // 5MB max
        ]);

        // Delete old photo if exists
        if ($person->photo) {
            $this->imageService->delete($person->photo);
        }

        // Store new photo
        $path = $this->imageService->storeProfilePhoto(
            $request->file('photo'),
            'people'
        );

        $person->update(['photo' => $path]);

        return response()->json([
            'success' => true,
            'photo_url' => \Illuminate\Support\Facades\Storage::url($path),
        ]);
    }

    /**
     * Delete photo for a person (AJAX)
     */
    public function deletePhoto(Person $person)
    {
        $church = $this->getCurrentChurch();

        if ($person->church_id !== $church->id) {
            return response()->json(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }

        if ($person->photo) {
            $this->imageService->delete($person->photo);
            $person->update(['photo' => null]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle bulk actions on multiple people
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:ministry,tag,message,delete,grant_access',
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'value' => 'nullable|integer',
            'message' => 'nullable|string|max:1000',
            'church_role_id' => 'nullable|integer|exists:church_roles,id',
        ]);

        $church = $this->getCurrentChurch();
        $ids = $validated['ids'];

        // Verify all people belong to this church
        $people = Person::where('church_id', $church->id)
            ->whereIn('id', $ids)
            ->get();

        if ($people->count() !== count($ids)) {
            return response()->json(['success' => false, 'message' => 'Деякі записи не знайдено']);
        }

        switch ($validated['action']) {
            case 'ministry':
                $ministry = $church->ministries()->find($validated['value']);
                if (!$ministry) {
                    return response()->json(['success' => false, 'message' => 'Служіння не знайдено']);
                }

                foreach ($people as $person) {
                    $person->ministries()->syncWithoutDetaching([$ministry->id]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Додано {$people->count()} людей до служіння «{$ministry->name}»",
                    'reload' => true
                ]);

            case 'tag':
                $tag = $church->tags()->find($validated['value']);
                if (!$tag) {
                    return response()->json(['success' => false, 'message' => 'Тег не знайдено']);
                }

                foreach ($people as $person) {
                    $person->tags()->syncWithoutDetaching([$tag->id]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Додано тег «{$tag->name}» для {$people->count()} людей",
                    'reload' => true
                ]);

            case 'message':
                if (!config('services.telegram.bot_token')) {
                    return response()->json(['success' => false, 'message' => 'Telegram бот не налаштовано']);
                }

                $message = $validated['message'];
                if (empty($message)) {
                    return response()->json(['success' => false, 'message' => 'Повідомлення не може бути порожнім']);
                }

                $telegram = \App\Services\TelegramService::make();
                $sent = 0;

                foreach ($people as $person) {
                    if ($person->telegram_chat_id) {
                        try {
                            $telegram->sendMessage($person->telegram_chat_id, $message);
                            $sent++;
                        } catch (\Exception $e) {
                            \Log::error('Bulk message error', ['person_id' => $person->id, 'error' => $e->getMessage()]);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => "Надіслано {$sent} повідомлень",
                ]);

            case 'grant_access':
                if (!auth()->user()->isAdmin()) {
                    return response()->json(['success' => false, 'message' => 'Недостатньо прав']);
                }

                $churchRoleId = $validated['church_role_id'] ?? null;
                if (!$churchRoleId) {
                    return response()->json(['success' => false, 'message' => 'Оберіть рівень доступу']);
                }

                $churchRole = $church->churchRoles()->find($churchRoleId);
                if (!$churchRole) {
                    return response()->json(['success' => false, 'message' => 'Роль не знайдено']);
                }

                $created = 0;
                $skipped = 0;
                $noEmail = 0;

                foreach ($people as $person) {
                    // Skip if already has user account
                    if ($person->user_id) {
                        $skipped++;
                        continue;
                    }

                    // Skip if no email
                    if (empty($person->email)) {
                        $noEmail++;
                        continue;
                    }

                    // Check if email already exists
                    if (User::where('email', $person->email)->exists()) {
                        $skipped++;
                        continue;
                    }

                    // Create user
                    $password = Str::random(10);
                    $user = User::create([
                        'church_id' => $church->id,
                        'name' => $person->full_name,
                        'email' => $person->email,
                        'password' => Hash::make($password),
                        'church_role_id' => $churchRoleId,
                        'onboarding_completed' => true,
                    ]);

                    // Link person to user
                    $person->update(['user_id' => $user->id]);

                    // Send invitation email
                    $token = Password::createToken($user);
                    $user->sendPasswordResetNotification($token, isInvite: true);

                    $created++;
                }

                $message = "Створено {$created} акаунтів.";
                if ($skipped > 0) {
                    $message .= " Пропущено {$skipped} (вже мають доступ).";
                }
                if ($noEmail > 0) {
                    $message .= " Без email: {$noEmail}.";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'reload' => true
                ]);

            case 'delete':
                foreach ($people as $person) {
                    $person->delete(); // Soft delete
                }

                return response()->json([
                    'success' => true,
                    'message' => "Видалено {$people->count()} людей",
                    'reload' => true
                ]);
        }

        return response()->json(['success' => false, 'message' => 'Невідома дія']);
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
