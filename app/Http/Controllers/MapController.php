<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->approved()
            ->with(['waters.species', 'manager'])
            ->when($request->filled('species'), function ($query) use ($request) {
                $query->whereHas('waters.species', fn ($q) => $q->where('species.slug', $request->string('species')));
            })
            ->when($request->filled('ticket_type'), function ($query) use ($request) {
                $query->where('ticket_type', $request->string('ticket_type'));
            })
            ->orderBy('name')
            ->get();

        $markers = $venues->map(fn (Venue $venue) => [
            'id' => $venue->id,
            'name' => $venue->name,
            'slug' => $venue->slug,
            'lat' => $venue->latitude,
            'lng' => $venue->longitude,
            'ticket_type' => $venue->ticketTypeLabel(),
            'ticket_type_raw' => $venue->ticket_type,
            'species' => $venue->allSpecies()->pluck('name')->values(),
            'species_slugs' => $venue->allSpecies()->pluck('slug')->values(),
            'address' => $venue->address,
            'verified' => $venue->manager_verified,
            'url' => route('venues.show', $venue),
            'overview' => str($venue->overview)->limit(140)->toString(),
        ]);

        return view('map.index', [
            'markers' => $markers,
            'species' => Species::orderBy('name')->get(),
            'filters' => $request->only(['species', 'ticket_type']),
        ]);
    }
}
