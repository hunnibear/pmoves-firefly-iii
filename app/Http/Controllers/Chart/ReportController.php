<?php

namespace FireflyIII\Http\Controllers\Chart;


use Carbon\Carbon;
use FireflyIII\Helpers\Report\ReportQueryInterface;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Support\CacheProperties;
use Illuminate\Support\Collection;
use Response;

/**
 * Class ReportController
 *
 * @package FireflyIII\Http\Controllers\Chart
 */
class ReportController extends Controller
{

    /** @var  \FireflyIII\Generator\Chart\Report\ReportChartGenerator */
    protected $generator;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        // create chart generator:
        $this->generator = app('FireflyIII\Generator\Chart\Report\ReportChartGenerator');
    }


    /**
     * Summarizes all income and expenses, per month, for a given year.
     *
     * @param ReportQueryInterface $query
     * @param                      $report_type
     * @param Carbon               $start
     * @param Carbon               $end
     * @param Collection           $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearInOut(ReportQueryInterface $query, $report_type, Carbon $start, Carbon $end, Collection $accounts)
    {
        // chart properties for cache:
        $cache = new CacheProperties;
        $cache->addProperty('yearInOut');
        $cache->addProperty($start);
        $cache->addProperty($accounts);
        $cache->addProperty($end);
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $entries = new Collection;
        while ($start < $end) {
            $month = clone $start;
            $month->endOfMonth();
            // total income and total expenses:
            $incomeSum  = $query->incomeInPeriodCorrectedForList($start, $month, $accounts)->sum('amount_positive');
            $expenseSum = $query->expenseInPeriodCorrectedForList($start, $month, $accounts)->sum('amount_positive');
            

            $entries->push([clone $start, $incomeSum, $expenseSum]);
            $start->addMonth();
        }

        $data = $this->generator->yearInOut($entries);
        $cache->store($data);

        return Response::json($data);

    }

    /**
     * Summarizes all income and expenses for a given year. Gives a total and an average.
     *
     * @param ReportQueryInterface $query
     * @param                      $report_type
     * @param Carbon               $start
     * @param Carbon               $end
     * @param Collection           $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearInOutSummarized(ReportQueryInterface $query, $report_type, Carbon $start, Carbon $end, Collection $accounts)
    {

        // chart properties for cache:
        $cache = new CacheProperties;
        $cache->addProperty('yearInOutSummarized');
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($accounts);
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $income  = '0';
        $expense = '0';
        $count   = 0;

        bcscale(2);

        while ($start < $end) {
            $month = clone $start;
            $month->endOfMonth();
            // total income and total expenses:
            $currentIncome  = $query->incomeInPeriodCorrectedForList($start, $month, $accounts)->sum('amount_positive');
            $currentExpense = $query->expenseInPeriodCorrectedForList($start, $month, $accounts)->sum('amount_positive');
            $income         = bcadd($income, $currentIncome);
            $expense        = bcadd($expense, $currentExpense);

            $count++;
            $start->addMonth();
        }

        $data = $this->generator->yearInOutSummarized($income, $expense, $count);
        $cache->store($data);

        return Response::json($data);

    }
}
