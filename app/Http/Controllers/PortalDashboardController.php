<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PortalDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $client = Auth::guard('client')->user();

        if (! $client) {
            abort(404);
        }

        $contactIds = $client->contacts()->pluck('id');

        $proposals = Proposal::whereIn('account_contact_id', $contactIds)
            ->with('proposalStatus:id,title,color')
            ->select(['id', 'title', 'proposal_status_id', 'grand_total', 'sent_at', 'accepted_at', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $invoices = Invoice::whereIn('account_contact_id', $contactIds)
            ->with('status:id,title,color')
            ->select(['id', 'invoice_number', 'invoice_status_id', 'grand_total', 'amount_paid', 'issue_date', 'due_date', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('portal/Dashboard', [
            'proposals' => $proposals,
            'invoices' => $invoices,
        ]);
    }
}
