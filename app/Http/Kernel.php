<?php

namespace FireflyIII\Http;

use FireflyIII\Http\Middleware\Authenticate;
use FireflyIII\Http\Middleware\EncryptCookies;
use FireflyIII\Http\Middleware\Range;
use FireflyIII\Http\Middleware\RedirectIfAuthenticated;
use FireflyIII\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Class Kernel
 *
 * @package FireflyIII\Http
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware
        = [
            CheckForMaintenanceMode::class,
        ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups
        = [
            'web'            => [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
            ],
            'web-auth'       => [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                Authenticate::class,
            ],
            'web-auth-range' => [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                Authenticate::class,
                Range::class,
            ],

            'api' => [
                'throttle:60,1',
            ],
        ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware
        = [
            'auth'       => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'guest'      => RedirectIfAuthenticated::class,
            'throttle'   => ThrottleRequests::class,
            'range'      => Range::class,
        ];
}
