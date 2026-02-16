<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to protect against common attacks.
     * Uses values from config/security.php
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $config = config('security.headers');

        // Prevent clickjacking - don't allow embedding in iframes
        $response->headers->set('X-Frame-Options', $config['x_frame_options'] ?? 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', $config['x_content_type_options'] ?? 'nosniff');

        // Enable XSS protection in browsers
        $response->headers->set('X-XSS-Protection', $config['x_xss_protection'] ?? '1; mode=block');

        // Referrer policy - don't leak full URL to other sites
        $response->headers->set('Referrer-Policy', $config['referrer_policy'] ?? 'strict-origin-when-cross-origin');

        // Permissions policy - restrict browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy - prevent XSS and injection attacks
        // Note: unsafe-eval needed for Tailwind CDN on landing pages
        if (config('app.env') === 'production') {
            // Telegram Mini App needs telegram.org scripts and to be framed by Telegram
            if ($request->is('telegram/app')) {
                $response->headers->set('Content-Security-Policy',
                    "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://telegram.org https://cdn.jsdelivr.net; " .
                    "style-src 'self' 'unsafe-inline'; " .
                    "connect-src 'self'; " .
                    "img-src 'self' data: blob:; " .
                    "frame-ancestors https://web.telegram.org https://desktop.telegram.org https://macos.telegram.org tg:; " .
                    "base-uri 'self'; " .
                    "object-src 'none';"
                );
                $response->headers->remove('X-Frame-Options');
            } else {
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; " .
                "img-src 'self' data: blob:; " .
                "connect-src 'self' https://cdn.jsdelivr.net wss:; " .
                "frame-src https://www.google.com; " .
                "manifest-src 'self'; " .
                "worker-src 'self' blob:; " .
                "frame-ancestors 'self'; " .
                "base-uri 'self'; " .
                "form-action 'self'; " .
                "object-src 'none';"
            );
            }

            // Force HTTPS in production - use config values
            $hstsMaxAge = $config['hsts_max_age'] ?? 31536000;
            $hstsIncludeSubdomains = ($config['hsts_include_subdomains'] ?? true) ? '; includeSubDomains' : '';
            $response->headers->set('Strict-Transport-Security', "max-age={$hstsMaxAge}{$hstsIncludeSubdomains}; preload");
        }

        return $response;
    }
}
