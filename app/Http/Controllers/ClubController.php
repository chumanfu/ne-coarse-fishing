<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Services\ClubPersistenceService;
use Illuminate\Http\RedirectResponse;
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
        abort_unless(
            $club->is_published
                || (auth()->user() && ($club->isManagedBy(auth()->user()) || auth()->user()->hasRole('super_admin'))),
            404
        );

        $club->load([
            'manager',
            'venues' => fn ($query) => $query
                ->approved()
                ->with(['photos', 'waters.species'])
                ->orderBy('name'),
        ]);

        return view('clubs.show', [
            'club' => $club,
        ]);
    }

    public function edit(Club $club): View
    {
        $this->authorize('manage', $club);

        return view('clubs.edit', [
            'club' => $club,
        ]);
    }

    public function update(Request $request, Club $club, ClubPersistenceService $persistence): RedirectResponse
    {
        $this->authorize('manage', $club);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'overview' => ['nullable', 'string', 'max:5000'],
            'town' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:5120'],
        ]);

        $persistence->apply($club, $validated, $request->file('logo'));

        return redirect()
            ->route('clubs.show', $club->fresh())
            ->with('status', 'Club details updated.');
    }
}
