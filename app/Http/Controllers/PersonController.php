<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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
use App\Services\PersonMergeService;

class PersonController extends Controller
{
    protected ImageService $imageService;
    protected PersonMergeService $mergeService;

    public function __construct(ImageService $imageService, PersonMergeService $mergeService)
    {
        $this->imageService = $imageService;
        $this->mergeService = $mergeService;
    }
    public function index(Request $request)
    {
        if (!auth()->user()->canView('people')) {
            return $this->errorResponse($request, __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();

        $query = Person::where('church_id', $church->id)
            ->where('membership_status', '!=', Person::STATUS_GUEST)
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
        $ministries = $church->ministries()->with('positions')->get();
        $churchRoles = \App\Models\ChurchRole::where('church_id', $church->id)->orderBy('sort_order')->get();

        // Calculate statistics using database aggregation (optimized)
        $today = now();
        $statsQuery = Person::where('church_id', $church->id)->where('membership_status', '!=', Person::STATUS_GUEST);

        // Total count (excludes guests — they belong to groups, not church members list)
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
            ->join('people', 'ministry_person.person_id', '=', 'people.id')
            ->whereNull('people.deleted_at')
            ->whereIn('ministry_id', $ministries->pluck('id'))
            ->distinct('person_id')
            ->count('person_id');

        // New this month - single query
        $newThisMonth = (clone $statsQuery)
            ->whereRaw('MONTH(COALESCE(first_visit_date, people.created_at)) = ?', [$today->month])
            ->whereRaw('YEAR(COALESCE(first_visit_date, people.created_at)) = ?', [$today->year])
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

    public function create(Request $request)
    {
        if (!auth()->user()->canCreate('people')) {
            return $this->errorResponse($request, 'У вас немає прав для створення записів.');
        }

        $church = $this->getCurrentChurch();
        $tags = Tag::where('church_id', $church->id)->get();
        $ministries = $church->ministries()->with('positions')->get();
        $churchRoles = \App\Models\ChurchRole::where('church_id', $church->id)->orderBy('sort_order')->get();

        return view('people.create', compact('tags', 'ministries', 'churchRoles'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canCreate('people')) {
            return $this->errorResponse($request, 'У вас немає прав для створення записів.');
        }

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
            'membership_status' => 'nullable|in:' . implode(',', array_keys(\App\Models\Person::MEMBERSHIP_STATUSES)),
            'church_role' => 'nullable|in:member,servant,deacon,presbyter,pastor',
            'church_role_id' => ['nullable', \Illuminate\Validation\Rule::exists('church_roles', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'notes' => 'nullable|string|max:5000',
            'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'photo_full' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
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
            // Store full original photo
            if ($request->hasFile('photo_full')) {
                $validated['photo_full'] = $this->imageService->store(
                    $request->file('photo_full'),
                    'people',
                    1200
                );
            } else {
                unset($validated['photo_full']);
            }
        } else {
            unset($validated['photo_full']);
        }

        // Handle empty church_role_id
        if (isset($validated['church_role_id']) && $validated['church_role_id'] === '') {
            $validated['church_role_id'] = null;
        }

        $validated['church_id'] = $church->id;

        // Check for duplicate by email in same church
        if (!empty($validated['email'])) {
            $existingPerson = Person::where('email', $validated['email'])
                ->where('church_id', $church->id)
                ->first();

            if ($existingPerson) {
                return $this->successResponse($request, 'Людина з цим email вже існує. Перенаправляю на її профіль.', 'people.show', [$existingPerson]);
            }
        }

        // Check for duplicate by phone in same church
        if (!empty($validated['phone'])) {
            $existingByPhone = Person::findByPhoneInChurch($validated['phone'], $church->id, false);

            if ($existingByPhone) {
                return $this->successResponse($request, 'Людина з цим номером телефону вже існує. Перенаправляю на її профіль.', 'people.show', [$existingByPhone]);
            }
        }

        // Check for duplicate by name in same church (unless explicitly forced)
        if (!$request->boolean('force_duplicate')) {
            $existingByName = Person::where('church_id', $church->id)
                ->where('first_name', $validated['first_name'])
                ->where('last_name', $validated['last_name'])
                ->first();

            if ($existingByName) {
                $info = $existingByName->first_name . ' ' . $existingByName->last_name;
                if ($existingByName->phone) $info .= ', тел: ' . $existingByName->phone;
                if ($existingByName->email) $info .= ', email: ' . $existingByName->email;

                if ($request->wantsJson()) {
                    return response()->json([
                        'duplicate' => true,
                        'message' => __('app.duplicate_person_warning'),
                        'existing_person' => [
                            'id' => $existingByName->id,
                            'name' => $existingByName->full_name,
                            'info' => $info,
                            'url' => route('people.show', $existingByName),
                            'photo_url' => $existingByName->photo ? \Illuminate\Support\Facades\Storage::url($existingByName->photo) : null,
                        ],
                    ], 409);
                }
                return redirect()->route('people.show', $existingByName)
                    ->with('warning', 'Людина з таким іменем вже існує.');
            }
        }

        $person = Person::create($validated);

        // Attach tags (only tags belonging to this church)
        if ($request->has('tags')) {
            $validTagIds = \App\Models\Tag::where('church_id', $church->id)
                ->whereIn('id', $request->tags)
                ->pluck('id')
                ->toArray();
            $person->tags()->sync($validTagIds);
        }

        // Attach ministries with positions (only ministries belonging to this church)
        if ($request->has('ministries')) {
            $validMinistryIds = \App\Models\Ministry::where('church_id', $church->id)
                ->pluck('id')
                ->toArray();
            foreach ($request->ministries as $ministryId => $data) {
                if (!empty($data['selected']) && in_array((int) $ministryId, $validMinistryIds)) {
                    $person->ministries()->attach($ministryId, [
                        'position_ids' => json_encode($data['positions'] ?? []),
                    ]);
                }
            }
        }

        return $this->successResponse($request, 'Людину успішно додано.', 'people.show', [$person]);
    }

    public function show(Person $person)
    {
        $this->authorizeChurch($person);
        $this->authorize('view', $person);

        // Allow viewing own profile without permission
        $isOwnProfile = auth()->user()->person && auth()->user()->person->id === $person->id;

        $person->load(['tags', 'ministries.positions', 'groups', 'user', 'churchRoleRelation', 'shepherd', 'sheep.churchRoleRelation', 'assignments' => function ($q) {
            $q->whereHas('event', fn($eq) => $eq->where('date', '>=', now()->subMonths(3)))
              ->with(['event.ministry', 'position']);
        }]);

        // Sort assignments by event date descending (not by created_at)
        $person->setRelation('assignments', $person->assignments->sortByDesc(fn($a) => $a->event?->date)->values());

        // Stats
        $stats = [
            'services_this_month' => $person->assignments()
                ->where('status', 'confirmed')
                ->whereHas('event', fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year))
                ->count(),
            'services_total' => $person->assignments()
                ->where('status', 'confirmed')
                ->count(),
            'attendance_3_months' => $person->attendanceRecords()
                ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subMonths(3)))
                ->where('present', true)
                ->count(),
            'attendance_rate' => $this->calculateAttendanceRate($person),
            'last_attended' => $person->attendanceRecords()
                ->whereHas('attendance')
                ->where('present', true)
                ->with('attendance')
                ->get()
                ->sortByDesc(fn($r) => $r->attendance?->date)
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
                'date' => $r->attendance?->date,
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
            ->filter(fn($a) => $a['date'] !== null)
            ->sortByDesc('date')
            ->take(20)
            ->values();

        // Attendance chart data (last 12 weeks) — uses already-loaded records
        $allAttendanceRecords = $person->attendanceRecords()
            ->whereHas('attendance', fn($q) => $q->where('date', '>=', now()->subWeeks(12)->startOfWeek()))
            ->with('attendance')
            ->where('present', true)
            ->get();

        $attendanceChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $attendanceChartData[] = [
                'week' => $weekStart->format('d.m'),
                'count' => $allAttendanceRecords->filter(
                    fn($r) => $r->attendance?->date?->between($weekStart, $weekEnd)
                )->count(),
            ];
        }

        // For admin inline editing or own profile editing
        $tags = collect();
        $ministries = collect();
        $churchRoles = collect();
        $shepherds = collect();
        $church = $this->getCurrentChurch();
        $canEditProfile = auth()->user()->isAdmin() || auth()->user()->canEdit('people') || $isOwnProfile;

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

    public function edit(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        $user = auth()->user();
        $isOwnProfile = $user->person && $user->person->id === $person->id;

        if (!$isOwnProfile && !$user->canEdit('people')) {
            return $this->errorResponse($request, 'У вас немає прав для редагування записів.');
        }

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
        $canEditPeople = $user->canEdit('people');

        // If not own profile and no edit permission, deny access
        if (!$isOwnProfile && !$canEditPeople) {
            abort(403, 'У вас немає дозволу редагувати цей профіль.');
        }

        // Different validation rules for users with edit permission vs own profile
        if ($canEditPeople) {
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
                'membership_status' => 'nullable|in:' . implode(',', array_keys(\App\Models\Person::MEMBERSHIP_STATUSES)),
                'church_role' => 'nullable|in:member,servant,deacon,presbyter,pastor',
                'church_role_id' => ['nullable', \Illuminate\Validation\Rule::exists('church_roles', 'id')->where('church_id', $this->getCurrentChurch()->id)],
                'notes' => 'nullable|string|max:5000',
                'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
                'photo_full' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
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
                'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
                'photo_full' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
            ]);
        }

        // Handle photo upload with WebP conversion
        if ($request->hasFile('photo')) {
            try {
                // Delete old photos
                $this->imageService->delete($person->photo);
                $this->imageService->delete($person->photo_full);
                $validated['photo'] = $this->imageService->storeProfilePhoto(
                    $request->file('photo'),
                    'people'
                );
                // Store full original photo
                if ($request->hasFile('photo_full')) {
                    $validated['photo_full'] = $this->imageService->store(
                        $request->file('photo_full'),
                        'people',
                        1200
                    );
                } else {
                    unset($validated['photo_full']);
                }
            } catch (\Exception $e) {
                \Log::error('Photo upload failed in update', [
                    'person_id' => $person->id,
                    'file_name' => $request->file('photo')->getClientOriginalName(),
                    'file_size' => $request->file('photo')->getSize(),
                    'file_mime' => $request->file('photo')->getMimeType(),
                    'error' => $e->getMessage(),
                ]);
                unset($validated['photo_full']);
            }
        } elseif ($request->input('remove_photo') === '1') {
            // Remove photos if requested
            $this->imageService->delete($person->photo);
            $this->imageService->delete($person->photo_full);
            $validated['photo'] = null;
            $validated['photo_full'] = null;
        } else {
            unset($validated['photo_full']);
        }

        // Handle empty church_role_id (users with edit permission only)
        if ($canEditPeople && isset($validated['church_role_id']) && $validated['church_role_id'] === '') {
            $validated['church_role_id'] = null;
        }

        $person->update($validated);

        // Sync tags (users with edit permission only, validated by church_id)
        if ($canEditPeople) {
            $churchId = $this->getCurrentChurch()->id;
            $validTagIds = \App\Models\Tag::where('church_id', $churchId)
                ->whereIn('id', $request->tags ?? [])
                ->pluck('id')
                ->toArray();
            $person->tags()->sync($validTagIds);
        }

        // Sync ministries with positions (users with edit permission only, validated by church_id)
        if ($canEditPeople) {
            $churchId = $churchId ?? $this->getCurrentChurch()->id;
            $validMinistryIds = \App\Models\Ministry::where('church_id', $churchId)
                ->pluck('id')
                ->toArray();
            $person->ministries()->detach();
            if ($request->has('ministries')) {
                foreach ($request->ministries as $ministryId => $data) {
                    if (!empty($data['selected']) && in_array((int) $ministryId, $validMinistryIds)) {
                        $person->ministries()->attach($ministryId, [
                            'position_ids' => json_encode($data['positions'] ?? []),
                        ]);
                    }
                }
            }
        }

        return $this->successResponse($request, 'Дані успішно оновлено.', 'people.show', [$person]);
    }

    public function destroy(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->canDelete('people')) {
            return $this->errorResponse($request, 'У вас немає прав для видалення записів.');
        }

        $person->delete();

        return $this->successResponse($request, 'Людину видалено.', 'people.index');
    }

