<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CodeOfConductController extends Controller
{
    public function __invoke(): View
    {
        return view('code-of-conduct');
    }
}
