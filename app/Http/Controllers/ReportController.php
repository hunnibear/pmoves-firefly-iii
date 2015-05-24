<?php namespace FireflyIII\Http\Controllers;

use Carbon\Carbon;
use FireflyIII\Helpers\Report\ReportHelperInterface;
use FireflyIII\Models\Account;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use Session;
use View;

/**
 * Class ReportController
 *
 * @package FireflyIII\Http\Controllers
 */
class ReportController extends Controller
{

    /** @var ReportHelperInterface */
    protected $helper;

    /**
     * @codeCoverageIgnore
     *
     * @param ReportHelperInterface $helper
     */
    public function __construct(ReportHelperInterface $helper)
    {
        parent::__construct();
        $this->helper = $helper;

        View::share('title', trans('firefly.reports'));
        View::share('mainTitleIcon', 'fa-line-chart');

    }

    /**
     * @param AccountRepositoryInterface $repository
     *
     * @return View
     * @internal param ReportHelperInterface $helper
     */
    public function index(AccountRepositoryInterface $repository)
    {
        $start  = Session::get('first');
        $months = $this->helper->listOfMonths($start);

        // does the user have shared accounts?
        $accounts  = $repository->getAccounts(['Default account', 'Asset account']);
        $hasShared = false;

        /** @var Account $account */
        foreach ($accounts as $account) {
            if ($account->getMeta('accountRole') == 'sharedAsset') {
                $hasShared = true;
            }
        }


        return view('reports.index', compact('months', 'hasShared'));
    }

    /**
     * @param string $year
     * @param string $month
     *
     * @param bool   $shared
     *
     * @return \Illuminate\View\View
     */
    public function month($year = '2014', $month = '1', $shared = false)
    {
        $start            = new Carbon($year . '-' . $month . '-01');
        $subTitle         = trans('firefly.reportForMonth', ['date' => $start->formatLocalized($this->monthFormat)]);
        $subTitleIcon     = 'fa-calendar';
        $end              = clone $start;
        $incomeTopLength  = 8;
        $expenseTopLength = 8;
        if ($shared == 'shared') {
            $shared   = true;
            $subTitle = trans('firefly.reportForMonthShared', ['date' => $start->formatLocalized($this->monthFormat)]);
        }

        $end->endOfMonth();

        $accounts   = $this->helper->getAccountReport($start, $end, $shared);
        $incomes    = $this->helper->getIncomeReport($start, $end, $shared);
        $expenses   = $this->helper->getExpenseReport($start, $end, $shared);
        $budgets    = $this->helper->getBudgetReport($start, $end, $shared);
        $categories = $this->helper->getCategoryReport($start, $end, $shared);
        $balance    = $this->helper->getBalanceReport($start, $end, $shared);
        $bills      = $this->helper->getBillReport($start, $end, $shared);


        return view(
            'reports.month',
            compact(
                'start', 'shared',
                'subTitle', 'subTitleIcon',
                'accounts',
                'incomes', 'incomeTopLength',
                'expenses', 'expenseTopLength',
                'budgets', 'balance',
                'categories',
                'bills'
            )
        );

    }

    /**
     * @param      $year
     *
     * @param bool $shared
     *
     * @return $this
     */
    public function year($year, $shared = false)
    {
        $start            = new Carbon('01-01-' . $year);
        $end              = clone $start;
        $subTitle         = trans('firefly.reportForYear', ['year' => $year]);
        $subTitleIcon     = 'fa-bar-chart';
        $incomeTopLength  = 8;
        $expenseTopLength = 8;

        if ($shared == 'shared') {
            $shared   = true;
            $subTitle = trans('firefly.reportForYearShared', ['year' => $year]);
        }
        $end->endOfYear();

        $accounts = $this->helper->getAccountReport($start, $end, $shared);
        $incomes  = $this->helper->getIncomeReport($start, $end, $shared);
        $expenses = $this->helper->getExpenseReport($start, $end, $shared);


        return view(
            'reports.year',
            compact(
                'start', // the date for this report.
                'shared', // is a shared report?
                'accounts', // all accounts
                'incomes', 'expenses', // expenses and incomes.
                'subTitle', 'subTitleIcon', // subtitle and subtitle icon.
                'incomeTopLength', // length of income top X
                'expenseTopLength' // length of expense top X.
            )
        );
    }


}
