<?php
/**
 * IsConfirmed.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Preferences;

/**
 * Class IsConfirmed
 *
 * @package FireflyIII\Http\Middleware
 */
class IsConfirmed
{
    /**
     * Handle an incoming request. User account must be confirmed for this routine to let
     * the user pass.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('login');
            }
        } else {
            // user must be logged in, then continue:
            $isConfirmed = Preferences::get('user_confirmed', false)->data;
            if ($isConfirmed === false) {

                // user account is not confirmed, redirect to
                // confirmation page:
                return redirect(route('confirmation_error'));
            }
        }

        return $next($request);
    }
}
