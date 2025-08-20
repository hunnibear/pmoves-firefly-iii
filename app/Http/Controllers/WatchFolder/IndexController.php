<?php

namespace FireflyIII\Http\Controllers\WatchFolder;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Placeholder IndexController for WatchFolder web routes.
 * Minimal implementations to allow route registration and basic responses.
 */
class IndexController
{
    public function index()
    {
        return response('WatchFolder index placeholder', 200);
    }

    public function delete($filename)
    {
        return response('WatchFolder delete placeholder for: ' . $filename, 200);
    }
}
