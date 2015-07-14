<?php namespace FireflyIII\Http\Controllers\Auth;

use Auth;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\Role;
use FireflyIII\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Mail;
use Session;
use Twig;
use Validator;

/**
 * Class AuthController
 *
 * @package FireflyIII\Http\Controllers\Auth
 */
class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate(
            $request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]
        );

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials            = $this->getCredentials($request);
        $credentials['blocked'] = 0;

        if (Auth::attempt($credentials, $request->has('remember'))) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return redirect($this->loginPath())
            ->withInput($request->only($this->loginUsername(), 'remember'))
            ->withErrors(
                [
                    $this->loginUsername() => $this->getFailedLoginMessage(),
                ]
            );
    }

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    public $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @codeCoverageIgnore
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Show the application login form.
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Http\Response
     *
     */
    public function getLogin()
    {
        return Twig::render('auth.login');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRegister(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        $data             = $request->all();
        $data['password'] = bcrypt($data['password']);

        Auth::login($this->create($data));

        // get the email address
        if (Auth::user() instanceof User) {
            $email   = Auth::user()->email;
            $address = route('index');
            // send email.
            Mail::send(
                ['emails.registered-html', 'emails.registered'], ['address' => $address], function (Message $message) use ($email) {
                $message->to($email, $email)->subject('Welcome to Firefly III! ');
            }
            );

            // set flash message
            Session::flash('success', 'You have registered successfully!');
            Session::flash('gaEventCategory', 'user');
            Session::flash('gaEventAction', 'new-registration');

            // first user ever?
            if (User::count() == 1) {
                $admin = Role::where('name', 'owner')->first();
                Auth::user()->attachRole($admin);
            }


            return redirect($this->redirectPath());
        }
        // @codeCoverageIgnoreStart
        abort(500, 'Not a user!');

        return redirect('/');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make(
            $data, [
                     'email'    => 'required|email|max:255|unique:users',
                     'password' => 'required|confirmed|min:6',
                 ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return User
     */
    public function create(array $data)
    {
        return User::create(
            [
                'email'    => $data['email'],
                'password' => $data['password'],
            ]
        );
    }
}
