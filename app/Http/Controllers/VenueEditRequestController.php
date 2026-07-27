<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\View\View;

class VenueEditRequestController extends Controller
{
    public function create(Venue $venue): View
    {
        $this->authorize('suggestEdit', $venue);

        return view('venues.suggest-edit', compact('venue'));
    }
}
