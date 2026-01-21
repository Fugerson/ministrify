<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Church;
use App\Models\Event;
use App\Models\Person;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardStatisticsService
{
    private int $cacheTtl = 1800; // 30 minutes

    /**
     * Get all dashboard statistics for a church
     */
    public function getStatistics(Church $church): array
    {
        $cacheKey = "dashboard_stats_{$church->id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($church) {
            return [
                'people' => $this->getPeopleStats($church),
                'age' => $this->getAgeStats($church),
                'ministries' => $this->getMinistryStats($church),
                'groups' => $this->getGroupStats($church),
                'events' => $this->getEventStats($church),
            ];
        });
    }

    /**
     * Get people statistics
     */
    public function getPeopleStats(Church $church): array
    {
        $query = Person::where('church_id', $church->id);
        $threeMonthsAgo = now()->subMonths(3);
        $ministryIds = $church->ministries()->pluck('id');

        $totalPeople = (clone $query)->count();

        $leadersCount = (clone $query)
            ->where(function ($q) {
                $q->whereHas('leadingMinistries')
                    ->orWhereHas('leadingGroups');
            })->count();

        $volunteersCount = (clone $query)->whereHas('ministries')->count();

        $newThisMonth = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $peopleTrend = Person::where('church_id', $church->id)
            ->where('created_at', '>=', $threeMonthsAgo)->count();

        $volunteersThreeMonthsAgo = DB::table('ministry_person')
            ->whereIn('ministry_id', $ministryIds)
            ->where('created_at', '<', $threeMonthsAgo)
            ->distinct('person_id')->count('person_id');

        return [
            'total' => $totalPeople,
            'leaders' => $leadersCount,
            'volunteers' => $volunteersCount,
            'new_this_month' => $newThisMonth,
            'trend' => $peopleTrend,
            'volunteers_trend' => $volunteersCount - $volunteersThreeMonthsAgo,
        ];
    }

    /**
     * Get age statistics using optimized single query
     */
    public function getAgeStats(Church $church): array
    {
        $today = now();

        $stats = DB::table('people')
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

        return [
            'children' => (int) ($stats->children ?? 0),
            'teens' => (int) ($stats->teens ?? 0),
            'youth' => (int) ($stats->youth ?? 0),
            'adults' => (int) ($stats->adults ?? 0),
            'seniors' => (int) ($stats->seniors ?? 0),
        ];
    }

    /**
     * Get ministry statistics
     */
    public function getMinistryStats(Church $church): array
    {
        $ministryIds = $church->ministries()->pluck('id');

        $ministriesList = $church->ministries()
            ->withCount('members')
            ->orderByDesc('members_count')
            ->get();

        $activeVolunteers = DB::table('ministry_person')
            ->whereIn('ministry_id', $ministryIds)
            ->distinct('person_id')
            ->count('person_id');

        $ministriesWithEvents = $church->ministries()
            ->whereHas('events', fn($q) => $q->where('date', '>=', now()))
            ->count();

        return [
            'total' => $ministriesList->count(),
            'list' => $ministriesList,
            'active_volunteers' => $activeVolunteers,
            'with_upcoming_events' => $ministriesWithEvents,
        ];
    }

    /**
     * Get group statistics using optimized single query
     */
    public function getGroupStats(Church $church): array
    {
        $stats = DB::table('groups')
            ->where('church_id', $church->id)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'paused' THEN 1 ELSE 0 END) as paused,
                SUM(CASE WHEN status = 'vacation' THEN 1 ELSE 0 END) as vacation
            ")
            ->first();

        $totalMembers = ($stats->active ?? 0) > 0
            ? DB::table('group_person')
                ->join('groups', 'group_person.group_id', '=', 'groups.id')
                ->where('groups.church_id', $church->id)
                ->where('groups.status', 'active')
                ->whereNull('groups.deleted_at')
                ->distinct('group_person.person_id')
                ->count('group_person.person_id')
            : 0;

        return [
            'total' => (int) ($stats->total ?? 0),
            'active' => (int) ($stats->active ?? 0),
            'paused' => (int) ($stats->paused ?? 0),
            'vacation' => (int) ($stats->vacation ?? 0),
            'total_members' => $totalMembers,
        ];
    }

    /**
     * Get event statistics using optimized single query
     */
    public function getEventStats(Church $church): array
    {
        $stats = DB::table('events')
            ->where('church_id', $church->id)
            ->whereNull('deleted_at')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN date >= ? THEN 1 ELSE 0 END) as upcoming
            ", [now()->toDateString()])
            ->first();

        return [
            'this_month' => (int) ($stats->total ?? 0),
            'upcoming' => (int) ($stats->upcoming ?? 0),
            'past' => (int) ($stats->total ?? 0) - (int) ($stats->upcoming ?? 0),
        ];
    }

    /**
     * Get expense statistics for admins
     */
    public function getExpenseStats(Church $church): array
    {
        $total = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->completed()
            ->thisMonth()
            ->sum('amount');

        $byCategory = Transaction::where('transactions.church_id', $church->id)
            ->outgoing()
            ->completed()
            ->thisMonth()
            ->leftJoin('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->selectRaw('transaction_categories.name as category_name, SUM(transactions.amount) as total_amount, COUNT(*) as count')
            ->groupBy('transactions.category_id', 'transaction_categories.name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($row) => [
                'name' => $row->category_name ?? 'Без категорії',
                'amount' => $row->total_amount,
                'count' => $row->count,
            ]);

        return [
            'total' => $total,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get attendance chart data for last N weeks
     */
    public function getAttendanceChartData(Church $church, int $weeks = 4): array
    {
        $startDate = now()->subWeeks($weeks - 1)->startOfWeek(Carbon::SUNDAY);

        $data = Attendance::where('church_id', $church->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('YEARWEEK(date, 1) as week_key, MIN(date) as week_start, SUM(total_count) as total')
            ->groupBy('week_key')
            ->orderBy('week_key')
            ->get()
            ->keyBy('week_key');

        $chartData = [];
        $currentWeek = $startDate->copy();

        for ($i = 0; $i < $weeks; $i++) {
            $weekKey = $currentWeek->format('oW');
            $chartData[] = [
                'label' => $currentWeek->format('d.m'),
                'value' => (int) ($data[$weekKey]->total ?? 0),
            ];
            $currentWeek->addWeek();
        }

        return $chartData;
    }

    /**
     * Get people needing attention (no attendance in N weeks)
     */
    public function getPeopleNeedingAttention(Church $church, int $weeks = 3): \Illuminate\Database\Eloquent\Collection
    {
        $cutoffDate = now()->subWeeks($weeks);

        return Person::where('church_id', $church->id)
            ->whereDoesntHave('attendanceRecords', function ($q) use ($cutoffDate) {
                $q->whereHas('attendance', fn($aq) => $aq->where('date', '>=', $cutoffDate))
                    ->where('present', true);
            })
            ->whereIn('membership_status', [Person::STATUS_MEMBER, Person::STATUS_ACTIVE])
            ->orderBy('last_name')
            ->limit(20)
            ->get();
    }

    /**
     * Clear cached statistics for a church
     */
    public function clearCache(Church $church): void
    {
        Cache::forget("dashboard_stats_{$church->id}");
    }
}
