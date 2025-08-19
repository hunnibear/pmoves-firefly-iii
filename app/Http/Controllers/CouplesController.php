<?php

declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use FireflyIII\Http\Controllers\Controller;

class CouplesController extends Controller
{
    public function index()
    {
        return view('couples.index');
    }
}
