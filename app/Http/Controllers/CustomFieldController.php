<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomFieldException;
use App\Http\Requests\StoreCustomFieldDefinitionRequest;
use App\Http\Requests\StoreCustomFieldGroupRequest;
use App\Http\Requests\UpdateCustomFieldDefinitionRequest;
use App\Http\Requests\UpdateCustomFieldGroupRequest;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldGroup;
use App\Services\CustomFieldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomFieldController extends Controller
{
    public function __construct(private readonly CustomFieldService $customFieldService) {}

    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');
        $search = $request->input('search');
        $groups = $this->customFieldService->listGroupsForWorkspace($workspace->id, $search);

        return Inertia::render('setup/custom-field/index', [
            'groups' => $groups,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function storeGroup(StoreCustomFieldGroupRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        try {
            $data = array_merge($request->validated(), ['workspace_id' => $workspace->id]);
            $this->customFieldService->createGroup($data);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Field group created successfully.']);
        } catch (CustomFieldException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateGroup(UpdateCustomFieldGroupRequest $request, CustomFieldGroup $group): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($group->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->customFieldService->updateGroup($group, $request->validated());

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Field group updated successfully.']);
        } catch (CustomFieldException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroyGroup(Request $request, CustomFieldGroup $group): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($group->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->customFieldService->deleteGroup($group);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Field group deleted successfully.']);
        } catch (CustomFieldException $e) {
            return redirect()->back()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function storeDefinition(StoreCustomFieldDefinitionRequest $request): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $group = CustomFieldGroup::find($request->input('custom_field_group_id'));

        if (! $group || $group->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->customFieldService->createDefinition($request->validated());

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Field definition created successfully.']);
        } catch (CustomFieldException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateDefinition(UpdateCustomFieldDefinitionRequest $request, CustomFieldDefinition $definition): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($definition->group->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->customFieldService->updateDefinition($definition, $request->validated());

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Field definition updated successfully.']);
        } catch (CustomFieldException $e) {
            return redirect()->back()->withInput()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroyDefinition(Request $request, CustomFieldDefinition $definition): RedirectResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if ($definition->group->workspace_id !== $workspace->id) {
            abort(404);
        }

        try {
            $this->customFieldService->deleteDefinition($definition);

            return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Field definition deleted successfully.']);
        } catch (CustomFieldException $e) {
            return redirect()->back()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
