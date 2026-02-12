<?php

namespace App\Http\Middleware;

use App\Services\SecurityAlertService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DetectSuspiciousActivity
{
    protected SecurityAlertService $alertService;

    protected array $sqlPatterns = [
        '/union\s+(all\s+)?select/i',
        '/\bor\b\s+\d+=\d+/i',
        "/'\s*or\s+'.*'='/i",
        '/drop\s+table/i',
        '/;\s*--/',
        '/sleep\s*\(/i',
        '/benchmark\s*\(/i',
        '/load_file\s*\(/i',
        '/into\s+(out|dump)file/i',
        '/information_schema/i',
        '/concat\s*\(/i',
        '/group_by\s*\(/i',
        '/char\s*\(\d+/i',
    ];

    protected array $xssPatterns = [
        '/<script[\s>]/i',
        '/javascript\s*:/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        '/onmouseover\s*=/i',
        '/onfocus\s*=/i',
        '/eval\s*\(/i',
        '/document\.(cookie|location|write)/i',
        '/\balert\s*\(/i',
        '/<iframe[\s>]/i',
        '/<svg[\s>].*on\w+\s*=/i',
    ];

    protected array $pathTraversalPatterns = [
        '/\.\.\//i',
        '/\.\.\\\\/i',
        '/\/etc\/passwd/i',
        '/\/proc\/self/i',
        '/\/etc\/shadow/i',
        '/\/var\/log/i',
    ];

    protected array $scannerPaths = [
        '/wp-admin',
        '/wp-login',
        '/wp-login.php',
        '/wp-content',
        '/wp-includes',
        '/xmlrpc.php',
        '/phpmyadmin',
        '/pma',
        '/myadmin',
        '/.env',
        '/.git',
        '/.git/config',
        '/admin.php',
        '/shell',
        '/cmd',
        '/cgi-bin',
        '/config.php',
        '/setup.php',
        '/install.php',
        '/vendor/.env',
        '/.aws/credentials',
        '/actuator',
        '/solr',
        '/console',
        '/telescope',
    ];

    public function __construct(SecurityAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $path = strtolower($request->path());
        $fullUrl = $request->fullUrl();
        $input = $this->getRequestInput($request);

        // Check for scanner/bot paths
        if ($this->isScanner($path)) {
            $this->alertService->alert('scanner', 'Scanner detected', [
                'ip' => $ip,
                'url' => $fullUrl,
                'details' => "Запрос к: /{$path}",
            ]);

            return response('', 403);
        }

        // Check for SQL injection
        $sqlMatch = $this->matchesPatterns($input, $this->sqlPatterns);
        if ($sqlMatch) {
            $this->alertService->alert('sql_injection', 'SQL Injection attempt', [
                'ip' => $ip,
                'url' => $fullUrl,
                'details' => "Паттерн: {$sqlMatch}",
            ]);

            return response('', 403);
        }

        // Check for XSS
        $xssMatch = $this->matchesPatterns($input, $this->xssPatterns);
        if ($xssMatch) {
            $this->alertService->alert('xss', 'XSS attempt', [
                'ip' => $ip,
                'url' => $fullUrl,
                'details' => "Паттерн: {$xssMatch}",
            ]);

            return response('', 403);
        }

        // Check for path traversal
        $pathMatch = $this->matchesPatterns($input, $this->pathTraversalPatterns);
        if ($pathMatch) {
            $this->alertService->alert('path_traversal', 'Path traversal attempt', [
                'ip' => $ip,
                'url' => $fullUrl,
                'details' => "Паттерн: {$pathMatch}",
            ]);

            return response('', 403);
        }

        // Process the request
        $response = $next($request);

        // Track mass 404s
        if ($response->getStatusCode() === 404) {
            $this->track404($ip, $fullUrl);
        }

        return $response;
    }

    protected function isScanner(string $path): bool
    {
        $normalizedPath = '/' . ltrim($path, '/');

        foreach ($this->scannerPaths as $scannerPath) {
            if ($normalizedPath === $scannerPath || str_starts_with($normalizedPath, $scannerPath . '/')) {
                return true;
            }
        }

        return false;
    }

    protected function getRequestInput(Request $request): string
    {
        $parts = [];

        // Query string
        if ($request->getQueryString()) {
            $parts[] = urldecode($request->getQueryString());
        }

        // Request body (only for POST/PUT/PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $sensitiveFields = [
                '_token', '_method', 'password', 'password_confirmation',
                'current_password', 'new_password', 'credit_card', 'card_number',
                'cvv', 'cvc', 'token', 'secret', 'api_key',
            ];
            foreach ($request->except($sensitiveFields) as $value) {
                if (is_string($value)) {
                    $parts[] = $value;
                }
            }
        }

        // URL path
        $parts[] = urldecode($request->path());

        return implode(' ', $parts);
    }

    protected function matchesPatterns(string $input, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    protected function track404(string $ip, string $url): void
    {
        $cacheKey = "404_count:{$ip}";
        $max404 = config('security.alerts.max_404_per_minute', 10);

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 0, 60);
        }
        $count = Cache::increment($cacheKey);

        if ($count === $max404) {
            $this->alertService->alert('mass_404', 'Mass 404 detected', [
                'ip' => $ip,
                'url' => $url,
                'details' => "Более {$max404} запросов 404 за минуту",
            ]);
        }
    }
}
