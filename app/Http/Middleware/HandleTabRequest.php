<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleTabRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if this is a tab AJAX request
        if ($request->header('X-Tab-Request') === '1' && $response->getStatusCode() === 200) {
            $content = $response->getContent();

            // Extract content from finance-content div
            if (preg_match('/<div id="finance-content">(.*?)<\/div><!-- \/finance-content -->/s', $content, $matches)) {
                return response($matches[1])->header('Content-Type', 'text/html');
            }

            // Fallback: try to extract everything after tabs and before scripts
            if (preg_match('/<div id="finance-content">(.*)/s', $content, $matches)) {
                $extracted = $matches[1];
                // Find the closing comment marker
                $pos = strpos($extracted, '</div><!-- /finance-content -->');
                if ($pos !== false) {
                    return response(substr($extracted, 0, $pos))->header('Content-Type', 'text/html');
                }
            }
        }

        return $response;
    }
}
