<?php namespace FireflyIII\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Config;
use FireflyIII\Http\Requests\NewUserFormRequest;
use FireflyIII\Models\AccountMeta;
use FireflyIII\Repositories\Account\AccountRepositoryInterface as ARI;
use Preferences ;
use Session;
use View;

/**
 * Class NewUserController
 *
 * @package FireflyIII\Http\Controllers
 */
class NewUserController extends Controller
{


    /**
     * @param ARI $repository
     *
     * @@return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(ARI $repository)
    {
        View::share('title', 'Welcome to Firefly!');
        View::share('mainTitleIcon', 'fa-fire');


        $types = Config::get('firefly.accountTypesByIdentifier.asset');
        $count = $repository->countAccounts($types);

        if ($count > 0) {
            return redirect(route('index'));

        }

        return view('new-user.index');
    }

    /**
     * @param NewUserFormRequest         $request
     * @param ARI $repository
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(NewUserFormRequest $request, ARI $repository)
    {
        // create normal asset account:
        $assetAccount = [
            'name'                   => $request->get('bank_name'),
            'iban'                   => null,
            'accountType'            => 'asset',
            'virtualBalance'         => 0,
            'active'                 => true,
            'user'                   => Auth::user()->id,
            'accountRole'            => 'defaultAsset',
            'openingBalance'         => round($request->input('bank_balance'), 2),
            'openingBalanceDate'     => new Carbon,
            'openingBalanceCurrency' => intval($request->input('amount_currency_id_bank_balance')),
        ];

        $repository->store($assetAccount);

        // create savings account
        if (strlen($request->get('savings_balance') > 0)) {
            $savingsAccount = [
                'name'                   => $request->get('bank_name') . ' savings account',
                'iban'                   => null,
                'accountType'            => 'asset',
                'virtualBalance'         => 0,
                'active'                 => true,
                'user'                   => Auth::user()->id,
                'accountRole'            => 'savingAsset',
                'openingBalance'         => round($request->input('savings_balance'), 2),
                'openingBalanceDate'     => new Carbon,
                'openingBalanceCurrency' => intval($request->input('amount_currency_id_savings_balance')),
            ];
            $repository->store($savingsAccount);
        }


        // create credit card.
        if (strlen($request->get('credit_card_limit') > 0)) {
            $creditAccount = [
                'name'                   => 'Credit card',
                'iban'                   => null,
                'accountType'            => 'asset',
                'virtualBalance'         => round($request->get('credit_card_limit'), 2),
                'active'                 => true,
                'user'                   => Auth::user()->id,
                'accountRole'            => 'ccAsset',
                'openingBalance'         => null,
                'openingBalanceDate'     => null,
                'openingBalanceCurrency' => intval($request->input('amount_currency_id_credit_card_limit')),
            ];
            $creditCard    = $repository->store($creditAccount);

            // store meta for CC:
            AccountMeta::create(['name' => 'ccType', 'data' => 'monthlyFull', 'account_id' => $creditCard->id,]);
            AccountMeta::create(['name' => 'ccMonthlyPaymentDate', 'data' => Carbon::now()->year . '-01-01', 'account_id' => $creditCard->id,]);

        }
        Session::flash('success', 'New account(s) created!');
        Preferences::mark();

        return redirect(route('index'));
    }
}
