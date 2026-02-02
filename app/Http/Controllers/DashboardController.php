<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Board;
use App\Models\BoardCard;
use App\Models\Event;
use App\Models\Group;
use App\Models\Person;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Allowed column widths for dashboard widgets (out of 12).
     */
    private const ALLOWED_COLS = [3, 4, 6, 8, 12];

    public function index()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Resolve the dashboard layout
        $layout = $this->resolveLayout($church, $isAdmin);

        // Collect only the enabled widget IDs
        $enabledWidgets = collect($layout['widgets'])
            ->where('enabled', true)
            ->pluck('id')
            ->toArray();

        // Load data only for enabled widgets
        $viewData = $this->loadWidgetData($church, $user, $isAdmin, $enabledWidgets);

        // Column class mapping
        $colClasses = [
            3  => 'md:col-span-3',
            4  => 'md:col-span-4',
            6  => 'md:col-span-6',
            8  => 'md:col-span-8',
            12 => 'md:col-span-12',
        ];

        // All widget configs for the "add widget" panel
        $allWidgets = config('dashboard_widgets.widgets', []);

        return view('dashboard.index', array_merge($viewData, [
            'layout' => $layout,
            'colClasses' => $colClasses,
            'allWidgets' => $allWidgets,
            'isAdmin' => $isAdmin,
        ]));
    }

    /**
     * Resolve dashboard layout from church settings or default.
     */
    private function resolveLayout($church, bool $isAdmin): array
    {
        $layout = $church->getSetting('dashboard_layout');
        $widgetRegistry = config('dashboard_widgets.widgets', []);

        if (!$layout || !isset($layout['widgets'])) {
            $layout = $this->getDefaultLayout();
        }

        // Merge any new widget types that were added to config but not yet in the saved layout
        $savedIds = collect($layout['widgets'])->pluck('id')->toArray();
        $maxOrder = collect($layout['widgets'])->max('order') ?? -1;

        foreach ($widgetRegistry as $id => $config) {
            if (!in_array($id, $savedIds)) {
                $maxOrder++;
                $layout['widgets'][] = [
                    'id' => $id,
                    'order' => $maxOrder,
                    'cols' => $config['default_cols'],
                    'enabled' => false,
                ];
            }
        }

        // Remove widgets that no longer exist in config
        $layout['widgets'] = collect($layout['widgets'])
            ->filter(fn($w) => isset($widgetRegistry[$w['id']]))
            ->values()
            ->toArray();

        // Filter admin-only widgets for non-admin users
        if (!$isAdmin) {
            $layout['widgets'] = collect($layout['widgets'])
                ->filter(fn($w) => !($widgetRegistry[$w['id']]['admin_only'] ?? false))
                ->values()
                ->toArray();
        }

        // Sort by order
        usort($layout['widgets'], fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return $layout;
    }

    /**
     * Get the default dashboard layout.
     */
    public static function getDefaultLayout(): array
    {
        return [
            'version' => 1,
            'widgets' => [
                ['id' => 'stats_grid',          'order' => 0,  'cols' => 12, 'enabled' => true],
                ['id' => 'birthdays',           'order' => 1,  'cols' => 12, 'enabled' => true],
                ['id' => 'pending_assignments', 'order' => 2,  'cols' => 12, 'enabled' => true],
                ['id' => 'upcoming_events',     'order' => 3,  'cols' => 8,  'enabled' => true],
                ['id' => 'attendance_chart',    'order' => 4,  'cols' => 4,  'enabled' => true],
                ['id' => 'analytics_charts',    'order' => 5,  'cols' => 12, 'enabled' => true],
                ['id' => 'financial_summary',   'order' => 6,  'cols' => 12, 'enabled' => true],
                ['id' => 'ministry_budgets',    'order' => 7,  'cols' => 6,  'enabled' => true],
                ['id' => 'expenses_breakdown',  'order' => 8,  'cols' => 6,  'enabled' => true],
                ['id' => 'need_attention',      'order' => 9,  'cols' => 6,  'enabled' => true],
                ['id' => 'urgent_tasks',        'order' => 10, 'cols' => 12, 'enabled' => false],
            ],
        ];
    }

    /**
     * Load data for enabled widgets only.
     */
    private function loadWidgetData($church, $user, bool $isAdmin, array $enabledWidgets): array
    {
        $data = [
            'stats' => [],
            'upcomingEvents' => collect(),
            'attendanceData' => [],
            'pendingAssignments' => collect(),
            'needAttention' => collect(),
            'ministryBudgets' => collect(),
            'birthdaysThisMonth' => collect(),
            'urgentTasks' => collect(),
            'growthData' => [],
            'financialData' => [],
            'expensesByCategory' => collect(),
        ];

        // Stats are needed by stats_grid and financial_summary
        $needsStats = array_intersect($enabledWidgets, ['stats_grid', 'financial_summary']);
        if (!empty($needsStats)) {
            $data['stats'] = $this->loadCachedStats($church);
        }

        // Financial data for admin widgets
        if ($isAdmin && array_intersect($enabledWidgets, ['financial_summary', 'analytics_charts'])) {
            $this->loadFinancialData($church, $data);
        }

        // Expenses for admin
        if ($isAdmin && in_array('expenses_breakdown', $enabledWidgets)) {
            $this->loadExpensesBreakdown($church, $data);
        }

        if (in_array('birthdays', $enabledWidgets)) {
            $data['birthdaysThisMonth'] = $this->loadBirthdaysThisMonth($church);
        }

        if (in_array('upcoming_events', $enabledWidgets)) {
            $data['upcomingEvents'] = $this->loadUpcomingEvents($church);
        }

        if (in_array('attendance_chart', $enabledWidgets)) {
            $data['attendanceData'] = $this->loadAttendanceData($church);
        }

        if (in_array('pending_assignments', $enabledWidgets)) {
            $data['pendingAssignments'] = $this->loadPendingAssignments($user);
        }

        if ($isAdmin && in_array('need_attention', $enabledWidgets)) {
            $data['needAttention'] = $this->loadNeedAttention($church);
        }

        if ($isAdmin && in_array('ministry_budgets', $enabledWidgets)) {
            $data['ministryBudgets'] = $this->loadMinistryBudgets($church);
        }

        if ($isAdmin && in_array('urgent_tasks', $enabledWidgets)) {
            $data['urgentTasks'] = $this->loadUrgentTasks($church);
        }

        if ($isAdmin && in_array('financial_summary', $enabledWidgets)) {
            $data['growthData'] = $this->loadGrowthData($church);
        }

        return $data;
    }

    private function loadCachedStats($church): array
    {
        $cacheKey = "dashboard_stats_{$church->id}";

        return Cache::remember($cacheKey, 1800, function () use ($church) {
            $peopleQuery = Person::where('church_id', $church->id);
            $today = now();
            $threeMonthsAgo = now()->subMonths(3);
            $ministryIds = $church->ministries()->pluck('id');

            $totalPeople = (clone $peopleQuery)->count();
            $leadersCount = (clone $peopleQuery)
                ->where(function ($q) {
                    $q->whereHas('leadingMinistries')
                      ->orWhereHas('leadingGroups');
                })->count();
            $volunteersCount = (clone $peopleQuery)->whereHas('ministries')->count();
            $newThisMonth = (clone $peopleQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $ageStatsRaw = \DB::table('people')
                ->where('church_id', $church->id)
                ->whereNull('deleted_at')
                ->whereNotNull('birth_date')
                ->selectRaw("
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, ?) <= 12 THEN 1 ELSE 0 END) as children,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 13 AND 17 THEN 1 ELSE 0 END) as teens,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 18 AND 35 THEN 1 ELSE 0 END) as youth,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 36 AND 59 THEN 1 ELSE 0 END) as adults,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, ?) >= 60 THEN 1 ELSE 0 END) as seniors
                ", [$today, $today, $today, $today, $today])
                ->first();

            $ageStats = [
                'children' => (int) ($ageStatsRaw->children ?? 0),
                'teens' => (int) ($ageStatsRaw->teens ?? 0),
                'youth' => (int) ($ageStatsRaw->youth ?? 0),
                'adults' => (int) ($ageStatsRaw->adults ?? 0),
                'seniors' => (int) ($ageStatsRaw->seniors ?? 0),
            ];

            $peopleTrend = Person::where('church_id', $church->id)
                ->where('created_at', '>=', $threeMonthsAgo)->count();
            $volunteersThreeMonthsAgo = \DB::table('ministry_person')
                ->whereIn('ministry_id', $ministryIds)
                ->where('created_at', '<', $threeMonthsAgo)
                ->distinct('person_id')->count('person_id');

            $ministriesList = $church->ministries()->withCount('members')->orderByDesc('members_count')->get();
            $activeVolunteers = \DB::table('ministry_person')
                ->whereIn('ministry_id', $ministryIds)
                ->distinct('person_id')->count('person_id');
            $ministriesWithEvents = $church->ministries()
                ->whereHas('events', fn($q) => $q->where('date', '>=', now()))->count();

            $groupStatsRaw = \DB::table('groups')
                ->where('church_id', $church->id)
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'paused' THEN 1 ELSE 0 END) as paused,
                    SUM(CASE WHEN status = 'vacation' THEN 1 ELSE 0 END) as vacation
                ")
                ->first();

            $totalGroups = (int) ($groupStatsRaw->total ?? 0);
            $activeGroups = (int) ($groupStatsRaw->active ?? 0);
            $pausedGroups = (int) ($groupStatsRaw->paused ?? 0);
            $vacationGroups = (int) ($groupStatsRaw->vacation ?? 0);

            $totalGroupMembers = $activeGroups > 0
                ? \DB::table('group_person')
                    ->join('groups', 'group_person.group_id', '=', 'groups.id')
                    ->where('groups.church_id', $church->id)
                    ->where('groups.status', 'active')
                    ->distinct('group_person.person_id')
                    ->count('group_person.person_id')
                : 0;

            $eventStatsRaw = \DB::table('events')
                ->where('church_id', $church->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN date >= ? THEN 1 ELSE 0 END) as upcoming
                ", [now()->toDateString()])
                ->first();

            $eventsThisMonth = (int) ($eventStatsRaw->total ?? 0);
            $upcomingEventsCount = (int) ($eventStatsRaw->upcoming ?? 0);

            return [
                'total_people' => $totalPeople,
                'leaders_count' => $leadersCount,
                'volunteers_count' => $volunteersCount,
                'new_people_this_month' => $newThisMonth,
                'people_trend' => $peopleTrend,
                'volunteers_trend' => $volunteersCount - $volunteersThreeMonthsAgo,
                'age_stats' => $ageStats,
                'total_ministries' => $ministriesList->count(),
                'ministries_list' => $ministriesList,
                'active_volunteers' => $activeVolunteers,
                'ministries_with_events' => $ministriesWithEvents,
                'total_groups' => $totalGroups,
                'active_groups' => $activeGroups,
                'paused_groups' => $pausedGroups,
                'vacation_groups' => $vacationGroups,
                'total_group_members' => $totalGroupMembers,
                'events_this_month' => $eventsThisMonth,
                'upcoming_events' => $upcomingEventsCount,
                'past_events' => $eventsThisMonth - $upcomingEventsCount,
            ];
        });
    }

    private function loadBirthdaysThisMonth($church)
    {
        return Person::where('church_id', $church->id)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->get()
            ->sortBy(fn($p) => $p->birth_date->day);
    }

    private function loadUpcomingEvents($church)
    {
        return Event::where('church_id', $church->id)
            ->where('date', '>=', now()->startOfDay())
            ->where('date', '<=', now()->addDays(7))
            ->with(['ministry', 'assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->orderBy('time')
            ->limit(5)
            ->get();
    }

    private function loadAttendanceData($church): array
    {
        $fourWeeksAgo = now()->subWeeks(3)->startOfWeek(Carbon::SUNDAY);
        $attendanceRaw = Attendance::where('church_id', $church->id)
            ->where('date', '>=', $fourWeeksAgo)
            ->selectRaw('YEARWEEK(date, 1) as week_key, MIN(date) as week_start, SUM(total_count) as total')
            ->groupBy('week_key')
            ->orderBy('week_key')
            ->get()
            ->keyBy('week_key');

        $attendanceData = [];
        for ($i = 3; $i >= 0; $i--) {
            $date = now()->subWeeks($i)->startOfWeek(Carbon::SUNDAY);
            $weekKey = $date->format('oW');
            $attendanceData[] = [
                'date' => $date->format('d.m'),
                'count' => $attendanceRaw[$weekKey]->total ?? 0,
            ];
        }

        return $attendanceData;
    }

    private function loadPendingAssignments($user)
    {
        if (!$user->person) {
            return collect();
        }

        return $user->person->assignments()
            ->where('status', 'pending')
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function loadNeedAttention($church)
    {
        $threeWeeksAgo = now()->subWeeks(3);

        return Person::where('church_id', $church->id)
            ->whereDoesntHave('attendanceRecords', function ($q) use ($threeWeeksAgo) {
                $q->whereHas('attendance', fn($aq) => $aq->where('date', '>=', $threeWeeksAgo))
                  ->where('present', true);
            })
            ->limit(5)
            ->get();
    }

    private function loadMinistryBudgets($church)
    {
        return $church->ministries()
            ->whereNotNull('monthly_budget')
            ->where('monthly_budget', '>', 0)
            ->with('expenses')
            ->get()
            ->map(fn($m) => [
                'name' => $m->name,
                'icon' => $m->icon ?? '⛪',
                'color' => $m->color,
                'budget' => $m->monthly_budget,
                'spent' => $m->spent_this_month,
                'percentage' => $m->budget_usage_percent,
            ]);
    }

    private function loadUrgentTasks($church)
    {
        $taskTracker = Board::where('church_id', $church->id)
            ->where('name', 'Трекер завдань')
            ->first();

        if (!$taskTracker) {
            return collect();
        }

        return BoardCard::whereHas('column', function ($q) use ($taskTracker) {
            $q->where('board_id', $taskTracker->id);
        })
        ->where('is_completed', false)
        ->where(function ($q) {
            $q->where('priority', 'urgent')
              ->orWhere('priority', 'high')
              ->orWhere(function ($dq) {
                  $dq->whereNotNull('due_date')
                     ->where('due_date', '<=', now()->addDays(2));
              });
        })
        ->with(['column', 'assignee'])
        ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 WHEN priority = 'high' THEN 1 ELSE 2 END")
        ->orderBy('due_date')
        ->limit(5)
        ->get();
    }

    private function loadFinancialData($church, array &$data): void
    {
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $financialRaw = Transaction::where('church_id', $church->id)
            ->completed()
            ->where('date', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, direction, SUM(amount) as total')
            ->groupBy('year', 'month', 'direction')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $financialGrouped = $financialRaw->groupBy(fn($item) => $item->year . '-' . $item->month);

        $financialData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year . '-' . $month->month;
            $monthData = $financialGrouped[$key] ?? collect();

            $financialData[] = [
                'month' => $month->translatedFormat('M'),
                'income' => $monthData->where('direction', 'in')->sum('total'),
                'expenses' => $monthData->where('direction', 'out')->sum('total'),
            ];
        }

        $data['financialData'] = $financialData;

        // Current month totals
        $currentKey = now()->year . '-' . now()->month;
        $currentMonthData = $financialGrouped[$currentKey] ?? collect();
        $data['stats']['income_this_month'] = $currentMonthData->where('direction', 'in')->sum('total');
    }

    private function loadExpensesBreakdown($church, array &$data): void
    {
        $data['stats']['expenses_this_month'] = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->completed()
            ->thisMonth()
            ->sum('amount');

        $data['expensesByCategory'] = Transaction::where('transactions.church_id', $church->id)
            ->outgoing()
            ->completed()
            ->thisMonth()
            ->leftJoin('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->selectRaw('transaction_categories.name as category_name, SUM(transactions.amount) as total_amount, COUNT(*) as transaction_count')
            ->groupBy('transactions.category_id', 'transaction_categories.name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($row) => [
                'name' => $row->category_name ?? 'Без категорії',
                'amount' => $row->total_amount,
                'count' => $row->transaction_count,
            ]);
    }

    private function loadGrowthData($church): array
    {
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $growthRaw = Person::where('church_id', $church->id)
            ->where('joined_date', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(joined_date) as year, MONTH(joined_date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        $growthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year . '-' . $month->month;
            $growthData[] = [
                'month' => $month->translatedFormat('M'),
                'count' => $growthRaw[$key]->count ?? 0,
            ];
        }

        return $growthData;
    }

    /**
     * Save dashboard layout (admin only).
     */
    public function saveLayout(Request $request)
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|string',
            'widgets.*.order' => 'required|integer|min:0',
            'widgets.*.cols' => 'required|integer|in:' . implode(',', self::ALLOWED_COLS),
            'widgets.*.enabled' => 'required|boolean',
        ]);

        $widgetRegistry = config('dashboard_widgets.widgets', []);

        // Filter to only valid widget IDs
        $widgets = collect($validated['widgets'])
            ->filter(fn($w) => isset($widgetRegistry[$w['id']]))
            ->map(fn($w) => [
                'id' => $w['id'],
                'order' => (int) $w['order'],
                'cols' => (int) $w['cols'],
                'enabled' => (bool) $w['enabled'],
            ])
            ->sortBy('order')
            ->values()
            ->toArray();

        $layout = [
            'version' => 1,
            'widgets' => $widgets,
        ];

        $church->setSetting('dashboard_layout', $layout);

        // Clear dashboard stats cache so layout changes take effect
        Cache::forget("dashboard_stats_{$church->id}");

        return response()->json(['success' => true]);
    }

    /**
     * Get birthdays for a specific month via AJAX
     */
    public function birthdays(Request $request)
    {
        $church = $this->getCurrentChurch();
        $month = (int) $request->get('month', now()->month);
        $month = max(1, min(12, $month));

        $people = Person::where('church_id', $church->id)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $month)
            ->get()
            ->sortBy(fn($p) => $p->birth_date->day)
            ->values()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->full_name,
                'initial' => mb_substr($p->first_name, 0, 1),
                'day' => $p->birth_date->format('d'),
                'month_short' => $p->birth_date->translatedFormat('M'),
                'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
                'url' => route('people.show', $p),
            ]);

        return response()->json([
            'count' => $people->count(),
            'people' => $people,
        ]);
    }

    /**
     * Get chart data via AJAX
     */
    public function chartData(Request $request)
    {
        $church = $this->getCurrentChurch();
        $type = $request->get('type', 'attendance');

        switch ($type) {
            case 'attendance':
                $data = $this->getAttendanceChartData($church);
                break;
            case 'growth':
                $data = $this->getGrowthChartData($church);
                break;
            case 'financial':
                $data = $this->getFinancialChartData($church);
                break;
            case 'ministries':
                $data = $this->getMinistriesChartData($church);
                break;
            default:
                $data = [];
        }

        return response()->json($data);
    }

    private function getAttendanceChartData($church): array
    {
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();
        $attendanceRaw = Attendance::where('church_id', $church->id)
            ->where('date', '>=', $twelveMonthsAgo)
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, AVG(total_count) as avg_count')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year . '-' . $month->month;
            $data[] = [
                'label' => $month->translatedFormat('M'),
                'value' => round($attendanceRaw[$key]->avg_count ?? 0),
            ];
        }
        return $data;
    }

    private function getGrowthChartData($church): array
    {
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();

        $cumulative = Person::where('church_id', $church->id)
            ->where('joined_date', '<', $twelveMonthsAgo)
            ->count();

        $growthRaw = Person::where('church_id', $church->id)
            ->where('joined_date', '>=', $twelveMonthsAgo)
            ->selectRaw('YEAR(joined_date) as year, MONTH(joined_date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year . '-' . $month->month;
            $joined = $growthRaw[$key]->count ?? 0;

            $cumulative += $joined;

            $data[] = [
                'label' => $month->translatedFormat('M'),
                'value' => $cumulative,
                'new' => $joined,
            ];
        }
        return $data;
    }

    private function getFinancialChartData($church): array
    {
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();

        $financialRaw = Transaction::where('church_id', $church->id)
            ->completed()
            ->where('date', '>=', $twelveMonthsAgo)
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, direction, SUM(amount) as total')
            ->groupBy('year', 'month', 'direction')
            ->get()
            ->groupBy(fn($item) => $item->year . '-' . $item->month);

        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year . '-' . $month->month;
            $monthData = $financialRaw[$key] ?? collect();

            $income = $monthData->where('direction', 'in')->sum('total');
            $expenses = $monthData->where('direction', 'out')->sum('total');

            $data[] = [
                'label' => $month->translatedFormat('M'),
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $income - $expenses,
            ];
        }
        return $data;
    }

    private function getMinistriesChartData($church): array
    {
        return $church->ministries()
            ->withCount('members')
            ->orderByDesc('members_count')
            ->limit(10)
            ->get()
            ->map(fn($m) => [
                'label' => $m->name,
                'value' => $m->members_count,
                'color' => $m->color ?? '#3b82f6',
            ])
            ->toArray();
    }
}
