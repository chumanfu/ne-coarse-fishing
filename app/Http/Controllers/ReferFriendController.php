<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReferFriendController extends Controller
{
    public function __invoke(): View
    {
        return view('refer', [
            'shareUrl' => route('home'),
            'shareTitle' => 'NE Coarse Fishing',
            'shareText' => 'Check out NE Coarse Fishing — venues, clubs, tackle shops and session reports across the North East.',
        ]);
    }
}
