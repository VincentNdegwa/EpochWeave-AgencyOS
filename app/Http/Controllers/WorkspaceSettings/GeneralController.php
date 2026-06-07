<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceSettings\UpdateWorkspaceGeneralSettingsRequest;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GeneralController extends Controller
{
    public function edit(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        if (! $request->user()?->rolesTeams()->where('id', $workspace->id)->exists()) {
            abort(403);
        }

        $canUpdate = $request->user()?->hasRole('admin', $workspace) ?? false;

        return Inertia::render('workspace-settings/General', [
            'canUpdateWorkspaceSettings' => $canUpdate,
        ]);
    }

    public function update(UpdateWorkspaceGeneralSettingsRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        $validated = $request->validated();

        $logoUrl = $validated['logo_url'] ?? null;

        if (is_string($logoUrl) && $logoUrl !== '') {
            $logoUrl = $this->finalizeTemporaryUpload($request, $workspace, 'logo', $logoUrl) ?? $logoUrl;
        }

        if (! $logoUrl && $workspace->logo_url) {
            $this->deleteWorkspaceFileIfLocal($workspace, $workspace->logo_url);
        }

        if ($logoUrl && $workspace->logo_url && $workspace->logo_url !== $logoUrl) {
            $this->deleteWorkspaceFileIfLocal($workspace, $workspace->logo_url);
        }

        $workspace->fill([
            'name' => $validated['name'],
            'display_name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'currency' => $validated['currency'] ?? null,
            'white_label' => $validated['white_label'],
            'logo_url' => $logoUrl ?: null,
            'primary_color' => $validated['primary_color'] ?? null,
        ]);

        $workspace->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workspace updated.')]);

        return to_route('workspace-settings.general');
    }

    private function finalizeTemporaryUpload(Request $request, Workspace $workspace, string $key, string $url): ?string
    {
        $path = $request->session()->get("temporary_uploads.{$key}");

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! Str::startsWith($path, 'uploads/tmp/')) {
            return null;
        }

        $expectedUrl = Storage::disk('public')->url($path);

        if ($url !== $expectedUrl) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $fileName = Str::uuid().($extension ? ".{$extension}" : '');
        $newPath = "uploads/workspaces/{$workspace->id}/{$key}/{$fileName}";

        Storage::disk('public')->move($path, $newPath);
        $request->session()->forget("temporary_uploads.{$key}");

        return Storage::disk('public')->url($newPath);
    }

    private function deleteWorkspaceFileIfLocal(Workspace $workspace, string $url): void
    {
        $prefix = Storage::disk('public')->url('');

        if (! Str::startsWith($url, $prefix)) {
            return;
        }

        $path = Str::after($url, $prefix);

        if (! Str::startsWith($path, "uploads/workspaces/{$workspace->id}/")) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
