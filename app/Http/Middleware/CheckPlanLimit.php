<?php

namespace App\Http\Middleware;

use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    public function __construct(protected PlanService $planService)
    {
    }

    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $church = $request->attributes->get('currentChurch');

        if (!$church) {
            return $next($request);
        }

        $check = $this->planService->checkLimit($church, $resource);

        if (!$check['allowed']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.plan_limit_reached', [
                        'resource' => $resource,
                        'limit' => $check['limit'],
                    ]),
                    'upgrade_url' => url('/pricing'),
                ], 403);
            }

            return back()->with('error', __('messages.plan_limit_reached', [
                'resource' => $resource,
                'limit' => $check['limit'],
            ]));
        }

        return $next($request);
    }
}
