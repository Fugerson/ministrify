<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Board;
use App\Models\BoardCard;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Income;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

        // Birthdays this week
        $birthdaysThisWeek = Person::where('church_id', $church->id)
            ->whereNotNull('birth_date')
            ->get()
            ->filter(function ($person) {
                $birthday = $person->birth_date->copy()->year(now()->year);
                return $birthday->between(now()->startOfDay(), now()->addDays(7));
            })
            ->sortBy(fn($p) => $p->birth_date->copy()->year(now()->year))
            ->take(5);

        // Upcoming events (next 7 days)
        $upcomingEvents = Event::where('church_id', $church->id)
            ->where('date', '>=', now()->startOfDay())
            ->where('date', '<=', now()->addDays(7))
            ->with(['ministry', 'assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->orderBy('time')
            ->limit(5)
            ->get();

        // Stats
        $stats = [
            'total_people' => Person::where('church_id', $church->id)->count(),
            'total_ministries' => $church->ministries()->count(),
            'total_groups' => Group::where('church_id', $church->id)->where('is_active', true)->count(),
            'events_this_month' => Event::where('church_id', $church->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
        ];

        // Expenses this month (for admins)
        if ($user->isAdmin()) {
            $stats['expenses_this_month'] = Expense::where('church_id', $church->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('amount');
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
                    'icon' => $m->icon,
                    'budget' => $m->monthly_budget,
                    'spent' => $m->spent_this_month,
                    'percentage' => $m->budget_usage_percent,
                ]);
        }

        // Boards with stats
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', false)
            ->withCount(['columns', 'cards'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        // Urgent & overdue tasks
        $urgentTasks = BoardCard::whereHas('column.board', function ($q) use ($church) {
            $q->where('church_id', $church->id)->where('is_archived', false);
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
            ->with(['column.board', 'assignee'])
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 WHEN priority = 'high' THEN 1 ELSE 2 END")
            ->orderBy('due_date')
            ->limit(5)
            ->get();

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

                $income = Income::where('church_id', $church->id)
                    ->whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->sum('amount');

                $expenses = Expense::where('church_id', $church->id)
                    ->whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->sum('amount');

                $financialData[] = [
                    'month' => $month->translatedFormat('M'),
                    'income' => $income,
                    'expenses' => $expenses,
                ];
            }

            // Current month totals
            $stats['income_this_month'] = Income::where('church_id', $church->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('amount');
        }

        return view('dashboard.index', compact(
            'upcomingEvents',
            'stats',
            'attendanceData',
            'pendingAssignments',
            'needAttention',
            'ministryBudgets',
            'birthdaysThisWeek',
            'boards',
            'urgentTasks',
            'growthData',
            'financialData'
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

            $income = Income::where('church_id', $church->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');

            $expenses = Expense::where('church_id', $church->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');

            $data[] = [
                'label' => $month->translatedFormat('M'),
                'income' => $income,
                'expenses' => $expenses,
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
