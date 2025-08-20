<?php

namespace FireflyIII\Http\Controllers\Admin;

use FireflyIII\Http\Controllers\WatchFolderController as BaseWatchFolderController;

/**
 * Shim controller to satisfy routes that reference
 * FireflyIII\Http\Controllers\Admin\WatchFolderController.
 * It simply extends the non-admin WatchFolderController so
 * existing logic is reused and route registration succeeds.
 */
class WatchFolderController extends BaseWatchFolderController
{
    // Inherit behavior from the base WatchFolderController.
}
