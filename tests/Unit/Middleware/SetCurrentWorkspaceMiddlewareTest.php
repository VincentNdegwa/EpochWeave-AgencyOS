<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetCurrentWorkspace;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SetCurrentWorkspaceMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private SetCurrentWorkspace $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SetCurrentWorkspace();
    }

    public function test_middleware_sets_workspace_from_session(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(session()->driver());
        session(['current_workspace_id' => $workspace->id]);

        $response = $this->middleware->handle($request, fn () => new Response());

        $this->assertEquals($workspace->id, $request->attributes->get('current_workspace')->id);
        $this->assertNotEmpty($request->attributes->get('workspaces'));
    }

    public function test_middleware_sets_first_workspace_when_session_is_empty(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(session()->driver());

        $response = $this->middleware->handle($request, fn () => new Response());

        $this->assertEquals($workspace->id, $request->attributes->get('current_workspace')->id);
        $this->assertEquals($workspace->id, session('current_workspace_id'));
    }

    public function test_middleware_sets_null_when_user_has_no_workspaces(): void
    {
        $user = User::factory()->create();

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(session()->driver());

        $response = $this->middleware->handle($request, fn () => new Response());

        $this->assertNull($request->attributes->get('current_workspace'));
        $this->assertEmpty($request->attributes->get('workspaces'));
    }

    public function test_middleware_sets_null_when_user_is_not_authenticated(): void
    {
        $request = Request::create('/dashboard', 'GET');
        $request->setLaravelSession(session()->driver());

        $response = $this->middleware->handle($request, fn () => new Response());

        $this->assertNull($request->attributes->get('current_workspace'));
        $this->assertEmpty($request->attributes->get('workspaces'));
    }

    public function test_middleware_caches_workspace_id_in_session(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(session()->driver());

        $this->middleware->handle($request, fn () => new Response());

        $this->assertEquals($workspace->id, session('current_workspace_id'));
    }

    public function test_middleware_returns_all_user_workspaces(): void
    {
        $user = User::factory()->create();
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace1);
        $user->addRole($role, $workspace2);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(session()->driver());

        $this->middleware->handle($request, fn () => new Response());

        $workspaces = $request->attributes->get('workspaces');
        $this->assertCount(2, $workspaces);
        $this->assertTrue($workspaces->contains('id', $workspace1->id));
        $this->assertTrue($workspaces->contains('id', $workspace2->id));
    }
}
