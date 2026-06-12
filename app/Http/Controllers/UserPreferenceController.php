<?php

namespace App\Http\Controllers;

use App\Services\UserPreferenceService;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function __construct(
        private UserPreferenceService $userPreferenceService
    ) {}

    public function updateDisplayMode(Request $request)
    {
        $request->validate([
            'display_mode' => 'required|in:list,kanban',
        ]);

        $workspace = $request->attributes->get('current_workspace');
        $user = $request->user();

        $this->userPreferenceService->setDisplayMode(
            $workspace->id,
            $user->id,
            $request->display_mode
        );

        return back();
    }
}
