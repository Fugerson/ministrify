<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to protect against common attacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking - don't allow embedding in iframes
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection in browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy - don't leak full URL to other sites
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy - restrict browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy - prevent XSS and injection attacks
        // Note: Alpine.js 3.x works without unsafe-eval
        if (config('app.env') === 'production') {
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; " .
                "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; " .
                "img-src 'self' data: https: blob:; " .
                "connect-src 'self' https://cdn.jsdelivr.net wss: https:; " .
                "manifest-src 'self'; " .
                "worker-src 'self' blob:; " .
                "frame-ancestors 'self';"
            );

            // Force HTTPS in production
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
