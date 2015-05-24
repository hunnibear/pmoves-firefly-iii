<?php namespace FireflyIII\Http\Middleware;

use Closure;
use FireflyIII\Models\Account;
use FireflyIII\Models\Bill;
use FireflyIII\Models\Budget;
use FireflyIII\Models\BudgetLimit;
use FireflyIII\Models\Category;
use FireflyIII\Models\LimitRepetition;
use FireflyIII\Models\PiggyBank;
use FireflyIII\Models\PiggyBankEvent;
use FireflyIII\Models\PiggyBankRepetition;
use FireflyIII\Models\Preference;
use FireflyIII\Models\Reminder;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionJournal;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Session;

/**
 * Class Cleanup
 *
 * @codeCoverageIgnore
 * @package FireflyIII\Http\Middleware
 */
class Cleanup
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
        $run   = env('RUNCLEANUP') == 'true' ? true : false;
        $count = 0;

        if ($run) {
            // encrypt account name
            $set = Account::where('encrypted', 0)->take(5)->get();
            /** @var Account $entry */
            foreach ($set as $entry) {
                $count++;
                $name        = $entry->name;
                $entry->name = $name;
                $entry->save();
            }
            unset($set, $entry, $name);

            // encrypt bill name
            $set = Bill::where('name_encrypted', 0)->take(5)->get();
            /** @var Bill $entry */
            foreach ($set as $entry) {
                $count++;
                $name        = $entry->name;
                $entry->name = $name;
                $entry->save();
            }
            unset($set, $entry, $name);

            // encrypt bill match
            $set = Bill::where('match_encrypted', 0)->take(5)->get();
            /** @var Bill $entry */
            foreach ($set as $entry) {
                $match        = $entry->match;
                $entry->match = $match;
                $entry->save();
            }
            unset($set, $entry, $match);

            // encrypt budget name
            $set = Budget::where('encrypted', 0)->take(5)->get();
            /** @var Budget $entry */
            foreach ($set as $entry) {
                $count++;
                $name        = $entry->name;
                $entry->name = $name;
                $entry->save();
            }
            unset($set, $entry, $name);

            // encrypt category name
            $set = Category::where('encrypted', 0)->take(5)->get();
            /** @var Category $entry */
            foreach ($set as $entry) {
                $count++;
                $name        = $entry->name;
                $entry->name = $name;
                $entry->save();
            }
            unset($set, $entry, $name);

            // encrypt piggy bank name
            $set = PiggyBank::where('encrypted', 0)->take(5)->get();
            /** @var PiggyBank $entry */
            foreach ($set as $entry) {
                $count++;
                $name        = $entry->name;
                $entry->name = $name;
                $entry->save();
            }
            unset($set, $entry, $name);

            // encrypt transaction journal description
            $set = TransactionJournal::where('encrypted', 0)->take(5)->get();
            /** @var TransactionJournal $entry */
            foreach ($set as $entry) {
                $count++;
                $description        = $entry->description;
                $entry->description = $description;
                $entry->save();
            }
            unset($set, $entry, $description);

            // encrypt reminder metadata
            $set = Reminder::where('encrypted', 0)->take(5)->get();
            /** @var Reminder $entry */
            foreach ($set as $entry) {
                $count++;
                $metadata        = $entry->metadata;
                $entry->metadata = $metadata;
                $entry->save();
            }
            unset($set, $entry, $metadata);

            // encrypt account virtual balance amount
            $set = Account::whereNull('virtual_balance_encrypted')->take(5)->get();
            /** @var Account $entry */
            foreach ($set as $entry) {
                $count++;
                $amount                 = $entry->amount;
                $entry->virtual_balance = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            // encrypt bill amount_min
            $set = Bill::whereNull('amount_min_encrypted')->take(5)->get();
            /** @var Bill $entry */
            foreach ($set as $entry) {
                $count++;
                $amount            = $entry->amount_min;
                $entry->amount_min = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            // encrypt bill amount_max
            $set = Bill::whereNull('amount_max_encrypted')->take(5)->get();
            /** @var Bill $entry */
            foreach ($set as $entry) {
                $count++;
                $amount            = $entry->amount_max;
                $entry->amount_max = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            // encrypt budget limit amount
            $set = BudgetLimit::whereNull('amount_encrypted')->take(5)->get();
            /** @var BudgetLimit $entry */
            foreach ($set as $entry) {
                $count++;
                $amount        = $entry->amount;
                $entry->amount = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            // encrypt limit repetition amount
            $set = LimitRepetition::whereNull('amount_encrypted')->take(5)->get();
            /** @var LimitRepetition $entry */
            foreach ($set as $entry) {
                $count++;
                $amount        = $entry->amount;
                $entry->amount = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            //encrypt piggy bank event amount
            $set = PiggyBankEvent::whereNull('amount_encrypted')->take(5)->get();
            /** @var PiggyBankEvent $entry */
            foreach ($set as $entry) {
                $count++;
                $amount        = $entry->amount;
                $entry->amount = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            // encrypt piggy bank repetition currentamount
            $set = PiggyBankRepetition::whereNull('currentamount_encrypted')->take(5)->get();
            /** @var PiggyBankRepetition $entry */
            foreach ($set as $entry) {
                $count++;
                $amount               = $entry->currentamount;
                $entry->currentamount = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            // encrypt piggy bank targetamount
            $set = PiggyBank::whereNull('targetamount_encrypted')->take(5)->get();
            /** @var PiggyBank $entry */
            foreach ($set as $entry) {
                $count++;
                $amount              = $entry->targetamount;
                $entry->targetamount = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);

            //encrypt preference name
            $set = Preference::whereNull('name_encrypted')->take(5)->get();
            /** @var Preference $entry */
            foreach ($set as $entry) {
                $count++;
                $name        = $entry->name;
                $entry->name = $name;
                $entry->save();
            }
            unset($set, $entry, $name);
            //encrypt preference data (add field)
            $set = Preference::whereNull('data_encrypted')->take(5)->get();
            /** @var Preference $entry */
            foreach ($set as $entry) {
                $count++;
                $data        = $entry->data;
                $entry->data = $data;
                $entry->save();
            }
            unset($set, $entry, $data);

            // encrypt transaction amount
            $set = Transaction::whereNull('amount_encrypted')->take(5)->get();
            /** @var Transaction $entry */
            foreach ($set as $entry) {
                $count++;
                $amount        = $entry->amount;
                $entry->amount = $amount;
                $entry->save();
            }
            unset($set, $entry, $amount);
        }
        if ($count == 0 && $run) {
            Session::flash('warning', 'Please open the .env file and change RUNCLEANUP=true to RUNCLEANUP=false');
        }

        return $next($request);
    }

}
