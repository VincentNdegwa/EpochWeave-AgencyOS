<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceMemberController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $members = User::whereHas('roles', function ($query) use ($workspace) {
            $query->where('role_user.workspace_id', $workspace->id);
        })
            ->with(['roles' => function ($query) use ($workspace) {
                $query->where('role_user.workspace_id', $workspace->id);
            }])
            ->select(['id', 'name', 'email', 'created_at'])
            ->get();

        $roles = DB::table('roles')
            ->whereNull('workspace_id')
            ->orWhere('workspace_id', $workspace->id)
            ->select(['id', 'name', 'display_name'])
            ->get();

        return Inertia::render('workspace-settings/Members', [
            'members' => $members,
            'roles' => $roles,
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['email'],
            'email' => $validated['email'],
            'password' => bcrypt(str()->random(16)),
        ]);

        $user->roles()->attach($validated['role_id'], ['workspace_id' => $workspace->id]);

        return redirect()->back();
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $user->roles()->where('role_user.workspace_id', $workspace->id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->roles()->detach($user->roles()->wherePivot('workspace_id', $workspace->id)->pluck('roles.id'));
        $user->roles()->attach($validated['role_id'], ['workspace_id' => $workspace->id]);

        return redirect()->back();
    }

    public function remove(Request $request, User $user): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $user->roles()->wherePivot('workspace_id', $workspace->id)->detach();

        return redirect()->back();
    }
}
