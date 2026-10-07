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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FloatShottingController extends Controller
{
    public function legacy(Request $request): RedirectResponse
    {
        if ($request->filled('rig')) {
            return redirect()->route('tools.rigs.edit', $request->integer('rig'), 301);
        }

        if ($request->filled('peg')) {
            return redirect()->route('tools.rigs.create', ['peg' => $request->integer('peg')], 301);
        }

        return redirect()->route('tools.rigs', status: 301);
    }

    public function index(Request $request): View
    {
        $rigs = $request->user()
            ? $request->user()->pegFloatRigs()->with(['venues', 'pegs.water.venue'])->latest()->get()
            : collect();

        $systemRigs = $request->user()
            ? PegFloatRig::query()->where('is_system', true)->orderBy('name')->get()
            : collect();

        return view('tools.rigs.index', [
            'rigs' => $rigs,
            'systemRigs' => $systemRigs,
        ]);
    }

    public function create(Request $request): View
    {
        return $this->editor($request, null);
    }

    public function edit(Request $request, PegFloatRig $pegFloatRig): View
    {
        $pegFloatRig->load(['venues', 'pegs.water.venue', 'peg.water.venue']);

        return $this->editor($request, $pegFloatRig);
    }

    public function guide(): View
    {
        return view('tools.shot-guide');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', PegFloatRig::class);

        $rig = $request->user()->pegFloatRigs()->create($this->attributes($request));
        $this->attachPlaces($rig, $request);

        return redirect()
            ->route('tools.rigs')
            ->with('status', 'Saved '.$rig->displayName().'.');
    }

    public function update(Request $request, PegFloatRig $pegFloatRig): RedirectResponse
    {
        Gate::authorize('update', $pegFloatRig);

        $pegFloatRig->update($this->attributes($request));
        $this->attachPlaces($pegFloatRig, $request);

        return redirect()
            ->route('tools.rigs')
            ->with('status', 'Saved '.$pegFloatRig->displayName().'.');
    }

    public function rename(Request $request, PegFloatRig $pegFloatRig): RedirectResponse
    {
        Gate::authorize('update', $pegFloatRig);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $pegFloatRig->update(['name' => $data['name']]);

        return redirect()
            ->route('tools.rigs')
            ->with('status', 'Renamed to '.$pegFloatRig->displayName().'.');
    }

    public function notes(Request $request, PegFloatRig $pegFloatRig): RedirectResponse
    {
        Gate::authorize('update', $pegFloatRig);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $notes = trim($data['notes'] ?? '');
        $pegFloatRig->update(['notes' => $notes === '' ? null : $notes]);

        return redirect()
            ->route('tools.rigs')
            ->with('status', 'Saved notes for '.$pegFloatRig->displayName().'.');
    }

    public function duplicate(Request $request, PegFloatRig $pegFloatRig): RedirectResponse
    {
        Gate::authorize('duplicate', $pegFloatRig);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $copy = $pegFloatRig->replicate(['system_key']);
        $copy->name = $data['name'];
        $copy->user_id = $request->user()->id;
        $copy->is_system = false;
        $copy->system_key = null;
        $copy->save();
        $copy->venues()->sync($pegFloatRig->venues()->pluck('venues.id'));
        $copy->pegs()->sync($pegFloatRig->pegs()->pluck('water_pegs.id'));

        return redirect()
            ->route('tools.rigs')
            ->with('status', 'Copied as '.$copy->displayName().'.');
    }

    public function destroy(PegFloatRig $pegFloatRig): RedirectResponse
    {
        Gate::authorize('delete', $pegFloatRig);

        $pegFloatRig->delete();

        return redirect()
            ->back()
            ->with('status', 'Rig removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'float_name' => ['nullable', 'string', 'max:255'],
            'float_size' => ['required', 'string', 'max:50'],
            'float_type' => ['required', Rule::in(PegFloatRig::FLOAT_TYPES)],
            'float_grams' => ['required', 'numeric', 'min:0.02', 'max:30'],
            'depth' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'depth_unit' => ['required', Rule::in(['ft', 'm'])],
            'pattern_id' => ['required', Rule::in(PegFloatRig::PATTERN_IDS)],
            'olivette_grams' => ['nullable', 'numeric', 'min:0.1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'placements' => ['required', 'string', 'max:20000'],
            'venue_ids' => ['nullable', 'array'],
            'venue_ids.*' => ['integer', Rule::exists('venues', 'id')->where('is_approved', true)],
            'peg_ids' => ['nullable', 'array'],
            'peg_ids.*' => ['integer', Rule::exists('water_pegs', 'id')->where('is_verified', true)],
        ]);

        return [
            'name' => $data['name'],
            'float_name' => filled($data['float_name'] ?? null) ? $data['float_name'] : 'Unnamed float',
            'float_size' => $data['float_size'],
            'float_type' => $data['float_type'],
            'float_grams' => $data['float_grams'],
            'depth' => $data['depth'],
            'depth_unit' => $data['depth_unit'],
            'pattern_id' => $data['pattern_id'],
            'olivette_grams' => in_array($data['pattern_id'], ['olivette', 'slider_olivette'], true)
                ? ($data['olivette_grams'] ?? null)
                : null,
            'notes' => $data['notes'] ?? null,
            'placements' => $this->placements($data['placements']),
        ];
    }

    private function attachPlaces(PegFloatRig $rig, Request $request): void
    {
        $pegIds = collect($request->input('peg_ids', []))->map(fn ($id) => (int) $id)->unique()->values();
        $pegs = WaterPeg::query()->verified()->with('water')->whereIn('id', $pegIds)->get();
        $venueIds = collect($request->input('venue_ids', []))
            ->map(fn ($id) => (int) $id)
            ->merge($pegs->map(fn (WaterPeg $peg) => $peg->water->venue_id))
            ->unique()
            ->values();
        $approved = Venue::query()->approved()->whereIn('id', $venueIds)->pluck('id');

        $rig->venues()->sync($approved);
        $rig->pegs()->sync($pegs->pluck('id'));
        $rig->forceFill(['water_peg_id' => $pegs->first()?->id])->save();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function placements(string $json): array
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded) || count($decoded) > 30) {
            throw ValidationException::withMessages([
                'placements' => 'The shot on this rig could not be saved.',
            ]);
        }

        $sizes = array_column(ShotReference::shots(), 'size');
        $clean = [];

        foreach ($decoded as $group) {
            if (! is_array($group) || ! in_array($group['role'] ?? '', PegFloatRig::ROLES, true)) {
                throw ValidationException::withMessages([
                    'placements' => 'The shot on this rig could not be saved.',
                ]);
            }

            $items = [];
            foreach ($group['items'] ?? [] as $item) {
                if (! is_array($item) || ! in_array($item['size'] ?? '', $sizes, true)) {
                    throw ValidationException::withMessages([
                        'placements' => 'The shot on this rig could not be saved.',
                    ]);
                }

                $count = (int) ($item['count'] ?? 0);
                if ($count < 1 || $count > 12 || count($items) >= 8) {
                    throw ValidationException::withMessages([
                        'placements' => 'The shot on this rig could not be saved.',
                    ]);
                }

                $items[] = ['size' => $item['size'], 'count' => $count];
            }

            $height = (float) ($group['height_cm'] ?? -1);
            if ($height < 0 || $height > 1500) {
                throw ValidationException::withMessages([
                    'placements' => 'The shot on this rig could not be saved.',
                ]);
            }

            $olivette = $group['olivette_grams'] ?? null;
            if ($olivette !== null && $olivette !== '') {
                $olivette = round((float) $olivette, 2);
                if ($olivette < 0.1 || $olivette > 12) {
                    throw ValidationException::withMessages([
                        'placements' => 'The shot on this rig could not be saved.',
                    ]);
                }
            } else {
                $olivette = null;
            }

            $clean[] = [
                'role' => $group['role'],
                'height_cm' => round($height, 2),
                'olivette_grams' => $olivette,
                'anchor' => ($group['anchor'] ?? 'hook') === 'float' ? 'float' : 'hook',
                'distance_unit' => ($group['distance_unit'] ?? 'cm') === 'in' ? 'in' : 'cm',
                'note' => is_string($group['note'] ?? null) ? mb_substr($group['note'], 0, 200) : null,
                'items' => $items,
            ];
        }

        return $clean;
    }

    private function editor(Request $request, ?PegFloatRig $rig): View
    {
        $peg = $rig?->peg ?? ($request->filled('peg')
            ? WaterPeg::verified()->with('water.venue')->find($request->integer('peg'))
            : null);

        $saved = old('placements');
        $placements = is_string($saved) ? json_decode($saved, true) : $rig?->placements;

        return view('tools.float-shotting', [
            'rig' => $rig,
            'peg' => $peg,
            'canUpdate' => $rig && $request->user()?->can('update', $rig),
            'venues' => $this->venuesWithPegs(),
            'placements' => is_array($placements) ? $placements : null,
            'tips' => ShotReference::patterns(),
        ]);
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
