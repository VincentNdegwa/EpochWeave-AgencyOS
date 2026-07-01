<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Models\AccountContact;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalTemplate;
use App\Models\User;
use App\Services\AccountService;
use App\Services\CompanySizeService;
use App\Services\IndustryService;
use App\Services\LeadSourceService;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderDataController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductUnitService $productUnitService,
        private AccountService $accountService,
        private IndustryService $industryService,
        private LeadSourceService $leadSourceService,
        private CompanySizeService $companySizeService,
    ) {}

    public function products(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $products = $this->productService->getActiveProductsByWorkspace($workspace->id);

        return response()->json($products);
    }

    public function units(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $units = $this->productUnitService->getProductUnitsByWorkspace($workspace->id);

        return response()->json($units);
    }

    public function templates(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $templates = ProposalTemplate::where('workspace_id', $workspace->id)->get();

        return response()->json($templates);
    }

    public function template(Request $request, ProposalTemplate $template): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        // Ensure the template belongs to the current workspace
        if ($template->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        return response()->json($template);
    }

    public function accounts(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');
        $accounts = $this->accountService->getAccountsByWorkspace($workspace->id)
            ->where('status', '!=', AccountStatus::Archived->value);

        return response()->json($accounts);
    }

    public function users(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $users = User::whereHas('rolesTeams', function ($query) use ($workspace) {
            $query->where('workspace_id', $workspace->id);
        })
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function accountContacts(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $query = AccountContact::whereHas('account', function ($query) use ($workspace) {
            $query->where('workspace_id', $workspace->id);
        })
            ->with(['account:id,company_name'])
            ->select(['id', 'account_id', 'first_name', 'last_name', 'email', 'phone', 'job_title', 'is_primary']);

        if ($request->has('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }

        $contacts = $query->orderBy('first_name')->get();

        return response()->json($contacts);
    }

    public function projects(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $query = Project::where('workspace_id', $workspace->id)
            ->select(['id', 'account_id', 'name', 'project_status_id', 'currency'])
            ->orderBy('name');

        if ($request->has('account_id')) {
            $query->where('account_id', $request->query('account_id'));
        }

        return response()->json($query->get());
    }

    public function proposals(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $query = Proposal::where('workspace_id', $workspace->id)
            ->select(['id', 'account_id', 'title', 'proposal_number', 'proposal_status_id'])
            ->orderBy('title');

        if ($request->has('account_id')) {
            $query->where('account_id', $request->query('account_id'));
        }

        return response()->json($query->get());
    }

    public function invoices(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        $query = Invoice::where('workspace_id', $workspace->id)
            ->select(['id', 'account_id', 'invoice_number', 'grand_total', 'invoice_status_id'])
            ->orderBy('invoice_number');

        if ($request->has('account_id')) {
            $query->where('account_id', $request->query('account_id'));
        }

        return response()->json($query->get());
    }

    public function industries(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        return response()->json($this->industryService->listForWorkspace($workspace->id));
    }

    public function leadSources(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        return response()->json($this->leadSourceService->listForWorkspace($workspace->id));
    }

    public function companySizes(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        return response()->json($this->companySizeService->listForWorkspace($workspace->id));
    }
}
