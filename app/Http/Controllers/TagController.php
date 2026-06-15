<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function __construct(private readonly TagService $tagService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $tags = $this->tagService->listForWorkspace($workspace->id);

        return Inertia::render('tags/index', [
            'tags' => $tags,
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->tagService->createTag($data);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag created successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($tag->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->tagService->updateTag($tag, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag updated successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Tag $tag): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($tag->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->tagService->deleteTag($tag);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Tag deleted successfully.']);

            return redirect()->back();
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
