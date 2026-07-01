<?php

namespace App\Http\Controllers;

use App\Exceptions\SocialProfileException;
use App\Http\Requests\StoreSocialProfileRequest;
use App\Http\Requests\UpdateSocialProfileRequest;
use App\Models\SocialProfile;
use App\Services\SocialProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SocialProfileController extends Controller
{
    public function __construct(private readonly SocialProfileService $socialProfileService) {}

    public function index(Request $request)
    {
        $type = $request->query('profileable_type');
        $id = $request->query('profileable_id');

        if (! $type || ! $id) {
            abort(400);
        }

        $profiles = $this->socialProfileService->listForEntity($type, (int) $id);

        return Inertia::render('social-profile/index', [
            'social_profiles' => $profiles,
        ]);
    }

    public function store(StoreSocialProfileRequest $request): RedirectResponse
    {
        try {
            $this->socialProfileService->createSocialProfile($request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Social profile added successfully.']);

            return redirect()->back();
        } catch (SocialProfileException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateSocialProfileRequest $request, SocialProfile $socialProfile): RedirectResponse
    {
        try {
            $this->socialProfileService->updateSocialProfile($socialProfile, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Social profile updated successfully.']);

            return redirect()->back();
        } catch (SocialProfileException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(SocialProfile $socialProfile): RedirectResponse
    {
        try {
            $this->socialProfileService->deleteSocialProfile($socialProfile);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Social profile deleted successfully.']);

            return redirect()->back();
        } catch (SocialProfileException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
