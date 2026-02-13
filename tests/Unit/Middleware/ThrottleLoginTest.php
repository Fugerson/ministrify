<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\ThrottleLogin;
use App\Services\SecurityAlertService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class ThrottleLoginTest extends TestCase
{
    public function test_allows_request_under_limit(): void
    {
        $limiter = Mockery::mock(RateLimiter::class);
        $limiter->shouldReceive('tooManyAttempts')->andReturn(false);
        $limiter->shouldReceive('hit')->zeroOrMoreTimes();
        $limiter->shouldReceive('clear')->zeroOrMoreTimes();

        $alertService = Mockery::mock(SecurityAlertService::class);

        $middleware = new ThrottleLogin($limiter, $alertService);

        $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);

        $response = $middleware->handle($request, function () {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_when_too_many_attempts(): void
    {
        $limiter = Mockery::mock(RateLimiter::class);
        $limiter->shouldReceive('tooManyAttempts')->andReturn(true);
        $limiter->shouldReceive('availableIn')->andReturn(45);

        $alertService = Mockery::mock(SecurityAlertService::class);
        $alertService->shouldReceive('alert')->once();

        $middleware = new ThrottleLogin($limiter, $alertService);

        $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);

        $response = $middleware->handle($request, function () {
            return new Response('OK', 200);
        });

        // Should return 302 (redirect back) or show error
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_returns_json_when_throttled_and_expects_json(): void
    {
        $limiter = Mockery::mock(RateLimiter::class);
        $limiter->shouldReceive('tooManyAttempts')->andReturn(true);
        $limiter->shouldReceive('availableIn')->andReturn(30);

        $alertService = Mockery::mock(SecurityAlertService::class);
        $alertService->shouldReceive('alert')->once();

        $middleware = new ThrottleLogin($limiter, $alertService);

        $request = Request::create('/login', 'POST', ['email' => 'test@test.com'], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $middleware->handle($request, function () {
            return new Response('OK', 200);
        });

        $this->assertEquals(429, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('retry_after', $data);
        $this->assertEquals(30, $data['retry_after']);
    }

    public function test_clears_limiter_on_successful_login(): void
    {
        $limiter = Mockery::mock(RateLimiter::class);
        $limiter->shouldReceive('tooManyAttempts')->andReturn(false);
        $limiter->shouldReceive('clear')->once();

        $alertService = Mockery::mock(SecurityAlertService::class);

        $middleware = new ThrottleLogin($limiter, $alertService);

        $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);

        $response = $middleware->handle($request, function () {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_increments_on_failed_login(): void
    {
        $limiter = Mockery::mock(RateLimiter::class);
        $limiter->shouldReceive('tooManyAttempts')->andReturn(false);
        $limiter->shouldReceive('hit')->once()->with(Mockery::type('string'), 60);

        $alertService = Mockery::mock(SecurityAlertService::class);

        $middleware = new ThrottleLogin($limiter, $alertService);

        $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);

        $response = $middleware->handle($request, function () {
            return new Response('Validation error', 422);
        });

        $this->assertEquals(422, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
