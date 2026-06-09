<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        if (! $request->user()?->hasRole('admin', $workspace)) {
            abort(403);
        }

        $validated = $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $uploads = [];

        /** @var array<string, UploadedFile> $files */
        $files = $validated['files'];

        foreach ($files as $key => $file) {
            $existing = $request->session()->get("temporary_uploads.{$key}");

            if (is_string($existing) && $existing !== '') {
                Storage::disk('public')->delete($existing);
            }

            $path = $file->store('uploads/tmp/'.$request->user()->id, 'public');

            $request->session()->put("temporary_uploads.{$key}", $path);

            $uploads[$key] = Storage::disk('public')->url($path);
        }

        return response()->json($uploads);
    }

    public function destroy(Request $request, string $key): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace instanceof Workspace) {
            abort(404);
        }

        if (! $request->user()?->hasRole('admin', $workspace)) {
            abort(403);
        }

        $path = $request->session()->pull("temporary_uploads.{$key}");

        if (is_string($path) && $path !== '') {
            Storage::disk('public')->delete($path);
        }

        return response()->json();
    }
}
