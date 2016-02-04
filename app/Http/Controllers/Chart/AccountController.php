<?php

namespace FireflyIII\Http\Controllers\Chart;

use Carbon\Carbon;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\Account;
use FireflyIII\Repositories\Account\AccountRepositoryInterface as ARI;
use FireflyIII\Support\CacheProperties;
use Illuminate\Support\Collection;
use Preferences;
use Response;
use Session;

/**
 * Class AccountController
 *
 * @package FireflyIII\Http\Controllers\Chart
 */
class AccountController extends Controller
{

    /** @var  \FireflyIII\Generator\Chart\Account\AccountChartGeneratorInterface */
    protected $generator;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        // create chart generator:
        $this->generator = app('FireflyIII\Generator\Chart\Account\AccountChartGeneratorInterface');
    }


    /**
     * Shows the balances for a given set of dates and accounts.
     *
     * @param            $reportType
     * @param Carbon     $start
     * @param Carbon     $end
     * @param Collection $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function report($reportType, Carbon $start, Carbon $end, Collection $accounts)
    {
        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('all');
        $cache->addProperty('accounts');
        $cache->addProperty('default');
        $cache->addProperty($reportType);
        $cache->addProperty($accounts);
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        // make chart:
        $data = $this->generator->frontpage($accounts, $start, $end);
        $cache->store($data);

        return Response::json($data);
    }

    /**
     * Shows the balances for all the user's expense accounts.
     *
     * @param ARI $repository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function expenseAccounts(ARI $repository)
    {
        $start    = clone Session::get('start', Carbon::now()->startOfMonth());
        $end      = clone Session::get('end', Carbon::now()->endOfMonth());
        $accounts = $repository->getAccounts(['Expense account', 'Beneficiary account']);

        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('expenseAccounts');
        $cache->addProperty('accounts');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $data = $this->generator->expenseAccounts($accounts, $start, $end);
        $cache->store($data);

        return Response::json($data);

    }

    /**
     * Shows the balances for all the user's frontpage accounts.
     *
     * @param ARI $repository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frontpage(ARI $repository)
    {
        $frontPage = Preferences::get('frontPageAccounts', []);
        $start     = clone Session::get('start', Carbon::now()->startOfMonth());
        $end       = clone Session::get('end', Carbon::now()->endOfMonth());
        $accounts  = $repository->getFrontpageAccounts($frontPage);

        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('frontpage');
        $cache->addProperty('accounts');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $data = $this->generator->frontpage($accounts, $start, $end);
        $cache->store($data);

        return Response::json($data);

    }

    /**
     * Shows an account's balance for a single month.
     *
     * @param Account $account
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function single(Account $account)
    {


        $start = Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());

        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('frontpage');
        $cache->addProperty('single');
        $cache->addProperty($account->id);
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $data = $this->generator->single($account, $start, $end);
        $cache->store($data);

        return Response::json($data);
    }
}
