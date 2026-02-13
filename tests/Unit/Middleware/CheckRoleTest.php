<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckRole;
use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    private CheckRole $middleware;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CheckRole();
        $this->church = Church::factory()->create();
    }

    public function test_admin_always_has_access(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create([
            'is_admin_role' => true,
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->actingAs($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'some_role');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_redirects_unauthenticated_to_login(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'admin');

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_denies_user_without_required_role(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create([
            'slug' => 'member',
            'is_admin_role' => false,
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->actingAs($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'admin');
    }

    public function test_allows_user_with_matching_role_slug(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'leader')
            ->first();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->actingAs($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'leader');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_allows_when_no_roles_specified(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->actingAs($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }); // No roles specified

        $this->assertEquals(200, $response->getStatusCode());
    }
}
