<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Exports\VolunteersExport;
use App\Exports\TransactionsExport;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->canView('reports')) {
            return redirect()->route('dashboard')->with('error', __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();

        // Quick stats
        $stats = [
            'total_members' => Person::where('church_id', $church->id)->count(),
            'active_members' => Person::where('church_id', $church->id)
                ->whereHas('attendanceRecords', fn($q) => $q->where('present', true)
                    ->whereHas('attendance', fn($aq) => $aq->where('date', '>=', now()->subMonths(3))))
                ->count(),
            'total_events' => Event::where('church_id', $church->id)->where('date', '>=', now()->startOfYear())->count(),
            'total_volunteers' => Person::where('church_id', $church->id)
                ->whereHas('ministries')
                ->count(),
        ];

        return view('reports.index', compact('stats'));
    }

    public function attendance(Request $request)
    {
        abort_unless(auth()->user()->canView('reports'), 403);
        $church = $this->getCurrentChurch();

        if (!$church->attendance_enabled) {
            abort(403, 'Функцію відвідуваності вимкнено для вашої церкви.');
        }

        $year = $request->get('year', now()->year);
        $ministryId = $request->get('ministry_id');

        // Monthly attendance data
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            // Count total attendance sessions
            $sessionsQuery = Attendance::where('church_id', $church->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $m);

            if ($ministryId) {
                $sessionsQuery->whereHas('attendable', fn($q) => $q->where('ministry_id', $ministryId));
            }

            // Count unique people who attended
            $attendanceIds = (clone $sessionsQuery)->pluck('id');
            $uniquePeople = AttendanceRecord::whereIn('attendance_id', $attendanceIds)
                ->where('present', true)
                ->distinct('person_id')
                ->count('person_id');

            $monthlyData[] = [
                'month' => Carbon::create($year, $m)->translatedFormat('M'),
                'count' => $sessionsQuery->sum('members_present'),
                'unique_people' => $uniquePeople,
            ];
        }

        // Attendance by weekday (filtered by ministry if selected)
        $weekdayQuery = Attendance::where('church_id', $church->id)
            ->whereYear('date', $year);
        if ($ministryId) {
            $weekdayQuery->whereHas('attendable', fn($q) => $q->where('ministry_id', $ministryId));
        }
        $weekdayData = (clone $weekdayQuery)
            ->selectRaw('DAYOFWEEK(date) as day, SUM(members_present) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Days starting from Monday (Ukrainian convention)
        $weekdays = [2 => __('app.day_mon'), 3 => __('app.day_tue'), 4 => __('app.day_wed'), 5 => __('app.day_thu'), 6 => __('app.day_fri'), 7 => __('app.day_sat'), 1 => __('app.day_sun')];
        $weekdayStats = [];
        foreach ($weekdays as $d => $label) {
            $weekdayStats[] = [
                'day' => $label,
                'count' => $weekdayData[$d] ?? 0,
            ];
        }

        // People who stopped attending (filtered by ministry if selected)
        $inactiveQuery = Person::where('church_id', $church->id);
        $attendanceFilter = function ($q) use ($ministryId) {
            $q->where('present', true)
                ->whereHas('attendance', function ($aq) use ($ministryId) {
                    if ($ministryId) {
                        $aq->whereHas('attendable', fn($mq) => $mq->where('ministry_id', $ministryId));
                    }
                });
        };
        $inactiveMembers = $inactiveQuery
            ->whereHas('attendanceRecords', fn($q) => $q->where('present', true)
                ->whereHas('attendance', function ($aq) use ($ministryId) {
                    $aq->where('date', '<', now()->subMonths(3));
                    if ($ministryId) {
                        $aq->whereHas('attendable', fn($mq) => $mq->where('ministry_id', $ministryId));
                    }
                }))
            ->whereDoesntHave('attendanceRecords', fn($q) => $q->where('present', true)
                ->whereHas('attendance', function ($aq) use ($ministryId) {
                    $aq->where('date', '>=', now()->subMonths(3));
                    if ($ministryId) {
                        $aq->whereHas('attendable', fn($mq) => $mq->where('ministry_id', $ministryId));
                    }
                }))
            ->with(['attendanceRecords' => fn($q) => $q->where('present', true)
                ->with('attendance')
                ->whereHas('attendance', fn($aq) => $aq->orderByDesc('date'))
                ->limit(1)])
            ->take(20)
            ->get();

        // Top attendees (filtered by ministry if selected)
        $topAttendees = Person::where('church_id', $church->id)
            ->whereHas('attendanceRecords', fn($q) => $q->where('present', true)
                ->whereHas('attendance', function ($aq) use ($year, $ministryId) {
                    $aq->whereYear('date', $year);
                    if ($ministryId) {
                        $aq->whereHas('attendable', fn($mq) => $mq->where('ministry_id', $ministryId));
                    }
                }))
            ->withCount(['attendanceRecords' => fn($q) => $q->where('present', true)
                ->whereHas('attendance', function ($aq) use ($year, $ministryId) {
                    $aq->whereYear('date', $year);
                    if ($ministryId) {
                        $aq->whereHas('attendable', fn($mq) => $mq->where('ministry_id', $ministryId));
                    }
                })])
            ->orderByDesc('attendance_records_count')
            ->take(10)
            ->get();

        $ministries = $church->ministries()->orderBy('name')->get();

        return view('reports.attendance', compact(
            'monthlyData', 'weekdayStats', 'inactiveMembers', 'topAttendees',
            'ministries', 'year', 'ministryId'
        ));
    }

    public function finances(Request $request)
    {
        abort_unless(auth()->user()->canView('finances'), 403);
        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);

        // Monthly income vs expenses
        $monthlyData = [];
        $cumulativeBalance = 0;
        for ($m = 1; $m <= 12; $m++) {
            $income = Transaction::where('church_id', $church->id)
                ->incoming()
                ->completed()
                ->whereYear('date', $year)
                ->whereMonth('date', $m)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0;

            $expense = Transaction::where('church_id', $church->id)
                ->outgoing()
                ->completed()
                ->whereYear('date', $year)
                ->whereMonth('date', $m)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0;

            $cumulativeBalance += ($income - $expense);

            $monthlyData[] = [
                'month' => Carbon::create($year, $m)->translatedFormat('M'),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'balance' => (float) ($income - $expense),
                'cumulative' => $cumulativeBalance,
            ];
        }

        // Year over year comparison
        $currentYear = Transaction::where('church_id', $church->id)
            ->completed()
            ->whereYear('date', $year)
            ->selectRaw("
                SUM(CASE WHEN direction = 'in' THEN COALESCE(amount_uah, amount) ELSE 0 END) as income,
                SUM(CASE WHEN direction = 'out' THEN COALESCE(amount_uah, amount) ELSE 0 END) as expense
            ")
            ->first();

        $prevYear = Transaction::where('church_id', $church->id)
            ->completed()
            ->whereYear('date', $year - 1)
            ->selectRaw("
                SUM(CASE WHEN direction = 'in' THEN COALESCE(amount_uah, amount) ELSE 0 END) as income,
                SUM(CASE WHEN direction = 'out' THEN COALESCE(amount_uah, amount) ELSE 0 END) as expense
            ")
            ->first();

        $comparison = [
            'current' => [
                'income' => (float) ($currentYear->income ?? 0),
                'expense' => (float) ($currentYear->expense ?? 0),
            ],
            'previous' => [
                'income' => (float) ($prevYear->income ?? 0),
                'expense' => (float) ($prevYear->expense ?? 0),
            ],
        ];

        // Income by category
        $incomeByCategory = Transaction::where('church_id', $church->id)
            ->incoming()
            ->completed()
            ->whereYear('date', $year)
            ->with('category')
            ->selectRaw('category_id, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        // Expense by ministry
        $expenseByMinistry = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->completed()
            ->whereYear('date', $year)
            ->with('ministry')
            ->selectRaw('ministry_id, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('ministry_id')
            ->orderByDesc('total')
            ->get();

        return view('reports.finances', compact(
            'monthlyData', 'comparison', 'incomeByCategory', 'expenseByMinistry', 'year'
        ));
    }

    public function volunteers(Request $request)
    {
        abort_unless(auth()->user()->canView('reports'), 403);
        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);

        // Top volunteers by assignments
        $topVolunteers = Person::where('church_id', $church->id)
            ->whereHas('assignments', fn($q) => $q->whereHas('event', fn($e) => $e->whereYear('date', $year)))
            ->withCount(['assignments' => fn($q) => $q->whereHas('event', fn($e) => $e->whereYear('date', $year))])
            ->orderByDesc('assignments_count')
            ->take(15)
            ->get();

        // Monthly volunteer activity
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $assignments = Assignment::whereHas('event', fn($q) => $q
                ->where('church_id', $church->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $m)
            )->count();

            $uniqueVolunteers = Assignment::whereHas('event', fn($q) => $q
                ->where('church_id', $church->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $m)
            )->distinct('person_id')->count('person_id');

            $monthlyData[] = [
                'month' => Carbon::create($year, $m)->translatedFormat('M'),
                'assignments' => $assignments,
                'volunteers' => $uniqueVolunteers,
            ];
        }

        // Volunteer distribution by ministry
        $byMinistry = Assignment::whereHas('event', fn($q) => $q
            ->where('church_id', $church->id)
            ->whereYear('date', $year)
        )
        ->join('events', 'assignments.event_id', '=', 'events.id')
        ->join('ministries', 'events.ministry_id', '=', 'ministries.id')
        ->whereNull('events.deleted_at')
        ->whereNull('ministries.deleted_at')
        ->selectRaw('ministries.name, ministries.color, COUNT(*) as count')
        ->groupBy('ministries.id', 'ministries.name', 'ministries.color')
        ->orderByDesc('count')
        ->get();

        // People who haven't volunteered in 3+ months
        $inactiveVolunteers = Person::where('church_id', $church->id)
            ->whereHas('assignments', fn($q) => $q->whereHas('event', fn($e) => $e->where('date', '<', now()->subMonths(3))))
            ->whereDoesntHave('assignments', fn($q) => $q->whereHas('event', fn($e) => $e->where('date', '>=', now()->subMonths(3))))
            ->take(20)
            ->get();

        return view('reports.volunteers', compact(
            'topVolunteers', 'monthlyData', 'byMinistry', 'inactiveVolunteers', 'year'
        ));
    }

    public function exportFinances(Request $request)
    {
        abort_unless(auth()->user()->canView('reports'), 403);
        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);
        $filename = "finances-{$year}.xlsx";

        return Excel::download(new TransactionsExport($church->id, $year), $filename);
    }

    public function exportAttendance(Request $request)
    {
        abort_unless(auth()->user()->canView('reports'), 403);
        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);
        $filename = "attendance-{$year}.xlsx";

        return Excel::download(new AttendanceExport($church->id, $year), $filename);
    }

    public function exportVolunteers(Request $request)
    {
        abort_unless(auth()->user()->canView('reports'), 403);
        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);
        $filename = "volunteers-{$year}.xlsx";

        return Excel::download(new VolunteersExport($church->id, $year), $filename);
    }
}