    public function restore(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        if (!auth()->user()->canDelete('people')) {
            return $this->errorResponse($request, 'У вас немає прав для відновлення записів.');
        }

        $person->restore();

        return $this->successResponse($request, 'Людину відновлено.', 'people.show', [$person]);
    }

    public function export(Request $request)
    {
        if (!auth()->user()->canView('people')) {
            abort(403, 'Недостатньо прав для експорту.');
        }

        $church = $this->getCurrentChurch();
        $ids = $request->has('ids') ? explode(',', $request->get('ids')) : null;
        $filename = 'people_' . now()->format('Y-m-d') . '.xlsx';

        // Log export action
        $this->logAuditAction('exported', 'Person', null, 'Експорт списку людей', [
            'count' => $ids ? count($ids) : 'all',
            'filename' => $filename,
        ]);

        return Excel::download(new PeopleExport($church->id, $ids), $filename);
    }

    public function import(Request $request)
    {
        if (!auth()->user()->canCreate('people')) {
            abort(403, 'Недостатньо прав для імпорту.');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $church = $this->getCurrentChurch();

        try {
            Excel::import(new PeopleImport($church->id), $request->file('file'));

            // Log import action
            $this->logAuditAction('imported', 'Person', null, 'Імпорт списку людей', [
                'filename' => $request->file('file')->getClientOriginalName(),
            ]);

            return $this->successResponse($request, 'Людей успішно імпортовано.');
        } catch (\Exception $e) {
            return $this->errorResponse($request, 'Помилка імпорту: ' . $e->getMessage());
        }
    }

    public function myProfile(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            return redirect()->route('dashboard')->with('error', __('app.profile_not_found'));
        }

        $person = $user->person->load(['tags', 'ministries', 'unavailableDates' => function ($q) {
            $q->where('date_to', '>=', now())->orderBy('date_from');
        }]);

        $upcomingAssignments = $person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
            ->get()
            ->sortBy(fn($a) => $a->event?->date)
            ->values();

        return view('people.my-profile', compact('person', 'upcomingAssignments'));
    }

