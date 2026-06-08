<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProposalTemplateRequest;
use App\Http\Requests\UpdateProposalTemplateRequest;
use App\Services\ProposalTemplateService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalTemplateController extends Controller
{
    public function __construct(
        private ProposalTemplateService $templateService
    ) {}

    public function index(Request $request)
    {
        $workspace = $request->attributes->get('current_workspace');
        $templates = $this->templateService->getTemplatesByWorkspace($workspace->id);
        $defaultTemplate = $this->templateService->getDefaultTemplate($workspace->id);

        return Inertia::render('proposal-templates/index', [
            'templates' => $templates,
            'defaultTemplate' => $defaultTemplate,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('proposal-templates/create');
    }

    public function store(StoreProposalTemplateRequest $request): RedirectResponse
    {
        try {
            $workspace = $request->attributes->get('current_workspace');
            $data = $request->validated();

            $data = array_merge($data, [
                'workspace_id' => $workspace->id,
                'is_default' => false,
            ]);

            $template = $this->templateService->createTemplate($data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Template created successfully.']);

            return redirect()->route('proposal-templates.show', $template->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function show(int $id)
    {
        $template = $this->templateService->getTemplateById($id);

        if (! $template) {
            abort(404);
        }

        return Inertia::render('proposal-templates/show', [
            'template' => $template,
        ]);
    }

    public function edit(int $id)
    {
        $template = $this->templateService->getTemplateById($id);

        if (! $template) {
            abort(404);
        }

        return Inertia::render('proposal-templates/edit', [
            'template' => $template,
        ]);
    }

    public function update(UpdateProposalTemplateRequest $request, int $id): RedirectResponse
    {
        try {
            $template = $this->templateService->getTemplateById($id);

            if (! $template) {
                abort(404);
            }

            $data = $request->validated();

            $this->templateService->updateTemplate($template, $data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Template updated successfully.']);

            return redirect()->route('proposal-templates.show', $template->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $template = $this->templateService->getTemplateById($id);

            if (! $template) {
                abort(404);
            }

            $this->templateService->deleteTemplate($template);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Template deleted successfully.']);

            return redirect()->route('proposal-templates.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function setDefault(int $id): RedirectResponse
    {
        try {
            $template = $this->templateService->getTemplateById($id);

            if (! $template) {
                abort(404);
            }

            $this->templateService->setDefaultTemplate($template);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Template set as default successfully.']);

            return redirect()->route('proposal-templates.index');
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function duplicate(int $id, Request $request): RedirectResponse
    {
        try {
            $template = $this->templateService->getTemplateById($id);

            if (! $template) {
                abort(404);
            }

            $newName = $request->input('name', $template->name . ' (Copy)');

            $newTemplate = $this->templateService->duplicateTemplate($template, $newName);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Template duplicated successfully.']);

            return redirect()->route('proposal-templates.show', $newTemplate->id);
        } catch (Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
