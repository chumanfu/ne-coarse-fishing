<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\TackleShop;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $venueMarkers = $venues->map(fn (Venue $venue) => [
            'id' => 'venue-'.$venue->id,
            'type' => 'venue',
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
            'geometries' => $venue->waters
                ->filter(fn ($water) => $water->hasGeometry())
                ->map(fn ($water) => $water->geoJson())
                ->values()
                ->all(),
        ]);

        $shopMarkers = $this->shouldIncludeTackleShops($request)
            ? $this->tackleShopMarkers()
            : collect();

        $markers = $venueMarkers->concat($shopMarkers)->values();

        return view('map.index', [
            'markers' => $markers,
            'venueCount' => $venueMarkers->count(),
            'shopCount' => $shopMarkers->count(),
            'species' => Species::orderBy('name')->get(),
            'filters' => $request->only(['species', 'ticket_type']),
        ]);
    }

    private function shouldIncludeTackleShops(Request $request): bool
    {
        // Species / ticket filters are venue-only; keep shops visible unless a venue filter is active.
        return ! $request->filled('species') && ! $request->filled('ticket_type');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function tackleShopMarkers(): Collection
    {
        return TackleShop::query()
            ->published()
            ->mappable()
            ->ordered()
            ->get()
            ->map(fn (TackleShop $shop) => [
                'id' => 'shop-'.$shop->id,
                'type' => 'tackle_shop',
                'name' => $shop->name,
                'slug' => $shop->slug,
                'lat' => $shop->latitude,
                'lng' => $shop->longitude,
                'ticket_type' => $shop->locationTypeLabel(),
                'ticket_type_raw' => $shop->location_type,
                'species' => [],
                'species_slugs' => [],
                'address' => $shop->address ?: $shop->town,
                'verified' => false,
                'url' => route('tackle-shops.show', $shop),
                'overview' => str($shop->overview)->limit(140)->toString(),
            ]);
    }
}
