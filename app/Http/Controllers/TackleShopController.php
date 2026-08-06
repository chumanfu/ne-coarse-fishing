<?php

namespace App\Http\Controllers;

use App\Models\TackleShop;
use App\Services\TackleShopPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class TackleShopController extends Controller
{
    public function index(Request $request): View
    {
        $shops = TackleShop::query()
            ->published()
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('location_type', $request->string('type'));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('town', 'like', $q)
                        ->orWhere('overview', 'like', $q)
                        ->orWhere('address', 'like', $q);
                });
            })
            ->ordered()
            ->paginate(18)
            ->withQueryString();

        return view('tackle-shops.index', [
            'shops' => $shops,
            'filters' => $request->only(['q', 'type']),
        ]);
    }

    public function show(TackleShop $tackleShop): View
    {
        abort_unless(
            $tackleShop->is_published
                || (auth()->user() && ($tackleShop->isManagedBy(auth()->user()) || auth()->user()->hasRole('super_admin'))),
            404
        );

        $tackleShop->load('manager');

        return view('tackle-shops.show', [
            'shop' => $tackleShop,
        ]);
    }

    public function edit(TackleShop $tackleShop): View
    {
        $this->authorize('manage', $tackleShop);

        return view('tackle-shops.edit', [
            'shop' => $tackleShop,
        ]);
    }

    public function update(Request $request, TackleShop $tackleShop, TackleShopPersistenceService $persistence): RedirectResponse
    {
        $this->authorize('manage', $tackleShop);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'overview' => ['nullable', 'string', 'max:5000'],
            'town' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location_type' => ['required', Rule::in(array_keys(TackleShop::LOCATION_TYPES))],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'max:5120'],
        ]);

        $persistence->apply($tackleShop, $validated, $request->file('logo'));

        return redirect()
            ->route('tackle-shops.show', $tackleShop->fresh())
            ->with('status', 'Tackle shop details updated.');
    }
}
