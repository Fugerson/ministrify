<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Only log GET requests (page views)
        if ($request->method() !== 'GET') {
            return;
        }

        // Only for authenticated users
        $user = $request->user();
        if (!$user) {
            return;
        }

        // Skip super admins — they are invisible
        if ($user->isSuperAdmin()) {
            return;
        }

        // Skip AJAX/fetch requests
        if ($request->ajax() || $request->wantsJson()) {
            return;
        }

        // Skip asset and system routes
        $path = $request->path();
        if ($this->shouldSkip($path)) {
            return;
        }

        // Skip non-successful responses
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 300) {
            return;
        }

        try {
            PageVisit::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'church_id' => $user->church_id,
                'url' => $request->fullUrl(),
                'route_name' => $request->route()?->getName(),
                'method' => 'GET',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail — logging should never break the app
        }
    }

    private function shouldSkip(string $path): bool
    {
        $skipPrefixes = [
            '_debugbar',
            'livewire',
            'sanctum',
            'telescope',
            'horizon',
        ];

        foreach ($skipPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        $skipExtensions = ['.js', '.css', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf'];
        foreach ($skipExtensions as $ext) {
            if (str_ends_with($path, $ext)) {
                return true;
            }
        }

        return false;
    }
}
