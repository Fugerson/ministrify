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
    public function index()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();
        $cacheKey = "dashboard_stats_{$church->id}";

        // Birthdays this month
        $birthdaysThisMonth = Person::where('church_id', $church->id)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->get()
            ->sortBy(fn($p) => $p->birth_date->day);

        // Upcoming events (next 7 days)
        $upcomingEvents = Event::where('church_id', $church->id)
            ->where('date', '>=', now()->startOfDay())
            ->where('date', '<=', now()->addDays(7))
            ->with(['ministry', 'assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->orderBy('time')
            ->limit(5)
            ->get();

        // Cache heavy statistics for 30 minutes
        $cachedStats = Cache::remember($cacheKey, 1800, function () use ($church) {
            $peopleQuery = Person::where('church_id', $church->id);
            $today = now();
            $threeMonthsAgo = now()->subMonths(3);
            $ministryIds = $church->ministries()->pluck('id');

            // People stats
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

            // Age statistics
            $ageStats = [
                'children' => (clone $peopleQuery)->whereNotNull('birth_date')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) <= 12', [$today])->count(),
                'teens' => (clone $peopleQuery)->whereNotNull('birth_date')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 13 AND 17', [$today])->count(),
                'youth' => (clone $peopleQuery)->whereNotNull('birth_date')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 18 AND 35', [$today])->count(),
                'adults' => (clone $peopleQuery)->whereNotNull('birth_date')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 36 AND 59', [$today])->count(),
                'seniors' => (clone $peopleQuery)->whereNotNull('birth_date')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) >= 60', [$today])->count(),
            ];

            // Trends
            $peopleTrend = Person::where('church_id', $church->id)
                ->where('created_at', '>=', $threeMonthsAgo)->count();
            $volunteersThreeMonthsAgo = \DB::table('ministry_person')
                ->whereIn('ministry_id', $ministryIds)
                ->where('created_at', '<', $threeMonthsAgo)
                ->distinct('person_id')->count('person_id');

            // Ministry stats
            $ministriesList = $church->ministries()->withCount('members')->orderByDesc('members_count')->get();
            $activeVolunteers = \DB::table('ministry_person')
                ->whereIn('ministry_id', $ministryIds)
                ->distinct('person_id')->count('person_id');
            $ministriesWithEvents = $church->ministries()
                ->whereHas('events', fn($q) => $q->where('date', '>=', now()))->count();

            // Group stats
            $activeGroupIds = Group::where('church_id', $church->id)->where('status', 'active')->pluck('id');
            $totalGroups = Group::where('church_id', $church->id)->count();
            $activeGroups = $activeGroupIds->count();
            $pausedGroups = Group::where('church_id', $church->id)->where('status', 'paused')->count();
            $vacationGroups = Group::where('church_id', $church->id)->where('status', 'vacation')->count();
            $totalGroupMembers = $activeGroups > 0
                ? \DB::table('group_person')->whereIn('group_id', $activeGroupIds)->distinct('person_id')->count('person_id')
                : 0;

            // Event stats
            $eventsThisMonth = Event::where('church_id', $church->id)
                ->whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
            $upcomingEventsCount = Event::where('church_id', $church->id)
                ->where('date', '>=', now())->where('date', '<=', now()->endOfMonth())->count();

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

        // Use cached stats
        $stats = $cachedStats;

        // Expenses this month (for admins)
        // Expenses breakdown by category (for admins)
        $expensesByCategory = collect();
        if ($user->isAdmin()) {
            $stats['expenses_this_month'] = Transaction::where('church_id', $church->id)
                ->outgoing()
                ->completed()
                ->thisMonth()
                ->sum('amount');

            // Optimized: aggregate at DB level instead of loading all transactions
            $expensesByCategory = Transaction::where('transactions.church_id', $church->id)
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

        // Attendance chart data (last 4 weeks) - optimized single query
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
            $weekKey = $date->format('oW'); // ISO week format
            $attendanceData[] = [
                'date' => $date->format('d.m'),
                'count' => $attendanceRaw[$weekKey]->total ?? 0,
            ];
        }

        // Pending assignments (for volunteers)
        $pendingAssignments = [];
        if ($user->person) {
            $pendingAssignments = $user->person->assignments()
                ->where('status', 'pending')
                ->with(['event.ministry', 'position'])
                ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // People needing attention (not attended for 3+ weeks)
        $needAttention = [];
        if ($user->isAdmin()) {
            $threeWeeksAgo = now()->subWeeks(3);

            $needAttention = Person::where('church_id', $church->id)
                ->whereDoesntHave('attendanceRecords', function ($q) use ($threeWeeksAgo) {
                    $q->whereHas('attendance', fn($aq) => $aq->where('date', '>=', $threeWeeksAgo))
                      ->where('present', true);
                })
                ->limit(5)
                ->get();
        }

        // Ministry budget status (for admins)
        $ministryBudgets = [];
        if ($user->isAdmin()) {
            $ministryBudgets = $church->ministries()
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

        // Get main task tracker board
        $taskTracker = Board::where('church_id', $church->id)
            ->where('name', 'Трекер завдань')
            ->first();

        // Urgent & overdue tasks (only from main tracker)
        $urgentTasks = collect();
        if ($taskTracker) {
            $urgentTasks = BoardCard::whereHas('column', function ($q) use ($taskTracker) {
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

        // Growth stats (for admins) - optimized single query
        $growthData = [];
        if ($user->isAdmin()) {
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
            $growthRaw = Person::where('church_id', $church->id)
                ->where('joined_date', '>=', $sixMonthsAgo)
                ->selectRaw('YEAR(joined_date) as year, MONTH(joined_date) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(fn($item) => $item->year . '-' . $item->month);

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $key = $month->year . '-' . $month->month;
                $growthData[] = [
                    'month' => $month->translatedFormat('M'),
                    'count' => $growthRaw[$key]->count ?? 0,
                ];
            }
        }

        // Financial overview (for admins) - optimized single query
        $financialData = [];
        if ($user->isAdmin()) {
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
            $financialRaw = Transaction::where('church_id', $church->id)
                ->completed()
                ->where('date', '>=', $sixMonthsAgo)
                ->selectRaw('YEAR(date) as year, MONTH(date) as month, direction, SUM(amount) as total')
                ->groupBy('year', 'month', 'direction')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Group by year-month
            $financialGrouped = $financialRaw->groupBy(fn($item) => $item->year . '-' . $item->month);

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

            // Current month totals - already in financialData
            $currentKey = now()->year . '-' . now()->month;
            $currentMonthData = $financialGrouped[$currentKey] ?? collect();
            $stats['income_this_month'] = $currentMonthData->where('direction', 'in')->sum('total');
        }

        return view('dashboard.index', compact(
            'upcomingEvents',
            'stats',
            'attendanceData',
            'pendingAssignments',
            'needAttention',
            'ministryBudgets',
            'birthdaysThisMonth',
            'urgentTasks',
            'growthData',
            'financialData',
            'expensesByCategory'
        ));
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
        // Optimized: single query with groupBy instead of 12 queries
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
        // Optimized: single query with groupBy instead of 12 queries
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
        // Optimized: single query with groupBy instead of 24 queries
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
