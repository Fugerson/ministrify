<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\EventSong;
use App\Models\BudgetItem;
use App\Models\Ministry;
use App\Models\MinistryBudget;
use App\Models\MinistryRole;
use App\Models\Person;
use App\Models\Resource;
use App\Models\Song;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\WorshipRole;
use App\Helpers\CurrencyHelper;
use App\Rules\BelongsToChurch;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MinistryController extends Controller
{
    public function index()
    {
        if (!auth()->user()->canView('ministries')) {
            return redirect()->route('dashboard')->with('error', __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();
        $user = auth()->user();

        // Show ALL ministries — locked ones display with a lock icon in the view
        $ministries = Ministry::where('church_id', $church->id)
            ->with(['leader', 'members', 'positions'])
            ->orderBy('name')
            ->get();

        // People for create modal (leader select)
        $people = $user->canCreate('ministries')
            ? Person::where('church_id', $church->id)->orderBy('last_name')->get()
            : collect();

        return view('ministries.index', compact('ministries', 'people'));
    }

    public function create()
    {
        $this->authorize('create', Ministry::class);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->orderBy('last_name')
            ->get();

        return view('ministries.create', compact('people'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Ministry::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'monthly_budget' => 'nullable|numeric|min:0',
        ]);

        $church = $this->getCurrentChurch();
        $validated['church_id'] = $church->id;
        $ministry = Ministry::create($validated);

        // Add leader as member
        if ($ministry->leader_id && !$ministry->members()->where('people.id', $ministry->leader_id)->exists()) {
            $ministry->members()->attach($ministry->leader_id, [
                'role' => 'leader',
                'joined_at' => now(),
            ]);
        }

        // Create default positions if provided
        if ($request->has('positions')) {
            foreach ($request->positions as $index => $positionName) {
                if (!empty($positionName)) {
                    $ministry->positions()->create([
                        'name' => $positionName,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        \App\Models\Church::clearMinistriesCache($church->id);

        return $this->successResponse($request, 'Команду створено!', 'ministries.show', [$ministry]);
    }

    public function show(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('view-ministry', $ministry);

        // Check private access
        if (!$ministry->canAccess()) {
            abort(403, 'Ця команда приватна. Доступ тільки для учасників.');
        }

        $church = $this->getCurrentChurch();

        $ministry->load([
            'leader',
            'positions',
            'members',
            'events' => fn($q) => $q->orderBy('date')->orderBy('time')->with(['ministry.positions', 'assignments']),
            'transactions' => fn($q) => $q->completed()->with(['category', 'attachments'])->orderByDesc('date'),
            'goals' => fn($q) => $q->with(['tasks.assignee', 'creator'])->orderByDesc('created_at'),
        ]);

        // Default tab: 'goals' for managers, 'schedule' for others
        $defaultTab = Gate::allows('contribute-ministry', $ministry) ? 'goals' : 'schedule';
        $tab = request('tab', $defaultTab);

        // Get boards for task creation
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', false)
            ->get();

        // Get available people for adding members (always load for client-side tabs)
        $memberIds = $ministry->members->pluck('id')->toArray();
        $availablePeople = Person::where('church_id', $church->id)
            ->whereNotIn('id', $memberIds)
            ->orderBy('last_name')
            ->get();

        // Get registered users (people with user accounts) for access settings
        $registeredUsers = Person::where('church_id', $church->id)
            ->whereHas('user')
            ->orderBy('last_name')
            ->get();

        // Resources: folder navigation
        $folderId = request('folder');
        $currentFolder = null;
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = Resource::where('id', $folderId)
                ->where('church_id', $church->id)
                ->where('ministry_id', $ministry->id)
                ->where('type', 'folder')
                ->first();

            if ($currentFolder) {
                $breadcrumbs = $currentFolder->getBreadcrumbs();
            } else {
                $folderId = null;
            }
        }

        $resources = Resource::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('parent_id', $currentFolder?->id)
            ->with('creator')
            ->orderByRaw("type = 'folder' DESC")
            ->orderBy('name')
            ->get();

        // Goals stats
        $goalsStats = [
            'total_goals' => $ministry->goals()->count(),
            'active_goals' => $ministry->goals()->active()->count(),
            'completed_goals' => $ministry->goals()->completed()->count(),
            'total_tasks' => $ministry->tasks()->count(),
            'completed_tasks' => $ministry->tasks()->done()->count(),
            'overdue_tasks' => $ministry->tasks()->overdue()->count(),
        ];

        // Load songs for worship ministries
        $songs = [];
        $songBoardTags = [];
        if ($ministry->is_worship_ministry) {
            $songs = Song::where('church_id', $church->id)
                ->orderBy('title')
                ->get();
            $songBoardTags = $ministry->settings['song_board_tags'] ?? [];
        }

        // Load schedule events: worship/sunday-part ministries see sunday services + own events; others see only own events
        $scheduleEventsQuery = Event::where('church_id', $church->id)
            ->where(function ($q) use ($ministry) {
                if ($ministry->is_worship_ministry || $ministry->is_sunday_service_part) {
                    $q->where('service_type', 'sunday_service')
                      ->orWhere('ministry_id', $ministry->id);
                } else {
                    $q->where('ministry_id', $ministry->id);
                }
            })
            ->orderBy('date')
            ->orderBy('time');

        if ($ministry->is_worship_ministry) {
            $scheduleEventsQuery->withCount(['songs as songs_count', 'ministryTeams as team_count' => function ($q) use ($ministry) {
                $q->where('ministry_id', $ministry->id);
            }]);
        } else {
            $scheduleEventsQuery->withCount(['ministryTeams as team_count' => function ($q) use ($ministry) {
                $q->where('ministry_id', $ministry->id);
            }]);
        }

        $scheduleEvents = $scheduleEventsQuery->get();
        $ministryRoles = $ministry->ministryRoles()->orderBy('sort_order')->get();

        // Get or create ministry board
        $ministryBoard = Board::firstOrCreate(
            ['church_id' => $church->id, 'ministry_id' => $ministry->id],
            [
                'name' => $ministry->name,
                'color' => $ministry->color ?? '#3b82f6',
                'is_archived' => false,
            ]
        );

        // Ensure default columns exist
        if ($ministryBoard->columns()->count() === 0) {
            $defaultColumns = [
                ['name' => 'До виконання', 'color' => 'gray', 'position' => 0],
                ['name' => 'В процесі', 'color' => 'blue', 'position' => 1],
                ['name' => 'На перевірці', 'color' => 'yellow', 'position' => 2],
                ['name' => 'Завершено', 'color' => 'green', 'position' => 3],
            ];
            foreach ($defaultColumns as $column) {
                $ministryBoard->columns()->create($column);
            }
        }

        // Migrate cards from main board to ministry board (one-time)
        if ($ministryBoard->cards()->count() === 0) {
            $mainBoard = Board::where('church_id', $church->id)
                ->where('name', 'Трекер завдань')
                ->first();

            if ($mainBoard) {
                $mainColumns = $mainBoard->columns()->orderBy('position')->pluck('id')->toArray();
                $ministryColumns = $ministryBoard->columns()->orderBy('position')->pluck('id')->toArray();

                foreach ($mainColumns as $idx => $mainColId) {
                    $targetColId = $ministryColumns[$idx] ?? end($ministryColumns);

                    BoardCard::where('column_id', $mainColId)
                        ->where('ministry_id', $ministry->id)
                        ->update(['column_id' => $targetColId]);
                }
            }
        }

        $ministryBoard->load([
            'columns.cards.assignee',
            'columns.cards.ministry',
            'columns.cards.epic',
            'columns.cards.checklistItems',
            'columns.cards.comments',
            'epics',
            'ministry',
        ]);

        $boardPeople = Person::where('church_id', $church->id)->orderBy('first_name')->get();
        $boardMinistries = collect([$ministry]);

        $columnIds = $ministryBoard->columns->pluck('id')->toArray();
        $epicStatsRaw = BoardCard::whereIn('column_id', $columnIds)
            ->whereNotNull('epic_id')
            ->selectRaw('epic_id, COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            ->groupBy('epic_id')
            ->get()
            ->keyBy('epic_id');

        $boardEpics = $ministryBoard->epics->map(function ($epic) use ($epicStatsRaw) {
            $stat = $epicStatsRaw[$epic->id] ?? null;
            $total = $stat ? (int) $stat->total : 0;
            $completed = $stat ? (int) $stat->completed : 0;
            return [
                'id' => $epic->id,
                'name' => $epic->name,
                'color' => $epic->color,
                'description' => $epic->description,
                'total' => $total,
                'completed' => $completed,
                'progress' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        });

        // Budget items for expenses tab
        $budgetYear = (int) request('budget_year', now()->year);
        $budgetMonth = (int) request('budget_month', now()->month);

        $ministryBudget = MinistryBudget::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('year', $budgetYear)
            ->where('month', $budgetMonth)
            ->with(['items.responsiblePeople', 'items.category'])
            ->first();

        // Spending by budget_item_id and category for this ministry/period (exclude allocations)
        $spendingRaw = Transaction::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('source_type', '!=', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $budgetYear)
            ->whereMonth('date', $budgetMonth)
            ->selectRaw('category_id, budget_item_id, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('category_id', 'budget_item_id')
            ->get();

        $spendingByItem = [];
        $spendingByCategoryUnlinked = [];
        $totalSpent = 0;
        foreach ($spendingRaw as $row) {
            $totalSpent += $row->total;
            if ($row->budget_item_id) {
                $spendingByItem[$row->budget_item_id] = ($spendingByItem[$row->budget_item_id] ?? 0) + $row->total;
            } else {
                $catKey = $row->category_id ?: 0;
                $spendingByCategoryUnlinked[$catKey] = ($spendingByCategoryUnlinked[$catKey] ?? 0) + $row->total;
            }
        }

        $budgetItems = [];
        $itemsSpentTotal = 0;
        if ($ministryBudget && $ministryBudget->items->isNotEmpty()) {
            foreach ($ministryBudget->items as $item) {
                $directSpent = $spendingByItem[$item->id] ?? 0;
                $autoMatched = $item->category_id ? ($spendingByCategoryUnlinked[$item->category_id] ?? 0) : 0;
                $itemSpent = $directSpent + $autoMatched;
                $itemsSpentTotal += $itemSpent;
                $budgetItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'category_id' => $item->category_id,
                    'planned_amount' => (float) $item->planned_amount,
                    'planned_date' => $item->planned_date?->format('Y-m-d'),
                    'actual' => $itemSpent,
                    'difference' => (float) $item->planned_amount - $itemSpent,
                    'responsible' => $item->responsiblePeople,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                ];
            }
        }

        // Income for this ministry/period (exclude allocations — they are shown separately)
        $totalIncome = Transaction::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('source_type', '!=', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $budgetYear)
            ->whereMonth('date', $budgetMonth)
            ->sum(\DB::raw('COALESCE(amount_uah, amount)'));

        // Allocation IN transactions for this ministry/period
        $totalAllocated = (float) Transaction::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('source_type', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $budgetYear)
            ->whereMonth('date', $budgetMonth)
            ->sum(\DB::raw('COALESCE(amount_uah, amount)'));

        $monthNames = ['', 'Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
        $budgetData = [
            'budget' => $ministryBudget,
            'items' => $budgetItems,
            'has_items' => !empty($budgetItems),
            'effective_budget' => $ministryBudget ? $ministryBudget->getEffectiveBudget() : 0,
            'total_spent' => $totalSpent,
            'total_income' => (float) $totalIncome,
            'total_allocated' => $totalAllocated,
            'unmatched_spent' => max(0, $totalSpent - $itemsSpentTotal),
            'year' => $budgetYear,
            'month' => $budgetMonth,
            'month_name' => $monthNames[$budgetMonth] . ' ' . $budgetYear,
        ];

        $expenseCategories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();

        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);

        return view('ministries.show', compact('ministry', 'tab', 'boards', 'availablePeople', 'resources', 'currentFolder', 'breadcrumbs', 'registeredUsers', 'goalsStats', 'songs', 'songBoardTags', 'scheduleEvents', 'ministryRoles', 'ministryBoard', 'boardPeople', 'boardMinistries', 'boardEpics', 'budgetData', 'expenseCategories', 'enabledCurrencies'));
    }

    /**
     * API: Budget data for a ministry/month (JSON) — used by Alpine.js
     */
    public function budgetData(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('view-ministry', $ministry);

        $church = $this->getCurrentChurch();
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $ministryBudget = MinistryBudget::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('year', $year)
            ->where('month', $month)
            ->with(['items.responsiblePeople', 'items.category'])
            ->first();

        $spendingRaw = Transaction::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('source_type', '!=', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('category_id, budget_item_id, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('category_id', 'budget_item_id')
            ->get();

        $spendingByItem = [];
        $spendingByCategoryUnlinked = [];
        $totalSpent = 0;
        foreach ($spendingRaw as $row) {
            $totalSpent += $row->total;
            if ($row->budget_item_id) {
                $spendingByItem[$row->budget_item_id] = ($spendingByItem[$row->budget_item_id] ?? 0) + $row->total;
            } else {
                $catKey = $row->category_id ?: 0;
                $spendingByCategoryUnlinked[$catKey] = ($spendingByCategoryUnlinked[$catKey] ?? 0) + $row->total;
            }
        }

        $budgetItems = [];
        $itemsSpentTotal = 0;
        if ($ministryBudget && $ministryBudget->items->isNotEmpty()) {
            foreach ($ministryBudget->items as $item) {
                $directSpent = $spendingByItem[$item->id] ?? 0;
                $autoMatched = $item->category_id ? ($spendingByCategoryUnlinked[$item->category_id] ?? 0) : 0;
                $itemSpent = $directSpent + $autoMatched;
                $itemsSpentTotal += $itemSpent;
                $budgetItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category_name' => $item->category?->name,
                    'category_icon' => $item->category?->icon,
                    'category_id' => $item->category_id,
                    'planned_amount' => (float) $item->planned_amount,
                    'planned_date' => $item->planned_date?->format('Y-m-d'),
                    'actual' => $itemSpent,
                    'difference' => (float) $item->planned_amount - $itemSpent,
                    'responsible' => $item->responsiblePeople->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->short_name ?? $p->first_name,
                    ]),
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                    'person_ids' => $item->responsiblePeople->pluck('id'),
                ];
            }
        }

        $effectiveBudget = $ministryBudget ? $ministryBudget->getEffectiveBudget() : 0;

        // Income for this ministry/period (exclude allocations — shown separately)
        $totalIncome = Transaction::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('source_type', '!=', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum(\DB::raw('COALESCE(amount_uah, amount)'));

        // Allocation IN transactions for this ministry/period
        $totalAllocated = (float) Transaction::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('source_type', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum(\DB::raw('COALESCE(amount_uah, amount)'));

        return response()->json([
            'success' => true,
            'budget_id' => $ministryBudget?->id,
            'items' => $budgetItems,
            'has_items' => !empty($budgetItems),
            'effective_budget' => $effectiveBudget,
            'total_spent' => $totalSpent,
            'total_income' => (float) $totalIncome,
            'total_allocated' => $totalAllocated,
            'unmatched_spent' => max(0, $totalSpent - $itemsSpentTotal),
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Copy budget items from current month to next month
     */
    public function copyBudget(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'from_year' => 'required|integer',
            'from_month' => 'required|integer|min:1|max:12',
            'to_year' => 'required|integer',
            'to_month' => 'required|integer|min:1|max:12',
        ]);

        $sourceBudget = MinistryBudget::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('year', $validated['from_year'])
            ->where('month', $validated['from_month'])
            ->with('items.responsiblePeople')
            ->first();

        if (!$sourceBudget || $sourceBudget->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Немає статей для копіювання.'], 422);
        }

        $targetBudget = MinistryBudget::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('year', $validated['to_year'])
            ->where('month', $validated['to_month'])
            ->first();

        if ($targetBudget && $targetBudget->items()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Цільовий місяць вже має статті бюджету.'], 422);
        }

        if (!$targetBudget) {
            $targetBudget = MinistryBudget::create([
                'church_id' => $church->id,
                'ministry_id' => $ministry->id,
                'year' => $validated['to_year'],
                'month' => $validated['to_month'],
                'monthly_budget' => 0,
            ]);
        }

        // Calculate month difference for shifting planned_date
        $sourceDate = \Carbon\Carbon::create($validated['from_year'], $validated['from_month'], 1);
        $targetDate = \Carbon\Carbon::create($validated['to_year'], $validated['to_month'], 1);
        $monthDiff = $sourceDate->diffInMonths($targetDate, false);

        foreach ($sourceBudget->items as $item) {
            $newPlannedDate = null;
            if ($item->planned_date) {
                $newPlannedDate = $item->planned_date->copy()->addMonths($monthDiff);
            }

            $newItem = BudgetItem::create([
                'church_id' => $church->id,
                'ministry_budget_id' => $targetBudget->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'planned_amount' => $item->planned_amount,
                'planned_date' => $newPlannedDate,
                'notes' => $item->notes,
                'sort_order' => $item->sort_order,
            ]);
            if ($item->responsiblePeople->isNotEmpty()) {
                $newItem->responsiblePeople()->attach($item->responsiblePeople->pluck('id'));
            }
        }

        $monthNames = ['', 'Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];

        return response()->json([
            'success' => true,
            'message' => 'Бюджет скопійовано на ' . $monthNames[$validated['to_month']] . ' ' . $validated['to_year'],
            'budget_id' => $targetBudget->id,
        ]);
    }

    /**
     * Ensure a MinistryBudget exists for ministry/month, create if needed
     */
    public function ensureBudget(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $church = $this->getCurrentChurch();
        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $budget = MinistryBudget::firstOrCreate(
            ['church_id' => $church->id, 'ministry_id' => $ministry->id, 'year' => $validated['year'], 'month' => $validated['month']],
            ['monthly_budget' => 0]
        );

        return response()->json(['success' => true, 'budget_id' => $budget->id]);
    }

    /**
     * Store a new budget item for a ministry
     */
    public function storeBudgetItem(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'budget_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'planned_amount' => 'required|numeric|min:0',
            'planned_date' => 'nullable|date',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'category_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'integer|exists:people,id',
        ]);

        $ministryBudget = MinistryBudget::where('id', $validated['budget_id'])
            ->where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->firstOrFail();

        // Resolve category: existing ID or create from custom name
        $categoryId = $validated['category_id'] ?? null;
        if (!$categoryId && !empty($validated['category_name'])) {
            $category = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $category->id;
        }

        if ($categoryId) {
            $exists = BudgetItem::where('ministry_budget_id', $ministryBudget->id)
                ->where('category_id', $categoryId)
                ->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ця категорія вже використовується в іншій статті бюджету.'], 422);
            }
        }

        $maxSort = BudgetItem::where('ministry_budget_id', $ministryBudget->id)->max('sort_order') ?? 0;

        $item = BudgetItem::create([
            'church_id' => $church->id,
            'ministry_budget_id' => $ministryBudget->id,
            'category_id' => $categoryId,
            'name' => $validated['name'],
            'planned_amount' => $validated['planned_amount'],
            'planned_date' => $validated['planned_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'sort_order' => $maxSort + 1,
        ]);

        if (!empty($validated['person_ids'])) {
            $item->responsiblePeople()->attach($validated['person_ids']);
        }

        return response()->json(['success' => true, 'message' => 'Статтю бюджету додано.', 'item' => $item->load('responsiblePeople', 'category')]);
    }

    /**
     * Update a budget item
     */
    public function updateBudgetItem(Request $request, BudgetItem $budgetItem)
    {
        $church = $this->getCurrentChurch();
        if ($budgetItem->church_id !== $church->id) abort(404);

        $ministry = $budgetItem->ministryBudget->ministry;
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'planned_amount' => 'required|numeric|min:0',
            'planned_date' => 'nullable|date',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'category_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'integer|exists:people,id',
        ]);

        // Resolve category: existing ID or create from custom name
        $church = $this->getCurrentChurch();
        $categoryId = $validated['category_id'] ?? null;
        if (!$categoryId && !empty($validated['category_name'])) {
            $category = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $category->id;
        }

        if ($categoryId) {
            $exists = BudgetItem::where('ministry_budget_id', $budgetItem->ministry_budget_id)
                ->where('category_id', $categoryId)
                ->where('id', '!=', $budgetItem->id)
                ->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ця категорія вже використовується в іншій статті бюджету.'], 422);
            }
        }

        $budgetItem->update([
            'name' => $validated['name'],
            'planned_amount' => $validated['planned_amount'],
            'planned_date' => $validated['planned_date'] ?? null,
            'category_id' => $categoryId,
            'notes' => $validated['notes'] ?? null,
        ]);

        $budgetItem->responsiblePeople()->sync($validated['person_ids'] ?? []);

        return response()->json(['success' => true, 'message' => 'Статтю бюджету оновлено.', 'item' => $budgetItem->load('responsiblePeople', 'category')]);
    }

    /**
     * Delete a budget item
     */
    public function destroyBudgetItem(Request $request, BudgetItem $budgetItem)
    {
        $church = $this->getCurrentChurch();
        if ($budgetItem->church_id !== $church->id) abort(404);

        $ministry = $budgetItem->ministryBudget->ministry;
        Gate::authorize('contribute-ministry', $ministry);

        Transaction::where('budget_item_id', $budgetItem->id)->update(['budget_item_id' => null]);
        $budgetItem->delete();

        return response()->json(['success' => true, 'message' => 'Статтю бюджету видалено.']);
    }

    // ==================
    // Ministry Expenses (via modal)
    // ==================

    /**
     * Store a new expense for a ministry (JSON API)
     */
    public function storeExpense(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'category_name' => 'nullable|string|max:100',
            'expense_type' => 'nullable|in:recurring,one_time',
            'payment_method' => 'nullable|in:cash,card',
            'budget_item_id' => 'nullable|integer|exists:budget_items,id',
            'notes' => 'nullable|string|max:5000',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
        ]);

        // If custom category name provided, find or create
        $categoryId = $validated['category_id'] ?? null;
        if (!$categoryId && !empty($validated['category_name'])) {
            $category = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $category->id;
        }

        $transaction = Transaction::create([
            'church_id' => $church->id,
            'ministry_id' => $ministry->id,
            'direction' => Transaction::DIRECTION_OUT,
            'source_type' => Transaction::SOURCE_EXPENSE,
            'expense_type' => $validated['expense_type'] ?? null,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'UAH',
            'date' => $validated['date'],
            'category_id' => $categoryId,
            'payment_method' => $validated['payment_method'] ?? null,
            'budget_item_id' => $validated['budget_item_id'] ?? null,
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'status' => Transaction::STATUS_COMPLETED,
            'recorded_by' => auth()->id(),
        ]);

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $stored = ImageService::storeWithHeicConversion($file, "receipts/{$church->id}");
                $transaction->attachments()->create([
                    'filename' => $stored['filename'],
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'size' => $stored['size'],
                ]);
            }
        }

        $transaction->load(['category', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => 'Витрату додано.',
            'transaction' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency ?? 'UAH',
                'direction' => $transaction->direction,
                'description' => $transaction->description,
                'date' => $transaction->date->format('Y-m-d'),
                'month' => (int) $transaction->date->format('m'),
                'year' => (int) $transaction->date->format('Y'),
                'date_formatted' => $transaction->date->format('d.m.Y'),
                'category' => $transaction->category?->name,
                'category_id' => $transaction->category_id,
                'expense_type' => $transaction->expense_type,
                'payment_method' => $transaction->payment_method,
                'budget_item_id' => $transaction->budget_item_id,
                'notes' => $transaction->notes,
                'attachments' => $transaction->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'url' => \Storage::url($a->path),
                    'is_image' => str_starts_with($a->mime_type, 'image/'),
                    'original_name' => $a->original_name,
                ]),
            ],
        ]);
    }

    /**
     * Get expense data for editing (JSON)
     */
    public function editExpenseData(Transaction $transaction)
    {
        $church = $this->getCurrentChurch();
        if ($transaction->church_id !== $church->id) abort(404);

        $ministry = Ministry::findOrFail($transaction->ministry_id);
        Gate::authorize('contribute-ministry', $ministry);

        $transaction->load('attachments');

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency ?? 'UAH',
                'description' => $transaction->description,
                'date' => $transaction->date->format('Y-m-d'),
                'category_id' => $transaction->category_id,
                'expense_type' => $transaction->expense_type,
                'payment_method' => $transaction->payment_method,
                'budget_item_id' => $transaction->budget_item_id,
                'notes' => $transaction->notes,
                'attachments' => $transaction->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'url' => \Storage::url($a->path),
                    'is_image' => str_starts_with($a->mime_type, 'image/'),
                    'original_name' => $a->original_name,
                ]),
            ],
        ]);
    }

    /**
     * Update an expense (JSON API, multipart for receipts)
     */
    public function updateExpense(Request $request, Transaction $transaction)
    {
        $church = $this->getCurrentChurch();
        if ($transaction->church_id !== $church->id) abort(404);

        $ministry = Ministry::findOrFail($transaction->ministry_id);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'category_name' => 'nullable|string|max:100',
            'expense_type' => 'nullable|in:recurring,one_time',
            'payment_method' => 'nullable|in:cash,card',
            'budget_item_id' => 'nullable|integer|exists:budget_items,id',
            'notes' => 'nullable|string|max:5000',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'integer|exists:transaction_attachments,id',
        ]);

        // If custom category name provided, find or create
        $categoryId = $validated['category_id'] ?? null;
        if (!$categoryId && !empty($validated['category_name'])) {
            $category = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $category->id;
        }

        $transaction->update([
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'UAH',
            'date' => $validated['date'],
            'description' => $validated['description'],
            'category_id' => $categoryId,
            'expense_type' => $validated['expense_type'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'budget_item_id' => $validated['budget_item_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (!empty($validated['delete_attachments'])) {
            $transaction->attachments()
                ->whereIn('id', $validated['delete_attachments'])
                ->get()
                ->each(fn($att) => $att->delete());
        }

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $stored = ImageService::storeWithHeicConversion($file, "receipts/{$church->id}");
                $transaction->attachments()->create([
                    'filename' => $stored['filename'],
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'size' => $stored['size'],
                ]);
            }
        }

        $transaction->refresh()->load(['category', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => 'Витрату оновлено.',
            'transaction' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency ?? 'UAH',
                'direction' => $transaction->direction,
                'description' => $transaction->description,
                'date' => $transaction->date->format('Y-m-d'),
                'month' => (int) $transaction->date->format('m'),
                'year' => (int) $transaction->date->format('Y'),
                'date_formatted' => $transaction->date->format('d.m.Y'),
                'category' => $transaction->category?->name,
                'category_id' => $transaction->category_id,
                'expense_type' => $transaction->expense_type,
                'payment_method' => $transaction->payment_method,
                'budget_item_id' => $transaction->budget_item_id,
                'notes' => $transaction->notes,
                'attachments' => $transaction->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'url' => \Storage::url($a->path),
                    'is_image' => str_starts_with($a->mime_type, 'image/'),
                    'original_name' => $a->original_name,
                ]),
            ],
        ]);
    }

    /**
     * Delete an expense (JSON API)
     */
    public function destroyExpense(Transaction $transaction)
    {
        $church = $this->getCurrentChurch();
        if ($transaction->church_id !== $church->id) abort(404);

        $ministry = Ministry::findOrFail($transaction->ministry_id);
        Gate::authorize('contribute-ministry', $ministry);

        $transaction->delete();

        return response()->json(['success' => true, 'message' => 'Витрату видалено.']);
    }

    /**
     * Store a new income transaction for a ministry (JSON API)
     */
    public function storeIncome(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ]);

        $transaction = Transaction::create([
            'church_id' => $church->id,
            'ministry_id' => $ministry->id,
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_INCOME,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'UAH',
            'date' => $validated['date'],
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'status' => Transaction::STATUS_COMPLETED,
            'recorded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Надходження додано.',
            'transaction' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency ?? 'UAH',
                'description' => $transaction->description,
                'date' => $transaction->date->format('Y-m-d'),
                'month' => (int) $transaction->date->format('m'),
                'year' => (int) $transaction->date->format('Y'),
                'date_formatted' => $transaction->date->format('d.m.Y'),
                'direction' => 'in',
                'notes' => $transaction->notes,
            ],
        ]);
    }

    /**
     * Delete an income transaction (JSON API)
     */
    public function deleteIncome(Transaction $transaction)
    {
        $church = $this->getCurrentChurch();
        if ($transaction->church_id !== $church->id) abort(404);
        if ($transaction->direction !== Transaction::DIRECTION_IN) abort(404);

        $ministry = Ministry::findOrFail($transaction->ministry_id);
        Gate::authorize('contribute-ministry', $ministry);

        $transaction->delete();

        return response()->json(['success' => true, 'message' => 'Надходження видалено.']);
    }

    public function scheduleGridData(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('view-ministry', $ministry);

        $church = $this->getCurrentChurch();

        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $monthNames = ['', 'січ', 'лют', 'бер', 'кві', 'тра', 'чер', 'лип', 'сер', 'вер', 'жов', 'лис', 'гру'];

        $rawEvents = Event::where('church_id', $church->id)
            ->where('service_type', 'sunday_service')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $events = $rawEvents->map(fn($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'date' => $e->date->format('Y-m-d'),
            'dateLabel' => $e->date->format('j') . ' ' . $monthNames[$e->date->month],
            'dayOfWeek' => mb_substr($e->date->translatedFormat('D'), 0, 2),
            'dataUrl' => route('ministries.worship-events.data', [$ministry, $e]),
            'eventUrl' => route('ministries.worship-events.show', [$ministry, $e]),
            'time' => $e->time?->format('H:i') ?? '',
            'fullDate' => $e->date->translatedFormat('l, j M'),
            'isSundayService' => true,
        ]);

        $roles = $ministry->ministryRoles()->orderBy('sort_order')->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'icon' => $r->icon,
            ]);

        $eventIds = $events->pluck('id')->toArray();

        $teamEntries = EventMinistryTeam::whereIn('event_id', $eventIds)
            ->where('ministry_id', $ministry->id)
            ->with('person')
            ->get();

        $grid = [];
        $seen = []; // track person_id per role+event to skip duplicates
        foreach ($teamEntries as $entry) {
            $roleId = (string) $entry->ministry_role_id;
            $eventId = (string) $entry->event_id;
            $key = $roleId . '-' . $eventId . '-' . $entry->person_id;

            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            if (!isset($grid[$roleId])) {
                $grid[$roleId] = [];
            }
            if (!isset($grid[$roleId][$eventId])) {
                $grid[$roleId][$eventId] = [];
            }

            $person = $entry->person;
            $personName = $person
                ? $person->first_name . ' ' . mb_substr($person->last_name, 0, 1) . '.'
                : '?';

            $grid[$roleId][$eventId][] = [
                'id' => $entry->id,
                'person_id' => $entry->person_id,
                'person_name' => $personName,
                'status' => $entry->status,
                'has_telegram' => (bool) $person?->telegram_chat_id,
            ];
        }

        $members = $ministry->members()->orderBy('last_name')->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->full_name,
                'has_telegram' => (bool) $m->telegram_chat_id,
            ]);

        // Songs per event
        $songs = [];
        if (count($eventIds) > 0) {
            $eventSongs = EventSong::whereIn('event_id', $eventIds)
                ->with('song')
                ->orderBy('order')
                ->get();

            foreach ($eventSongs as $es) {
                $eId = (string) $es->event_id;
                if (!isset($songs[$eId])) {
                    $songs[$eId] = [];
                }
                $songs[$eId][] = [
                    'title' => $es->song?->title ?? '?',
                    'key' => $es->key,
                ];
            }
        }

        // Add counts to events
        $teamByEvent = [];
        foreach ($teamEntries as $entry) {
            $eId = (string) $entry->event_id;
            $teamByEvent[$eId] = ($teamByEvent[$eId] ?? 0) + 1;
        }

        $events = $events->map(function ($e) use ($songs, $teamByEvent) {
            $eId = (string) $e['id'];
            $e['songsCount'] = count($songs[$eId] ?? []);
            $e['teamCount'] = $teamByEvent[$eId] ?? 0;
            return $e;
        });

        $currentPersonId = auth()->user()->person?->id;

        return response()->json(compact('events', 'roles', 'grid', 'members', 'songs', 'currentPersonId'));
    }

    public function edit(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->orderBy('last_name')
            ->get();

        return view('ministries.edit', compact('ministry', 'people'));
    }

    public function update(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'monthly_budget' => 'nullable|numeric|min:0',
            'is_worship_ministry' => 'boolean',
            'is_sunday_service_part' => 'boolean',
        ]);

        $validated['is_worship_ministry'] = $request->boolean('is_worship_ministry');
        $validated['is_sunday_service_part'] = $request->boolean('is_sunday_service_part');

        $oldLeaderId = $ministry->getOriginal('leader_id');
        $ministry->update($validated);

        // If leader changed, demote old leader and promote new one
        if ($ministry->leader_id !== $oldLeaderId) {
            // Demote old leader's role in pivot
            if ($oldLeaderId && $ministry->members()->where('people.id', $oldLeaderId)->exists()) {
                $ministry->members()->updateExistingPivot($oldLeaderId, ['role' => 'member']);
            }

            // Ensure new leader is a member with 'leader' role
            if ($ministry->leader_id) {
                if ($ministry->members()->where('people.id', $ministry->leader_id)->exists()) {
                    $ministry->members()->updateExistingPivot($ministry->leader_id, ['role' => 'leader']);
                } else {
                    $ministry->members()->attach($ministry->leader_id, [
                        'role' => 'leader',
                        'joined_at' => now(),
                    ]);
                }
            }
        } elseif ($ministry->leader_id && !$ministry->members()->where('people.id', $ministry->leader_id)->exists()) {
            // Leader unchanged but not in members yet
            $ministry->members()->attach($ministry->leader_id, [
                'role' => 'leader',
                'joined_at' => now(),
            ]);
        }

        \App\Models\Church::clearMinistriesCache($ministry->church_id);

        return $this->successResponse($request, 'Команду оновлено!', 'ministries.show', [$ministry]);
    }

    public function destroy(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        $this->authorize('delete', $ministry);

        $churchId = $ministry->church_id;
        $ministry->delete();

        \App\Models\Church::clearMinistriesCache($churchId);

        return $this->successResponse($request, 'Служіння видалено.', 'ministries.index');
    }

    public function membersJson(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);

        $members = $ministry->members()
            ->select('people.id', 'people.first_name', 'people.last_name')
            ->orderBy('people.first_name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->full_name ?? ($p->first_name . ' ' . $p->last_name),
            ]);

        return response()->json(['members' => $members]);
    }

    public function addMember(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'person_id' => ['required', new \App\Rules\BelongsToChurch(\App\Models\Person::class)],
            'position_ids' => 'nullable|array',
        ]);

        // Check if already a member
        if ($ministry->members()->where('person_id', $validated['person_id'])->exists()) {
            return $this->errorResponse($request, 'Ця людина вже є учасником служіння.');
        }

        $ministry->members()->attach($validated['person_id'], [
            'position_ids' => json_encode(array_map('intval', $validated['position_ids'] ?? [])),
        ]);

        // Log member added
        $person = Person::find($validated['person_id']);
        $this->logAuditAction('member_added', 'Ministry', $ministry->id, $ministry->name, [
            'person_id' => $validated['person_id'],
            'person_name' => $person?->full_name,
            'position_ids' => $validated['position_ids'] ?? [],
        ]);

        return $this->successResponse($request, 'Учасника додано.');
    }

    public function removeMember(Request $request, Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);
        abort_unless($person->church_id === $this->getCurrentChurch()->id, 404);

        // Prevent removing the leader from members
        if ($ministry->leader_id === $person->id) {
            return $this->errorResponse($request, 'Неможливо видалити лідера з команди. Спочатку змініть лідера.');
        }

        $ministry->members()->detach($person->id);

        // Log member removed
        $this->logAuditAction('member_removed', 'Ministry', $ministry->id, $ministry->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
        ]);

        return $this->successResponse($request, 'Учасника видалено.');
    }

    public function updateMemberPositions(Request $request, Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);
        abort_unless($person->church_id === $this->getCurrentChurch()->id, 404);

        $validated = $request->validate([
            'position_ids' => 'nullable|array',
        ]);

        // Get old positions
        $oldPivot = $ministry->members()->where('person_id', $person->id)->first()?->pivot;
        $oldPositionIds = $oldPivot ? json_decode($oldPivot->position_ids ?? '[]', true) : [];

        $ministry->members()->updateExistingPivot($person->id, [
            'position_ids' => json_encode(array_map('intval', $validated['position_ids'] ?? [])),
        ]);

        // Log positions update
        $this->logAuditAction('positions_updated', 'Ministry', $ministry->id, $ministry->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
            'new_position_ids' => $validated['position_ids'] ?? [],
        ], [
            'old_position_ids' => $oldPositionIds,
        ]);

        return $this->successResponse($request, 'Позиції оновлено.');
    }

    public function updateMemberRole(Request $request, Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);
        abort_unless($person->church_id === $this->getCurrentChurch()->id, 404);

        $validated = $request->validate([
            'role' => 'required|in:member,co-leader,leader',
        ]);

        $newRole = $validated['role'];

        // If setting as leader, update ministry's leader_id
        if ($newRole === 'leader') {
            // Demote current leader in pivot if exists
            if ($ministry->leader_id && $ministry->leader_id !== $person->id) {
                $ministry->members()->updateExistingPivot($ministry->leader_id, ['role' => 'member']);
            }
            $ministry->update(['leader_id' => $person->id]);
        } elseif ($ministry->leader_id === $person->id) {
            // Removing leader role — clear leader_id
            $ministry->update(['leader_id' => null]);
        }

        $ministry->members()->updateExistingPivot($person->id, ['role' => $newRole]);

        return response()->json(['success' => true, 'role' => $newRole]);
    }

    public function togglePrivacy(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $oldValue = $ministry->is_private;
        $ministry->update([
            'is_private' => !$ministry->is_private,
        ]);

        // Log privacy toggle
        $this->logAuditAction('privacy_toggled', 'Ministry', $ministry->id, $ministry->name, [
            'is_private' => $ministry->is_private,
        ], [
            'is_private' => $oldValue,
        ]);

        return response()->json([
            'success' => true,
            'is_private' => $ministry->is_private,
        ]);
    }

    public function updateVisibility(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'visibility' => 'required|in:public,members,leaders,specific',
            'allowed_person_ids' => 'nullable|array',
            'allowed_person_ids.*' => 'integer|exists:people,id',
        ]);

        $oldVisibility = $ministry->visibility;
        $oldAllowedIds = $ministry->allowed_person_ids;

        $ministry->update([
            'visibility' => $validated['visibility'],
            'allowed_person_ids' => $validated['allowed_person_ids'] ?? [],
            // Also update is_private for backwards compatibility
            'is_private' => $validated['visibility'] !== 'public',
        ]);

        // Log visibility update
        $this->logAuditAction('visibility_updated', 'Ministry', $ministry->id, $ministry->name, [
            'visibility' => $validated['visibility'],
            'allowed_person_ids' => $validated['allowed_person_ids'] ?? [],
        ], [
            'visibility' => $oldVisibility,
            'allowed_person_ids' => $oldAllowedIds,
        ]);

        return response()->json([
            'success' => true,
            'visibility' => $ministry->visibility,
            'allowed_person_ids' => $ministry->allowed_person_ids,
        ]);
    }

    public function updateSongBoardTags(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'tags' => 'present|array',
            'tags.*' => 'string|max:50',
        ]);

        $settings = $ministry->settings ?? [];
        $settings['song_board_tags'] = array_values(array_filter($validated['tags']));
        $ministry->update(['settings' => $settings]);

        return response()->json(['success' => true, 'tags' => $settings['song_board_tags']]);
    }

    public function storeWorshipRole(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = WorshipRole::where('church_id', $this->getCurrentChurch()->id)->max('sort_order') ?? 0;

        $role = WorshipRole::create([
            'church_id' => $this->getCurrentChurch()->id,
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return $this->successResponse($request, 'Роль додано', null, [], ['id' => $role->id]);
    }

    public function updateWorshipRole(Request $request, Ministry $ministry, WorshipRole $role)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($role);
        Gate::authorize('contribute-ministry', $ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $role->update($validated);

        return $this->successResponse($request, 'Роль оновлено');
    }

    public function destroyWorshipRole(Request $request, Ministry $ministry, WorshipRole $role)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($role);
        Gate::authorize('contribute-ministry', $ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $role->delete();

        return $this->successResponse($request, 'Роль видалено');
    }

    public function storeMinistryRole(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = $ministry->ministryRoles()->max('sort_order') ?? 0;

        $role = MinistryRole::create([
            'ministry_id' => $ministry->id,
            'name' => $validated['name'],
            'icon' => !empty($validated['icon']) ? $validated['icon'] : null,
            'color' => !empty($validated['color']) ? $validated['color'] : null,
            'sort_order' => $maxOrder + 1,
        ]);

        return $this->successResponse($request, 'Роль додано', null, [], ['id' => $role->id]);
    }

    public function updateMinistryRole(Request $request, Ministry $ministry, MinistryRole $role)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        if ($role->ministry_id !== $ministry->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['icon'] = !empty($validated['icon']) ? $validated['icon'] : null;
        $validated['color'] = !empty($validated['color']) ? $validated['color'] : null;
        $role->update($validated);

        return $this->successResponse($request, 'Роль оновлено');
    }

    public function destroyMinistryRole(Request $request, Ministry $ministry, MinistryRole $role)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('contribute-ministry', $ministry);

        if ($role->ministry_id !== $ministry->id) {
            abort(404);
        }

        $role->delete();

        return $this->successResponse($request, 'Роль видалено');
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