    public function myGiving(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            return redirect()->route('dashboard')->with('error', __('app.profile_not_found'));
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
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0,
            'total_this_month' => \App\Models\Transaction::where('church_id', $church->id)
                ->where('person_id', $person->id)
                ->incoming()
                ->completed()
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0,
            'total_lifetime' => \App\Models\Transaction::where('church_id', $church->id)
                ->where('person_id', $person->id)
                ->incoming()
                ->completed()
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0,
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
            ->selectRaw('MONTH(date) as month, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return view('people.my-giving', compact('person', 'transactions', 'stats', 'years', 'year', 'monthlyData'));
    }

    public function updateMyPhoto(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            abort(404);
        }

        $person = $user->person;

        if ($request->input('remove') === '1') {
            $this->imageService->delete($person->photo);
            $this->imageService->delete($person->photo_full);
            $person->update(['photo' => null, 'photo_full' => null]);
            return response()->json(['photo_url' => null]);
        }

        $request->validate([
            'photo' => 'required|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
            'photo_full' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120',
        ]);

        $this->imageService->delete($person->photo);
        $this->imageService->delete($person->photo_full);
        $path = $this->imageService->storeProfilePhoto($request->file('photo'), 'people');

        $updateData = ['photo' => $path];
        if ($request->hasFile('photo_full')) {
            $fullPath = $this->imageService->store($request->file('photo_full'), 'people', 1200);
            $updateData['photo_full'] = $fullPath;
        }
        $person->update($updateData);

        return response()->json(['photo_url' => Storage::url($path)]);
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

        $person = $user->person;
        $person->update($validated);

        return $this->successResponse($request, 'Профіль оновлено.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => [__('app.current_password_incorrect')]]
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => __('app.password_changed')]);
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

