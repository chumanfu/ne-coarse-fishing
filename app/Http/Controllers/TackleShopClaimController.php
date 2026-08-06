<?php

namespace App\Http\Controllers;

use App\Models\TackleShop;
use App\Models\TackleShopClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TackleShopClaimController extends Controller
{
    public function store(Request $request, TackleShop $tackleShop): RedirectResponse
    {
        $this->authorize('claim', $tackleShop);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        TackleShopClaim::query()->create([
            'tackle_shop_id' => $tackleShop->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('tackle-shops.show', $tackleShop)
            ->with('status', 'Tackle shop ownership claim submitted. An admin will review it shortly.');
    }
}
