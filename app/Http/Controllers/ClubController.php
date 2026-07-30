<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(Request $request): View
    {
        $clubs = Club::query()
            ->published()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('town', 'like', $q)
                        ->orWhere('overview', 'like', $q);
                });
            })
            ->ordered()
            ->paginate(18)
            ->withQueryString();

        return view('clubs.index', [
            'clubs' => $clubs,
            'filters' => $request->only(['q']),
        ]);
    }

    public function show(Club $club): View
    {
        abort_unless($club->is_published, 404);

        return view('clubs.show', [
            'club' => $club,
        ]);
    }
}