        return $this->successResponse($request, 'Дати недоступності додано.');
    }

    public function removeUnavailableDate(Request $request, UnavailableDate $unavailableDate)
    {
        $user = auth()->user();

        if (!$user->person || $unavailableDate->person_id !== $user->person->id) {
            abort(403);
        }

        $unavailableDate->delete();

        return $this->successResponse($request, 'Дати видалено.');
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

    public function unlinkTelegram(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            return $this->errorResponse($request, 'Профіль не знайдено');
        }

        $user->person->update(['telegram_chat_id' => null]);

        // Log telegram unlink
        $this->logAuditAction('telegram_unlinked', 'Person', $user->person->id, $user->person->full_name);

        return $this->successResponse($request, 'Telegram від\'єднано');
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'design_theme' => 'required|string|in:,modern,glass,corporate,ocean,sunset',
        ]);

        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['design_theme'] = $validated['design_theme'];
        $user->update(['settings' => $settings]);

        return response()->json(['success' => true]);
    }

    public function updateMenuPosition(Request $request)
    {
        $validated = $request->validate([
            'menu_position' => 'required|string|in:left,right,top,bottom',
        ]);

        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['menu_position'] = $validated['menu_position'];
        $user->update(['settings' => $settings]);

        return response()->json(['success' => true]);
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

        $oldRoleId = $person->user->church_role_id;
        $person->user->update(['church_role_id' => $newRoleId]);

        // Sync pivot for this church
        \Illuminate\Support\Facades\DB::table('church_user')
            ->where('user_id', $person->user->id)
            ->where('church_id', $church->id)
            ->update([
                'church_role_id' => $newRoleId,
                'updated_at' => now(),
            ]);

        // Log role change
        $oldRoleName = $oldRoleId ? \App\Models\ChurchRole::find($oldRoleId)?->name : 'Без ролі';
        $newRoleName = $newRoleId ? \App\Models\ChurchRole::find($newRoleId)?->name : 'Без ролі';
        $this->logAuditAction('role_changed', 'User', $person->user->id, $person->full_name, [
            'new_role' => $newRoleName,
        ], [
            'old_role' => $oldRoleName,
        ]);

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

        $oldEmail = $person->user->email;
        $person->user->update(['email' => $validated['email']]);

        // Log email change
        $this->logAuditAction('email_changed', 'User', $person->user->id, $person->full_name, [
            'new_email' => $validated['email'],
        ], [
            'old_email' => $oldEmail,
        ]);

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
        $churchRole = ChurchRole::where('church_id', $church->id)->findOrFail($validated['church_role_id']);

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
                // Don't assign church_role_id yet - set to pending approval instead
                'requested_church_role_id' => $validated['church_role_id'],
                'servant_approval_status' => 'pending',
                'onboarding_completed' => true,
            ]);
            $user = $trashedUser;

            Log::channel('security')->info('Soft-deleted user restored via createAccount (pending approval)', [
                'user_id' => $user->id,
                'email' => $validated['email'],
                'church_id' => $church->id,
                'requested_role_id' => $validated['church_role_id'],
            ]);
        } else {
            $user = User::create([
                'church_id' => $church->id,
                'name' => $person->full_name,
                'email' => $validated['email'],
                'password' => Hash::make($password),
                // Don't assign church_role_id yet - set to pending approval instead
                'requested_church_role_id' => $validated['church_role_id'],
                'servant_approval_status' => 'pending',
                'onboarding_completed' => true,
            ]);
        }

        // Link person to user
        $person->update(['user_id' => $user->id]);

        // Create pivot record
        \Illuminate\Support\Facades\DB::table('church_user')->updateOrInsert(
            ['user_id' => $user->id, 'church_id' => $church->id],
            [
                'church_role_id' => $validated['church_role_id'],
                'person_id' => $person->id,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Send invitation email
        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token, isInvite: true);

        return response()->json([
            'success' => true,
            'message' => 'Обліковий запис створено. Лист для встановлення пароля надіслано на email.',
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

        // Log password reset
        $this->logAuditAction('password_reset', 'User', $person->user->id, $person->full_name);

        return response()->json([
            'success' => true,
            'message' => 'Пароль скинуто. Лист надіслано на email.',
            'email_sent' => true,
        ]);
    }

    public function updateShepherd(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        $currentUser = auth()->user();

        // Allow: admins, users with edit permission, or shepherds assigning to themselves
        $isShepherdSelfAssign = $currentUser->person?->is_shepherd
            && $request->input('shepherd_id') == $currentUser->person->id;

        if (!$currentUser->isAdmin() && !$currentUser->canEdit('people') && !$isShepherdSelfAssign) {
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
            $church = $this->getCurrentChurch();
            $shepherd = Person::where('church_id', $church->id)->find($shepherdId);

            if (!$shepherd) {
                return response()->json(['message' => 'Опікуна не знайдено'], 404);
            }

            if (!$shepherd->is_shepherd) {
                return response()->json(['message' => 'Ця людина не є опікуном'], 400);
            }

            if ($shepherd->id === $person->id) {
                return response()->json(['message' => 'Людина не може бути своїм опікуном'], 400);
            }
        }

        $oldShepherdId = $person->shepherd_id;
        $person->update(['shepherd_id' => $shepherdId]);

        // Log shepherd change
        $oldShepherdName = $oldShepherdId ? Person::find($oldShepherdId)?->full_name : null;
        $newShepherdName = $shepherdId ? Person::find($shepherdId)?->full_name : null;
        $this->logAuditAction('shepherd_assigned', 'Person', $person->id, $person->full_name, [
            'shepherd' => $newShepherdName ?? 'Знято',
        ], [
            'shepherd' => $oldShepherdName ?? 'Не призначено',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Quick edit view - Excel-like editing interface
     */
    public function quickEdit(Request $request)
    {
        if (!auth()->user()->canView('people')) {
            return $this->errorResponse($request, __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();

        $people = Person::where('church_id', $church->id)
            ->where('membership_status', '!=', Person::STATUS_GUEST)
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
        if (!auth()->user()->canEdit('people')) {
            abort(403);
        }

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
            'membership_status' => 'nullable|in:guest,newcomer,member,servant,leader,leadership',
            'church_role' => 'nullable|in:member,servant,deacon,presbyter,pastor',
            'ministry_id' => ['nullable', \Illuminate\Validation\Rule::exists('ministries', 'id')->where('church_id', $church->id)],
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

            $isDuplicate = false;

            // Check for duplicate by email in same church
            $email = $data['email'] ?? null;
            if ($email) {
                $existing = Person::where('email', $email)
                    ->where('church_id', $church->id)
                    ->first();
                if ($existing) {
                    $person = $existing;
                    $isDuplicate = true;
                    $stats['updated']++;
                    goto skip_create;
                }
            }

            // Check for duplicate by phone in same church
            $phone = $data['phone'] ?? null;
            if ($phone) {
                $existingByPhone = Person::findByPhoneInChurch($phone, $church->id, false);
                if ($existingByPhone) {
                    $person = $existingByPhone;
                    $isDuplicate = true;
                    $stats['updated']++;
                    goto skip_create;
                }
            }

            // Check for duplicate by name in same church
            $firstName = $data['first_name'] ?? null;
            $lastName = $data['last_name'] ?? null;
            if ($firstName && $lastName) {
                $existingByName = Person::where('church_id', $church->id)
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();
                if ($existingByName) {
                    $person = $existingByName;
                    $isDuplicate = true;
                    $stats['updated']++;
                    goto skip_create;
                }
            }

            $person = Person::create([
                ...$data,
                'church_id' => $church->id,
            ]);

            skip_create:

            if ($ministryId) {
                $person->ministries()->syncWithoutDetaching([$ministryId]);
            }

            $created[] = ['id' => $person->id];
            if (!$isDuplicate) {
                $stats['created']++;
            }
        }

        // Update existing people
        foreach ($validated['update'] ?? [] as $data) {
            $person = Person::where('church_id', $church->id)->find($data['id']);
            if (!$person) continue;

            $hasMinistryField = array_key_exists('ministry_id', $data);
            $ministryId = $data['ministry_id'] ?? null;
            unset($data['ministry_id'], $data['id']);

            $person->update($data);

            // Update ministry (add/remove single ministry for quick edit)
            if ($hasMinistryField) {
                if ($ministryId) {
                    $person->ministries()->syncWithoutDetaching([$ministryId]);
                }
                // Note: empty ministry_id in quick edit = no change (don't detach all)
            }

            $stats['updated']++;
        }

        // Delete people (requires delete permission)
        if (!empty($validated['delete']) && auth()->user()->canDelete('people')) {
            foreach ($validated['delete'] as $id) {
                $person = Person::where('church_id', $church->id)->find($id);
                if ($person) {
                    $person->delete();
                    $stats['deleted']++;
                }
            }
        }

        // Log quick edit action
        if ($stats['created'] > 0 || $stats['updated'] > 0 || $stats['deleted'] > 0) {
            $this->logAuditAction('quick_edit_saved', 'Person', null, 'Швидке редагування', [
                'created' => $stats['created'],
                'updated' => $stats['updated'],
                'deleted' => $stats['deleted'],
            ]);
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

        // Check edit permission (own profile or canEdit('people'))
        if (!auth()->user()->can('update', $person)) {
            return response()->json(['success' => false, 'message' => 'Недостатньо прав'], 403);
        }

        $request->validate([
            'photo' => 'required|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:5120', // 5MB max
        ]);

        try {
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
        } catch (\Exception $e) {
            \Log::error('Photo upload failed', [
                'person_id' => $person->id,
                'file_name' => $request->file('photo')->getClientOriginalName(),
                'file_size' => $request->file('photo')->getSize(),
                'file_mime' => $request->file('photo')->getMimeType(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не вдалося обробити фото. Спробуйте інший файл (JPG/PNG).',
            ], 422);
        }
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

        // Check edit permission (own profile or canEdit('people'))
        if (!auth()->user()->can('update', $person)) {
            return response()->json(['success' => false, 'message' => 'Недостатньо прав'], 403);
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

                // Log bulk ministry assignment
                $this->logAuditAction('bulk_ministry_assigned', 'Ministry', $ministry->id, $ministry->name, [
                    'people_count' => $people->count(),
                    'person_ids' => $ids,
                ]);

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

                // Log bulk tag assignment
                $this->logAuditAction('bulk_tag_assigned', 'Tag', $tag->id, $tag->name, [
                    'people_count' => $people->count(),
                    'person_ids' => $ids,
                ]);

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

                // Log bulk message send
                $this->logAuditAction('bulk_message_sent', 'Person', null, 'Масова розсилка', [
                    'sent_count' => $sent,
                    'total_selected' => count($ids),
                ]);

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

                    // Create user with pending approval
                    $password = Str::random(10);
                    $user = User::create([
                        'church_id' => $church->id,
                        'name' => $person->full_name,
                        'email' => $person->email,
                        'password' => Hash::make($password),
                        // Don't assign role yet - set to pending approval
                        'requested_church_role_id' => $churchRoleId,
                        'servant_approval_status' => 'pending',
                        'onboarding_completed' => true,
                    ]);

                    // Link person to user
                    $person->update(['user_id' => $user->id]);

                    // Create pivot record
                    \Illuminate\Support\Facades\DB::table('church_user')->updateOrInsert(
                        ['user_id' => $user->id, 'church_id' => $church->id],
                        [
                            'church_role_id' => $churchRoleId,
                            'person_id' => $person->id,
                            'joined_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

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

                // Log bulk access grant
                if ($created > 0) {
                    $this->logAuditAction('bulk_access_granted', 'User', null, 'Масове надання доступу', [
                        'created' => $created,
                        'skipped' => $skipped,
                        'no_email' => $noEmail,
                        'role' => $churchRole->name,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'reload' => true
                ]);

            case 'delete':
                if (!auth()->user()->canDelete('people')) {
                    return response()->json(['success' => false, 'message' => 'Недостатньо прав для видалення']);
                }

                $deletedNames = $people->pluck('full_name')->toArray();
                foreach ($people as $person) {
                    $person->delete(); // Soft delete
                }

                // Log bulk delete
                $this->logAuditAction('bulk_deleted', 'Person', null, 'Масове видалення', [
                    'count' => count($deletedNames),
                    'names' => array_slice($deletedNames, 0, 10), // Store first 10 names
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Видалено {$people->count()} людей",
                    'reload' => true
                ]);
        }

        return response()->json(['success' => false, 'message' => 'Невідома дія']);
    }

    /**
     * Find potential duplicate people (AJAX, admin only)
     */
    public function findDuplicates()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $church = $this->getCurrentChurch();
        $pairs = $this->mergeService->findDuplicates($church->id);

        // Eager load relationships for all people in pairs
        $personIds = $pairs->flatMap(fn($p) => [$p['personA']->id, $p['personB']->id])->unique();
        if ($personIds->isNotEmpty()) {
            $loaded = Person::with(['ministries', 'tags'])->whereIn('id', $personIds)->get()->keyBy('id');
            $pairs = $pairs->map(function ($pair) use ($loaded) {
                return [
                    'personA' => $loaded[$pair['personA']->id] ?? $pair['personA'],
                    'personB' => $loaded[$pair['personB']->id] ?? $pair['personB'],
                    'reasons' => $pair['reasons'],
                ];
            });
        }

        $result = $pairs->map(function ($pair) {
            return [
                'personA' => $this->formatPersonForDuplicates($pair['personA']),
                'personB' => $this->formatPersonForDuplicates($pair['personB']),
                'reasons' => $pair['reasons'],
            ];
        })->values();

        return response()->json(['pairs' => $result]);
    }

    /**
     * Merge two people (AJAX, admin only)
     */
    public function mergePeople(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $validated = $request->validate([
            'primary_id' => 'required|integer|exists:people,id',
            'secondary_id' => 'required|integer|exists:people,id|different:primary_id',
            'field_selections' => 'nullable|array',
            'field_selections.*' => 'in:A,B',
        ]);

        $church = $this->getCurrentChurch();

        $primary = Person::where('church_id', $church->id)->findOrFail($validated['primary_id']);
        $secondary = Person::where('church_id', $church->id)->findOrFail($validated['secondary_id']);

        if (!empty($validated['field_selections'])) {
            $this->mergeService->mergeWithFieldSelection($primary, $secondary, $validated['field_selections']);
        } else {
            $this->mergeService->merge($primary, $secondary);
        }

        $this->logAuditAction('people_merged', 'Person', $primary->id, $primary->full_name, [
            'merged_from_id' => $secondary->id,
            'merged_from_name' => $secondary->full_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('app.duplicates_merged_success'),
        ]);
    }

    private function formatPersonForDuplicates(Person $person): array
    {
        return [
            'id' => $person->id,
            'full_name' => $person->full_name,
            'first_name' => $person->first_name,
            'last_name' => $person->last_name,
            'phone' => $person->phone,
            'email' => $person->email,
            'photo_url' => $person->photo ? \Illuminate\Support\Facades\Storage::url($person->photo) : null,
            'birth_date' => $person->birth_date?->format('d.m.Y'),
            'gender' => $person->gender,
            'membership_status' => $person->membership_status,
            'telegram_username' => $person->telegram_username,
            'telegram_chat_id' => $person->telegram_chat_id,
            'address' => $person->address,
            'notes' => $person->notes,
            'has_user' => (bool) $person->user_id,
            'ministries' => $person->ministries->pluck('name')->toArray(),
            'tags' => $person->tags->pluck('name')->toArray(),
            'created_at' => $person->created_at?->format('d.m.Y'),
            'url' => route('people.show', $person),
        ];
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
