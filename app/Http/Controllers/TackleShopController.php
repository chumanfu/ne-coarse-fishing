<?php

namespace App\Http\Controllers;

use App\Models\TackleShop;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        abort_unless($tackleShop->is_published, 404);

        return view('tackle-shops.show', [
            'shop' => $tackleShop,
        ]);
    }
}
