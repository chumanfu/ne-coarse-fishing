<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\Water;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WaterMapImageController extends Controller
{
    public function update(Request $request, Venue $venue, Water $water): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($venue->canManagePegs($request->user()), 403);

        $validated = $request->validate([
            'map_image' => ['required', 'image', 'max:10240'],
        ]);

        if (filled($water->map_image_path)) {
            Uploads::delete($water->map_image_path);
        }

        $path = Uploads::store($validated['map_image'], 'water-maps');

        $water->update(['map_image_path' => $path]);

        // Pegs must be remapped against the new top-down image.
        $water->pegs()->update([
            'map_x' => null,
            'map_y' => null,
            'latitude' => null,
            'longitude' => null,
        ]);

        return back()->with('status', 'Pond map image saved. Place pegs again on the new image.');
    }

    public function destroy(Venue $venue, Water $water): RedirectResponse
    {
        abort_unless($water->venue_id === $venue->id, 404);
        abort_unless($venue->canManagePegs(request()->user()), 403);

        if (filled($water->map_image_path)) {
            Uploads::delete($water->map_image_path);
            $water->update(['map_image_path' => null]);
        }

        $water->pegs()->update([
            'map_x' => null,
            'map_y' => null,
            'latitude' => null,
            'longitude' => null,
        ]);

        return back()->with('status', 'Pond map image removed. Peg positions were cleared.');
    }
}
