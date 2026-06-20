<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $activities = Activity::where('workspace_id', $workspace->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('activities/Index', [
            'activities' => $activities,
        ]);
    }
}
