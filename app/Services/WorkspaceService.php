<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class WorkspaceService
{
    public function createWorkspace(User $user, array $data): Workspace
    {
        return DB::transaction(function () use ($user, $data) {
            $workspace = Workspace::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? null,
                'description' => $data['description'] ?? null,
                'currency' => $data['currency'] ?? null,
                'white_label' => $data['white_label'] ?? false,
                'domain' => $data['domain'] ?? null,
                'logo_url' => $data['logo_url'] ?? null,
                'primary_color' => $data['primary_color'] ?? null,
            ]);

            $adminRole = Role::updateOrCreate(
                ['name' => 'admin'],
                [
                    'display_name' => 'Administrator',
                    'description' => 'System administrator with full access',
                    'workspace_id' => $workspace->id,
                ]
            );

            $user->addRole($adminRole, $workspace);

            return $workspace;
        });
    }

    public function switchWorkspace(User $user, Workspace $workspace): void
    {
        if (!$user->rolesTeams()->where('id', $workspace->id)->exists()) {
            abort(403, 'You do not have access to this workspace.');
        }

        session(['current_workspace_id' => $workspace->id]);
    }
}
