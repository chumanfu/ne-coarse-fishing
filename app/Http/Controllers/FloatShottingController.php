<?php

namespace App\Http\Controllers;

use App\Models\PegFloatRig;
use App\Models\Venue;
use App\Models\WaterPeg;
use App\Support\ShotReference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FloatShottingController extends Controller
{
    public function show(Request $request): View
    {
        $rig = $request->filled('rig')
            ? PegFloatRig::with('peg.water.venue')->find($request->integer('rig'))
            : null;

        $peg = $rig?->peg ?? ($request->filled('peg')
            ? WaterPeg::verified()->with('water.venue')->find($request->integer('peg'))
            : null);

        return view('tools.float-shotting', [
            'rig' => $rig,
            'peg' => $peg,
            'venues' => $this->venuesWithPegs(),
            'tips' => ShotReference::patterns(),
        ]);
    }

    public function guide(): View
    {
        return view('tools.shot-guide');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', PegFloatRig::class);

        $data = $request->validate([
            'water_peg_id' => ['required', 'integer', Rule::exists('water_pegs', 'id')->where('is_verified', true)],
            'float_name' => ['nullable', 'string', 'max:255'],
            'float_size' => ['required', 'string', 'max:50'],
            'float_type' => ['required', Rule::in(PegFloatRig::FLOAT_TYPES)],
            'float_grams' => ['required', 'numeric', 'min:0.02', 'max:30'],
            'depth' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'depth_unit' => ['required', Rule::in(['ft', 'm'])],
            'pattern_id' => ['required', Rule::in(PegFloatRig::PATTERN_IDS)],
            'olivette_grams' => ['nullable', 'numeric', 'min:0.1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $rig = $request->user()->pegFloatRigs()->create([
            ...$data,
            'float_name' => filled($data['float_name'] ?? null) ? $data['float_name'] : 'Unnamed float',
            'olivette_grams' => $data['pattern_id'] === 'olivette' ? ($data['olivette_grams'] ?? null) : null,
        ]);

        $peg = WaterPeg::with('water.venue')->find($data['water_peg_id']);

        return redirect()
            ->route('tools.float-shotting', ['rig' => $rig->id])
            ->with('status', 'Saved to '.$peg->water->venue->name.' · '.$peg->label().'.');
    }

    public function destroy(PegFloatRig $pegFloatRig): RedirectResponse
    {
        Gate::authorize('delete', $pegFloatRig);

        $venue = $pegFloatRig->peg?->water?->venue;
        $pegFloatRig->delete();

        return $venue
            ? redirect()->route('venues.show', $venue)->with('status', 'Float rig removed.')
            : redirect()->route('tools.float-shotting');
    }

    /**
     * Venues that have verified pegs, for the save form.
     *
     * @return list<array{id: int, name: string, pegs: list<array{id: int, label: string}>}>
     */
    private function venuesWithPegs(): array
    {
        return Venue::query()
            ->approved()
            ->whereHas('waters.pegs', fn ($query) => $query->where('is_verified', true))
            ->with(['waters.pegs' => fn ($query) => $query->where('is_verified', true)])
            ->orderBy('name')
            ->get()
            ->map(fn (Venue $venue) => [
                'id' => $venue->id,
                'name' => $venue->name,
                'pegs' => $venue->waters
                    ->flatMap->pegs
                    ->map(fn (WaterPeg $peg) => [
                        'id' => $peg->id,
                        'label' => $peg->water->name.' · '.$peg->label(),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }
}
