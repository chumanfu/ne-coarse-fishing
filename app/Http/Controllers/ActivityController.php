<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        $activities = Activity::query()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('activities.index', [
            'activities' => $activities,
        ]);
    }
}
