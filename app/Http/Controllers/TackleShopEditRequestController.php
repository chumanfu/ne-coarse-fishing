<?php

namespace App\Http\Controllers;

use App\Models\TackleShop;
use App\Models\TackleShopEditRequest;
use App\Services\TackleShopPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TackleShopEditRequestController extends Controller
{
    public function create(TackleShop $tackleShop): View
    {
        $this->authorize('suggestEdit', $tackleShop);

        return view('tackle-shops.suggest-edit', [
            'shop' => $tackleShop,
        ]);
    }

    public function store(Request $request, TackleShop $tackleShop, TackleShopPersistenceService $persistence): RedirectResponse
    {
        $this->authorize('suggestEdit', $tackleShop);

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
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $editRequest = TackleShopEditRequest::query()->create([
            'tackle_shop_id' => $tackleShop->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'proposed_data' => $persistence->proposedFromInput($validated),
            'status' => 'pending',
        ]);

        app(\App\Services\ActivityLogger::class)->tackleShopEditSuggested($editRequest);

        return redirect()
            ->route('tackle-shops.show', $tackleShop)
            ->with('status', 'Suggested tackle shop edit submitted for admin review.');
    }
}
