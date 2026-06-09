<?php

namespace App\Http\Controllers;

use App\PortalInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalSetupController extends Controller
{
    public function __construct(
        private PortalInvitationService $invitationService
    ) {}

    public function show(string $token)
    {
        $invitation = $this->invitationService->getInvitationByToken($token);

        if (! $invitation || ! $invitation->isValid()) {
            abort(404, 'Invalid or expired invitation link.');
        }

        return Inertia::render('portal/setup', [
            'token' => $token,
            'email' => $invitation->accountContact->email,
            'company_name' => $invitation->account->company_name,
        ]);
    }

    public function complete(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->invitationService->completeSetup($token, $request->password);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Account setup complete. Please log in with your email and password.']);

            return redirect()->route('login');
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
