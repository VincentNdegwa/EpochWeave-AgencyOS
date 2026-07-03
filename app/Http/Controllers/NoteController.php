<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Note;
use App\Models\Project;
use App\Models\Proposal;
use App\Services\NoteService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    public function __construct(
        private NoteService $noteService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $validated = $request->validate([
            'noteable_type' => ['required', 'string'],
            'noteable_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $noteable = $this->resolveNoteable(
            $validated['noteable_type'],
            $validated['noteable_id'],
            $workspace->id,
        );

        try {
            $this->noteService->createNote($noteable, [
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'body' => $validated['body'],
                'is_internal' => $validated['is_internal'] ?? false,
            ]);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($note->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        try {
            $this->noteService->updateNote($note, [
                'body' => $validated['body'],
                'is_internal' => $validated['is_internal'] ?? $note->is_internal,
            ]);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($note->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->noteService->deleteNote($note);

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }

    private function resolveNoteable(string $type, int $id, int $workspaceId): Model
    {
        $allowed = [
            'account' => Account::class,
            'proposal' => Proposal::class,
            'project' => Project::class,
            'invoice' => Invoice::class,
        ];

        $class = $allowed[$type] ?? null;

        if ($class === null) {
            throw ValidationException::withMessages([
                'noteable_type' => 'Invalid noteable type.',
            ]);
        }

        /** @var Model $model */
        $model = $class::where('workspace_id', $workspaceId)->find($id);

        if ($model === null) {
            abort(404);
        }

        return $model;
    }
}
