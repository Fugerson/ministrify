<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckPermission;
use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class CheckPermissionTest extends TestCase
{
    use RefreshDatabase;

    private CheckPermission $middleware;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CheckPermission();
        $this->church = Church::factory()->create();
    }

    public function test_allows_user_with_permission(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        ChurchRolePermission::create([
            'church_role_id' => $role->id,
            'module' => 'people',
            'actions' => ['view'],
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'people', 'view');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_denies_user_without_permission(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'people', 'delete');
    }

    public function test_returns_401_for_unauthenticated(): void
    {
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => null);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'people', 'view');
    }

    public function test_returns_json_for_ajax_request(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'people', 'delete');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function test_default_action_is_view(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        ChurchRolePermission::create([
            'church_role_id' => $role->id,
            'module' => 'people',
            'actions' => ['view'],
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'people'); // No action specified — should default to 'view'

        $this->assertEquals(200, $response->getStatusCode());
    }
}
