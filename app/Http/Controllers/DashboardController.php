<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Person;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

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

        return view('dashboard.index', compact(
            'upcomingEvents',
            'stats',
            'attendanceData',
            'pendingAssignments',
            'needAttention',
            'ministryBudgets'
        ));
    }
}
