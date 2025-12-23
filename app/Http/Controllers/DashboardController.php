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

class DashboardController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

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

        // Detailed People Stats
        $allPeople = Person::where('church_id', $church->id)->get();
        $totalPeople = $allPeople->count();
        $leadersCount = Person::where('church_id', $church->id)
            ->where(function ($q) {
                $q->whereHas('leadingMinistries')
                  ->orWhereHas('leadingGroups');
            })->count();
        $volunteersCount = Person::where('church_id', $church->id)
            ->whereHas('ministries')
            ->count();
        $newThisMonth = Person::where('church_id', $church->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Age statistics
        $ageStats = [
            'children' => $allPeople->filter(fn($p) => $p->age !== null && $p->age <= 12)->count(),
            'teens' => $allPeople->filter(fn($p) => $p->age !== null && $p->age >= 13 && $p->age <= 17)->count(),
            'youth' => $allPeople->filter(fn($p) => $p->age !== null && $p->age >= 18 && $p->age <= 35)->count(),
            'adults' => $allPeople->filter(fn($p) => $p->age !== null && $p->age >= 36 && $p->age <= 59)->count(),
            'seniors' => $allPeople->filter(fn($p) => $p->age !== null && $p->age >= 60)->count(),
        ];

        // People trend (last 3 months)
        $threeMonthsAgo = now()->subMonths(3);
        $newPeopleLastThreeMonths = Person::where('church_id', $church->id)
            ->where('created_at', '>=', $threeMonthsAgo)
            ->count();
        $peopleTrend = $newPeopleLastThreeMonths;

        // Volunteers trend (last 3 months) - count people who joined ministries
        $volunteersThreeMonthsAgo = \DB::table('ministry_person')
            ->whereIn('ministry_id', $church->ministries()->pluck('id'))
            ->where('created_at', '<', $threeMonthsAgo)
            ->distinct('person_id')
            ->count('person_id');
        $volunteersTrend = $volunteersCount - $volunteersThreeMonthsAgo;

        // Detailed Ministry Stats
        $ministriesList = $church->ministries()->withCount('members')->orderByDesc('members_count')->get();
        $totalMinistries = $ministriesList->count();
        $activeVolunteers = \DB::table('ministry_person')
            ->whereIn('ministry_id', $church->ministries()->pluck('id'))
            ->distinct('person_id')
            ->count('person_id');
        $ministriesWithEvents = $church->ministries()
            ->whereHas('events', fn($q) => $q->where('date', '>=', now()))
            ->count();

        // Detailed Group Stats
        $activeGroupIds = Group::where('church_id', $church->id)->where('status', 'active')->pluck('id');
        $totalGroups = Group::where('church_id', $church->id)->count();
        $activeGroups = $activeGroupIds->count();
        $pausedGroups = Group::where('church_id', $church->id)->where('status', 'paused')->count();
        $vacationGroups = Group::where('church_id', $church->id)->where('status', 'vacation')->count();
        $totalGroupMembers = $activeGroups > 0
            ? \DB::table('group_person')->whereIn('group_id', $activeGroupIds)->distinct('person_id')->count('person_id')
            : 0;

        // Detailed Event Stats
        $eventsThisMonth = Event::where('church_id', $church->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
        $upcomingEventsCount = Event::where('church_id', $church->id)
            ->where('date', '>=', now())
            ->where('date', '<=', now()->endOfMonth())
            ->count();
        $pastEventsThisMonth = $eventsThisMonth - $upcomingEventsCount;

        $stats = [
            'total_people' => $totalPeople,
            'leaders_count' => $leadersCount,
            'volunteers_count' => $volunteersCount,
            'new_people_this_month' => $newThisMonth,
            'people_trend' => $peopleTrend,
            'volunteers_trend' => $volunteersTrend,
            'age_stats' => $ageStats,
            'total_ministries' => $totalMinistries,
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
            'past_events' => $pastEventsThisMonth,
        ];

        // Expenses this month (for admins)
        // Expenses breakdown by category (for admins)
        $expensesByCategory = collect();
        if ($user->isAdmin()) {
            $stats['expenses_this_month'] = Transaction::where('church_id', $church->id)
                ->outgoing()
                ->completed()
                ->thisMonth()
                ->sum('amount');

            $expensesByCategory = Transaction::where('church_id', $church->id)
                ->outgoing()
                ->completed()
                ->thisMonth()
                ->with('category')
                ->get()
                ->groupBy('category_id')
                ->map(function ($transactions, $categoryId) {
                    $category = $transactions->first()->category;
                    return [
                        'name' => $category?->name ?? 'Без категорії',
                        'amount' => $transactions->sum('amount'),
                        'count' => $transactions->count(),
                    ];
                })
                ->sortByDesc('amount')
                ->values();
        }

        // Attendance chart data (last 4 weeks)
        $attendanceData = [];
        for ($i = 3; $i >= 0; $i--) {
            $date = now()->subWeeks($i)->startOfWeek(Carbon::SUNDAY);
            $attendance = Attendance::where('church_id', $church->id)
                ->whereBetween('date', [$date, $date->copy()->endOfWeek()])
                ->sum('total_count');

            $attendanceData[] = [
                'date' => $date->format('d.m'),
                'count' => $attendance,
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

        // Growth stats (for admins)
        $growthData = [];
        if ($user->isAdmin()) {
            // Members joined per month (last 6 months)
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $count = Person::where('church_id', $church->id)
                    ->whereYear('joined_date', $month->year)
                    ->whereMonth('joined_date', $month->month)
                    ->count();

                $growthData[] = [
                    'month' => $month->translatedFormat('M'),
                    'count' => $count,
                ];
            }
        }

        // Financial overview (for admins)
        $financialData = [];
        if ($user->isAdmin()) {
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);

                $income = Transaction::where('church_id', $church->id)
                    ->incoming()
                    ->completed()
                    ->forMonth($month->year, $month->month)
                    ->sum('amount');

                $expenses = Transaction::where('church_id', $church->id)
                    ->outgoing()
                    ->completed()
                    ->forMonth($month->year, $month->month)
                    ->sum('amount');

                $financialData[] = [
                    'month' => $month->translatedFormat('M'),
                    'income' => $income,
                    'expenses' => $expenses,
                ];
            }

            // Current month totals
            $stats['income_this_month'] = Transaction::where('church_id', $church->id)
                ->incoming()
                ->completed()
                ->thisMonth()
                ->sum('amount');
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
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $attendance = Attendance::where('church_id', $church->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->avg('total_count') ?? 0;

            $data[] = [
                'label' => $month->translatedFormat('M'),
                'value' => round($attendance),
            ];
        }
        return $data;
    }

    private function getGrowthChartData($church): array
    {
        $data = [];
        $cumulative = Person::where('church_id', $church->id)
            ->where('joined_date', '<', now()->subMonths(11)->startOfMonth())
            ->count();

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $joined = Person::where('church_id', $church->id)
                ->whereYear('joined_date', $month->year)
                ->whereMonth('joined_date', $month->month)
                ->count();

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
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $income = Transaction::where('church_id', $church->id)
                ->incoming()
                ->completed()
                ->forMonth($month->year, $month->month)
                ->sum('amount');

            $expenses = Transaction::where('church_id', $church->id)
                ->outgoing()
                ->completed()
                ->forMonth($month->year, $month->month)
                ->sum('amount');

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
