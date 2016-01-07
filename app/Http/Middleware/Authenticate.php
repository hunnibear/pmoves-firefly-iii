<?php namespace FireflyIII\Http\Middleware;

use App;
use Auth;
use Carbon\Carbon;
use Closure;
use FireflyIII\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Preferences;

/**
 * Class Authenticate
 *
 * @codeCoverageIgnore
 * @package FireflyIII\Http\Middleware
 */
class Authenticate
{

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     *
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }
        /** @var User $user */
        $user = $this->auth->user();
        if ($user instanceof User && intval($user->blocked) == 1) {
            Auth::logout();

            return redirect()->route('index');
        }

        // if logged in, set user language:
        $pref = Preferences::get('language', env('DEFAULT_LANGUAGE', 'en_US'));
        App::setLocale($pref->data);
        Carbon::setLocale(substr($pref->data, 0, 2));
        $locale = explode(',', trans('config.locale'));
        $locale = array_map('trim', $locale);

        setlocale(LC_TIME, $locale);
        setlocale(LC_MONETARY, $locale);

        return $next($request);
    }

}
