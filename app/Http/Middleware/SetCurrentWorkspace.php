<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentWorkspace
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = null;
        $workspaces = [];

        if ($request->user()) {
            $workspaces = method_exists($request->user(), 'rolesTeams') && $request->user()->rolesTeams()
                ? $request->user()->rolesTeams()->get()
                : collect();

            if ($workspaces->isEmpty() && method_exists($request->user(), 'workspaces')) {
                $workspaces = $request->user()->workspaces()->get();
            }

            if ($request->session()->has('current_workspace_id')) {
                $workspace = Workspace::find($request->session()->get('current_workspace_id'));
            }

            if (! $workspace && $workspaces->isNotEmpty()) {
                $workspace = $workspaces->first();
                $request->session()->put('current_workspace_id', $workspace->id);
            }
        }

        $request->attributes->set('current_workspace', $workspace);
        $request->attributes->set('workspaces', $workspaces);

        return $next($request);
    }
}
