<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SanitizeInput;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SanitizeInputTest extends TestCase
{
    private SanitizeInput $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SanitizeInput();
    }

    public function test_removes_null_bytes(): void
    {
        $request = Request::create('/test', 'POST', [
            'name' => "Test\0Name",
        ]);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('TestName', $req->input('name'));
            return new Response('OK');
        });
    }

    public function test_trims_whitespace(): void
    {
        $request = Request::create('/test', 'POST', [
            'name' => '  Test Name  ',
        ]);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('Test Name', $req->input('name'));
            return new Response('OK');
        });
    }

    public function test_removes_control_characters(): void
    {
        $request = Request::create('/test', 'POST', [
            'name' => "Test\x01\x02Name",
        ]);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('TestName', $req->input('name'));
            return new Response('OK');
        });
    }

    public function test_preserves_newlines_and_tabs(): void
    {
        $request = Request::create('/test', 'POST', [
            'notes' => "Line 1\nLine 2\tTabbed",
        ]);

        $this->middleware->handle($request, function ($req) {
            $this->assertStringContainsString("\n", $req->input('notes'));
            $this->assertStringContainsString("\t", $req->input('notes'));
            return new Response('OK');
        });
    }

    public function test_does_not_sanitize_password_fields(): void
    {
        $request = Request::create('/test', 'POST', [
            'password' => "Test\0Password",
            'password_confirmation' => "Test\0Password",
            'current_password' => "Old\0Pass",
            'new_password' => "New\0Pass",
        ]);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals("Test\0Password", $req->input('password'));
            $this->assertEquals("Test\0Password", $req->input('password_confirmation'));
            $this->assertEquals("Old\0Pass", $req->input('current_password'));
            $this->assertEquals("New\0Pass", $req->input('new_password'));
            return new Response('OK');
        });
    }

    public function test_sanitizes_nested_input(): void
    {
        $request = Request::create('/test', 'POST', [
            'data' => [
                'name' => "  Nested\0Value  ",
            ],
        ]);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('NestedValue', $req->input('data.name'));
            return new Response('OK');
        });
    }
}
