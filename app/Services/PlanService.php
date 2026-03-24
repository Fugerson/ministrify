<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Event;
use App\Models\Person;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlanService
{
    public function getUsage(Church $church): array
    {
        return Cache::remember("church:{$church->id}:plan_usage", 300, function () use ($church) {
            return [
                'people' => Person::where('church_id', $church->id)->count(),
                'users' => DB::table('church_user')->where('church_id', $church->id)->count(),
                'ministries' => $church->ministries()->count(),
                'groups' => $church->groups()->count(),
                'events_per_month' => Event::where('church_id', $church->id)
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month)
                    ->count(),
            ];
        });
    }

    public function checkLimit(Church $church, string $resource): array
    {
        $usage = $this->getUsage($church);
        $limit = $church->getPlanLimit($resource);
        $current = $usage[$resource] ?? 0;

        return [
            'allowed' => $limit === -1 || $current < $limit,
            'current' => $current,
            'limit' => $limit,
            'unlimited' => $limit === -1,
            'percentage' => $limit > 0 ? min(100, round($current / $limit * 100)) : 0,
        ];
    }

    public function canCreate(Church $church, string $resource): bool
    {
        return $this->checkLimit($church, $resource)['allowed'];
    }

    public function getWarnings(Church $church): array
    {
        $warnings = [];
        $usage = $this->getUsage($church);

        foreach ($usage as $resource => $current) {
            $limit = $church->getPlanLimit($resource);
            if ($limit === -1 || $limit === 0) {
                continue;
            }

            $percentage = round($current / $limit * 100);

            if ($percentage >= 80) {
                $warnings[] = [
                    'resource' => $resource,
                    'current' => $current,
                    'limit' => $limit,
                    'percentage' => min(100, $percentage),
                    'over' => $current >= $limit,
                ];
            }
        }

        return $warnings;
    }

    public function changePlan(Church $church, string $plan): void
    {
        $church->update([
            'plan' => $plan,
            'plan_changed_at' => now(),
        ]);

        Cache::forget("church:{$church->id}:plan_usage");
    }

    public static function clearUsageCache(int $churchId): void
    {
        Cache::forget("church:{$churchId}:plan_usage");
    }

    public static function getAllPlans(): array
    {
        return config('plans.plans', []);
    }

    public static function getPlan(string $slug): ?array
    {
        return config("plans.plans.{$slug}");
    }
}
