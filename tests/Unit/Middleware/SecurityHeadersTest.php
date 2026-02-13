<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    private SecurityHeaders $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeaders();
    }

    public function test_sets_x_frame_options(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    public function test_sets_x_content_type_options(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_sets_x_xss_protection(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    public function test_sets_referrer_policy(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    public function test_sets_permissions_policy(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('camera=(), microphone=(), geolocation=()', $response->headers->get('Permissions-Policy'));
    }

    public function test_sets_csp_in_production(): void
    {
        config(['app.env' => 'production']);

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);

        // Reset
        config(['app.env' => 'testing']);
    }

    public function test_sets_hsts_in_production(): void
    {
        config(['app.env' => 'production']);

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $hsts = $response->headers->get('Strict-Transport-Security');
        $this->assertNotNull($hsts);
        $this->assertStringContainsString('max-age=', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);

        config(['app.env' => 'testing']);
    }

    public function test_no_csp_in_non_production(): void
    {
        config(['app.env' => 'testing']);

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertNull($response->headers->get('Content-Security-Policy'));
    }
}
