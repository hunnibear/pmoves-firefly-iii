<?php namespace FireflyIII\Http\Controllers;

use Amount;
use Carbon\Carbon;
use Config;
use ExpandedForm;
use FireflyIII\Http\Requests\PiggyBankFormRequest;
use FireflyIII\Models\PiggyBank;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\PiggyBank\PiggyBankRepositoryInterface;
use Illuminate\Support\Collection;
use Input;
use Preferences;
use Redirect;
use Session;
use Steam;
use URL;
use View;

/**
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * Class PiggyBankController
 *
 * @package FireflyIII\Http\Controllers
 */
class PiggyBankController extends Controller
{

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        View::share('title', trans('firefly.piggyBanks'));
        View::share('mainTitleIcon', 'fa-sort-amount-asc');
    }

    /**
     * Add money to piggy bank
     *
     * @param AccountRepositoryInterface $repository
     * @param PiggyBank                  $piggyBank
     *
     * @return $this
     */
    public function add(AccountRepositoryInterface $repository, PiggyBank $piggyBank)
    {
        $date          = Session::get('end', Carbon::now()->endOfMonth());
        $leftOnAccount = $repository->leftOnAccount($piggyBank->account, $date);
        $savedSoFar    = $piggyBank->currentRelevantRep()->currentamount;
        $leftToSave    = $piggyBank->targetamount - $savedSoFar;
        $maxAmount     = min($leftOnAccount, $leftToSave);

        return view('piggy-banks.add', compact('piggyBank', 'maxAmount'));
    }

    /**
     * @param AccountRepositoryInterface $repository
     *
     * @return mixed
     */
    public function create(AccountRepositoryInterface $repository)
    {

        $periods      = Config::get('firefly.piggy_bank_periods');
        $accounts     = ExpandedForm::makeSelectList($repository->getAccounts(['Default account', 'Asset account']));
        $subTitle     = trans('firefly.create_new_piggybank');
        $subTitleIcon = 'fa-plus';

        // put previous url in session if not redirect from store (not "create another").
        if (Session::get('piggy-banks.create.fromStore') !== true) {
            Session::put('piggy-banks.create.url', URL::previous());
        }
        Session::forget('piggy-banks.create.fromStore');
        Session::flash('gaEventCategory', 'piggy-banks');
        Session::flash('gaEventAction', 'create');

        return view('piggy-banks.create', compact('accounts', 'periods', 'subTitle', 'subTitleIcon'));
    }

    /**
     * @param PiggyBank $piggyBank
     *
     * @return $this
     */
    public function delete(PiggyBank $piggyBank)
    {
        $subTitle = trans('firefly.delete_piggy_bank', ['name' => $piggyBank->name]);

        // put previous url in session
        Session::put('piggy-banks.delete.url', URL::previous());
        Session::flash('gaEventCategory', 'piggy-banks');
        Session::flash('gaEventAction', 'delete');

        return view('piggy-banks.delete', compact('piggyBank', 'subTitle'));
    }

    /**
     * @param PiggyBankRepositoryInterface $repository
     * @param PiggyBank                    $piggyBank
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PiggyBankRepositoryInterface $repository, PiggyBank $piggyBank)
    {


        Session::flash('success', 'Piggy bank "' . e($piggyBank->name) . '" deleted.');
        Preferences::mark();
        $repository->destroy($piggyBank);

        return Redirect::to(Session::get('piggy-banks.delete.url'));
    }

    /**
     * @param AccountRepositoryInterface $repository
     * @param PiggyBank                  $piggyBank
     *
     * @return View
     */
    public function edit(AccountRepositoryInterface $repository, PiggyBank $piggyBank)
    {

        $periods      = Config::get('firefly.piggy_bank_periods');
        $accounts     = ExpandedForm::makeSelectList($repository->getAccounts(['Default account', 'Asset account']));
        $subTitle     = trans('firefly.update_piggy_title', ['name' => $piggyBank->name]);
        $subTitleIcon = 'fa-pencil';

        /*
         * Flash some data to fill the form.
         */
        if (is_null($piggyBank->targetdate) || $piggyBank->targetdate == '') {
            $targetDate = null;
        } else {
            $targetDate = new Carbon($piggyBank->targetdate);
            $targetDate = $targetDate->format('Y-m-d');
        }
        $preFilled = ['name'         => $piggyBank->name,
                      'account_id'   => $piggyBank->account_id,
                      'targetamount' => $piggyBank->targetamount,
                      'targetdate'   => $targetDate,
        ];
        Session::flash('preFilled', $preFilled);
        Session::flash('gaEventCategory', 'piggy-banks');
        Session::flash('gaEventAction', 'edit');

        // put previous url in session if not redirect from store (not "return_to_edit").
        if (Session::get('piggy-banks.edit.fromUpdate') !== true) {
            Session::put('piggy-banks.edit.url', URL::previous());
        }
        Session::forget('piggy-banks.edit.fromUpdate');

        return view('piggy-banks.edit', compact('subTitle', 'subTitleIcon', 'piggyBank', 'accounts', 'periods', 'preFilled'));
    }

    /**
     * @param AccountRepositoryInterface   $repository
     * @param PiggyBankRepositoryInterface $piggyRepository
     *
     * @return View
     */
    public function index(AccountRepositoryInterface $repository, PiggyBankRepositoryInterface $piggyRepository)
    {
        /** @var Collection $piggyBanks */
        $piggyBanks = $piggyRepository->getPiggyBanks();
        $end        = Session::get('end', Carbon::now()->endOfMonth());

        $accounts = [];
        /** @var PiggyBank $piggyBank */
        foreach ($piggyBanks as $piggyBank) {
            $piggyBank->savedSoFar = floatval($piggyBank->currentRelevantRep()->currentamount);
            $piggyBank->percentage = $piggyBank->savedSoFar != 0 ? intval($piggyBank->savedSoFar / $piggyBank->targetamount * 100) : 0;
            $piggyBank->leftToSave = $piggyBank->targetamount - $piggyBank->savedSoFar;

            /*
             * Fill account information:
             */
            $account = $piggyBank->account;
            if (!isset($accounts[$account->id])) {
                $accounts[$account->id] = [
                    'name'              => $account->name,
                    'balance'           => Steam::balance($account, $end, true),
                    'leftForPiggyBanks' => $repository->leftOnAccount($account, $end),
                    'sumOfSaved'        => $piggyBank->savedSoFar,
                    'sumOfTargets'      => floatval($piggyBank->targetamount),
                    'leftToSave'        => $piggyBank->leftToSave
                ];
            } else {
                $accounts[$account->id]['sumOfSaved'] += $piggyBank->savedSoFar;
                $accounts[$account->id]['sumOfTargets'] += floatval($piggyBank->targetamount);
                $accounts[$account->id]['leftToSave'] += $piggyBank->leftToSave;
            }
        }

        return view('piggy-banks.index', compact('piggyBanks', 'accounts'));
    }

    /**
     * @param PiggyBankRepositoryInterface $repository
     */
    public function order(PiggyBankRepositoryInterface $repository)
    {
        $data = Input::get('order');

        // set all users piggy banks to zero:
        $repository->reset();


        if (is_array($data)) {
            foreach ($data as $order => $id) {
                $repository->setOrder(intval($id), (intval($order) + 1));
            }
        }
    }

    /**
     * @param PiggyBankRepositoryInterface $repository
     * @param AccountRepositoryInterface   $accounts
     * @param PiggyBank                    $piggyBank
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAdd(PiggyBankRepositoryInterface $repository, AccountRepositoryInterface $accounts, PiggyBank $piggyBank)
    {
        $amount        = round(floatval(Input::get('amount')), 2);
        $date          = Session::get('end', Carbon::now()->endOfMonth());
        $leftOnAccount = $accounts->leftOnAccount($piggyBank->account, $date);
        $savedSoFar    = $piggyBank->currentRelevantRep()->currentamount;
        $leftToSave    = $piggyBank->targetamount - $savedSoFar;
        $maxAmount     = round(min($leftOnAccount, $leftToSave), 2);

        if ($amount <= $maxAmount) {
            $repetition = $piggyBank->currentRelevantRep();
            $repetition->currentamount += $amount;
            $repetition->save();

            // create event
            $repository->createEvent($piggyBank, $amount);

            Session::flash('success', 'Added ' . Amount::format($amount, false) . ' to "' . e($piggyBank->name) . '".');
            Preferences::mark();
        } else {
            Session::flash('error', 'Could not add ' . Amount::format($amount, false) . ' to "' . e($piggyBank->name) . '".');
        }

        return Redirect::route('piggy-banks.index');
    }

    /**
     * @param PiggyBankRepositoryInterface $repository
     * @param PiggyBank                    $piggyBank
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRemove(PiggyBankRepositoryInterface $repository, PiggyBank $piggyBank)
    {
        $amount = floatval(Input::get('amount'));

        $savedSoFar = $piggyBank->currentRelevantRep()->currentamount;

        if ($amount <= $savedSoFar) {
            $repetition = $piggyBank->currentRelevantRep();
            $repetition->currentamount -= $amount;
            $repetition->save();

            // create event
            $repository->createEvent($piggyBank, $amount * -1);

            Session::flash('success', 'Removed ' . Amount::format($amount, false) . ' from "' . e($piggyBank->name) . '".');
            Preferences::mark();
        } else {
            Session::flash('error', 'Could not remove ' . Amount::format($amount, false) . ' from "' . e($piggyBank->name) . '".');
        }

        return Redirect::route('piggy-banks.index');
    }

    /**
     * @param PiggyBank $piggyBank
     *
     *
     * @return \Illuminate\View\View
     */
    public function remove(PiggyBank $piggyBank)
    {
        return view('piggy-banks.remove', compact('piggyBank'));
    }

    /**
     * @param PiggyBankRepositoryInterface $repository
     * @param PiggyBank                    $piggyBank
     *
     * @return View
     */
    public function show(PiggyBankRepositoryInterface $repository, PiggyBank $piggyBank)
    {
        $events   = $repository->getEvents($piggyBank);
        $subTitle = e($piggyBank->name);

        return view('piggy-banks.show', compact('piggyBank', 'events', 'subTitle'));

    }

    /**
     * @param PiggyBankFormRequest         $request
     * @param PiggyBankRepositoryInterface $repository
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function store(PiggyBankFormRequest $request, PiggyBankRepositoryInterface $repository)
    {

        $piggyBankData = [
            'name'          => $request->get('name'),
            'startdate'     => new Carbon,
            'account_id'    => intval($request->get('account_id')),
            'targetamount'  => floatval($request->get('targetamount')),
            'remind_me'     => false,
            'reminder_skip' => 0,
            'targetdate'    => strlen($request->get('targetdate')) > 0 ? new Carbon($request->get('targetdate')) : null,
        ];

        $piggyBank = $repository->store($piggyBankData);

        Session::flash('success', 'Stored piggy bank "' . e($piggyBank->name) . '".');
        Preferences::mark();

        if (intval(Input::get('create_another')) === 1) {
            Session::put('piggy-banks.create.fromStore', true);

            return Redirect::route('piggy-banks.create')->withInput();
        }


        // redirect to previous URL.
        return Redirect::to(Session::get('piggy-banks.create.url'));
    }

    /**
     * @param PiggyBankRepositoryInterface $repository
     * @param PiggyBankFormRequest         $request
     * @param PiggyBank                    $piggyBank
     *
     * @return $this
     */
    public function update(PiggyBankRepositoryInterface $repository, PiggyBankFormRequest $request, PiggyBank $piggyBank)
    {
        $piggyBankData = [
            'name'          => $request->get('name'),
            'startdate'     => is_null($piggyBank->startdate) ? $piggyBank->created_at : $piggyBank->startdate,
            'account_id'    => intval($request->get('account_id')),
            'targetamount'  => floatval($request->get('targetamount')),
            'remind_me'     => false,
            'reminder_skip' => 0,
            'targetdate'    => strlen($request->get('targetdate')) > 0 ? new Carbon($request->get('targetdate')) : null,
        ];

        $piggyBank = $repository->update($piggyBank, $piggyBankData);

        Session::flash('success', 'Updated piggy bank "' . e($piggyBank->name) . '".');
        Preferences::mark();

        if (intval(Input::get('return_to_edit')) === 1) {
            Session::put('piggy-banks.edit.fromUpdate', true);

            return Redirect::route('piggy-banks.edit', [$piggyBank->id]);
        }


        // redirect to previous URL.
        return Redirect::to(Session::get('piggy-banks.edit.url'));


    }


}
