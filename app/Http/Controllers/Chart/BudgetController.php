<?php

namespace FireflyIII\Http\Controllers\Chart;

use Cache;
use Carbon\Carbon;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\Budget;
use FireflyIII\Models\LimitRepetition;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Support\CacheProperties;
use Grumpydictator\Gchart\GChart;
use Illuminate\Support\Collection;
use Log;
use Navigation;
use Preferences;
use Response;
use Session;

/**
 * Class BudgetController
 *
 * @package FireflyIII\Http\Controllers\Chart
 */
class BudgetController extends Controller
{
    /**
     * @param GChart                    $chart
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function budget(GChart $chart, BudgetRepositoryInterface $repository, Budget $budget)
    {
        $chart->addColumn(trans('firefly.period'), 'date');
        $chart->addColumn(trans('firefly.spent'), 'number');


        $first = $repository->getFirstBudgetLimitDate($budget);
        $range = Preferences::get('viewRange', '1M')->data;
        $last  = Session::get('end', new Carbon);
        $final = clone $last;
        $final->addYears(2);
        $last = Navigation::endOfX($last, $range, $final);

        // chart properties for cache:
        $chartProperties = new CacheProperties();
        $chartProperties->addProperty($first);
        $chartProperties->addProperty($last);
        $chartProperties->addProperty('budget');
        $chartProperties->addProperty('budget');
        $md5 = $chartProperties->md5();

        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached chart [' . $md5 . '].');

            return Response::json(Cache::get($md5));
        }


        while ($first < $last) {
            $end = Navigation::addPeriod($first, $range, 0);

            $spent = $repository->spentInPeriodCorrected($budget, $first, $end);
            $chart->addRow($end, $spent);


            $first = Navigation::addPeriod($first, $range, 0);
        }

        $chart->generate();

        $data = $chart->getData();
        Cache::forever($md5, $data);

        return Response::json($data);
    }

    /**
     * Shows the amount left in a specific budget limit.
     *
     * @param GChart                    $chart
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     * @param LimitRepetition           $repetition
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function budgetLimit(GChart $chart, BudgetRepositoryInterface $repository, Budget $budget, LimitRepetition $repetition)
    {
        $start = clone $repetition->startdate;
        $end   = $repetition->enddate;

        // chart properties for cache:
        $chartProperties = new CacheProperties();
        $chartProperties->addProperty($start);
        $chartProperties->addProperty($end);
        $chartProperties->addProperty('budget');
        $chartProperties->addProperty('limit');
        $chartProperties->addProperty($budget->id);
        $chartProperties->addProperty($repetition->id);
        $md5 = $chartProperties->md5();

        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached chart [' . $md5 . '].');

            return Response::json(Cache::get($md5));
        }

        $chart->addColumn(trans('firefly.day'), 'date');
        $chart->addColumn(trans('firefly.left'), 'number');


        $amount = $repetition->amount;

        while ($start <= $end) {
            /*
             * Sum of expenses on this day:
             */
            $sum = $repository->expensesOnDayCorrected($budget, $start);
            $amount += $sum;
            $chart->addRow(clone $start, $amount);
            $start->addDay();
        }
        $chart->generate();

        $data = $chart->getData();
        Cache::forever($md5, $data);

        return Response::json($data);

    }

    /**
     * Shows a budget list with spent/left/overspent.
     *
     * @param GChart                    $chart
     * @param BudgetRepositoryInterface $repository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frontpage(GChart $chart, BudgetRepositoryInterface $repository)
    {
        $chart->addColumn(trans('firefly.budget'), 'string');
        $chart->addColumn(trans('firefly.left'), 'number');
        $chart->addColumn(trans('firefly.spent'), 'number');
        $chart->addColumn(trans('firefly.overspent'), 'number');

        $budgets    = $repository->getBudgets();
        $start      = Session::get('start', Carbon::now()->startOfMonth());
        $end        = Session::get('end', Carbon::now()->endOfMonth());
        $allEntries = new Collection;

        // chart properties for cache:
        $chartProperties = new CacheProperties();
        $chartProperties->addProperty($start);
        $chartProperties->addProperty($end);
        $chartProperties->addProperty('budget');
        $chartProperties->addProperty('all');
        $md5 = $chartProperties->md5();

        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached chart [' . $md5 . '].');

            return Response::json(Cache::get($md5));
        }


        /** @var Budget $budget */
        foreach ($budgets as $budget) {
            $repetitions = $repository->getBudgetLimitRepetitions($budget, $start, $end);
            if ($repetitions->count() == 0) {
                $expenses = $repository->spentInPeriodCorrected($budget, $start, $end, true);
                $allEntries->push([$budget->name, 0, 0, $expenses]);
                continue;
            }
            /** @var LimitRepetition $repetition */
            foreach ($repetitions as $repetition) {
                $expenses  = $repository->spentInPeriodCorrected($budget, $repetition->startdate, $repetition->enddate, true);
                $left      = $expenses < floatval($repetition->amount) ? floatval($repetition->amount) - $expenses : 0;
                $spent     = $expenses > floatval($repetition->amount) ? floatval($repetition->amount) : $expenses;
                $overspent = $expenses > floatval($repetition->amount) ? $expenses - floatval($repetition->amount) : 0;
                $allEntries->push(
                    [$budget->name . ' (' . $repetition->startdate->formatLocalized($this->monthAndDayFormat) . ')',
                     $left,
                     $spent,
                     $overspent
                    ]
                );
            }
        }

        $noBudgetExpenses = $repository->getWithoutBudgetSum($start, $end) * -1;
        $allEntries->push([trans('firefly.noBudget'), 0, 0, $noBudgetExpenses]);

        foreach ($allEntries as $entry) {
            if ($entry[1] != 0 || $entry[2] != 0 || $entry[3] != 0) {
                $chart->addRow($entry[0], $entry[1], $entry[2], $entry[3]);
            }
        }

        $chart->generate();

        $data = $chart->getData();
        Cache::forever($md5, $data);

        return Response::json($data);

    }

    /**
     * Show a yearly overview for a budget.
     *
     * @param GChart                    $chart
     * @param BudgetRepositoryInterface $repository
     * @param                           $year
     * @param bool                      $shared
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function year(GChart $chart, BudgetRepositoryInterface $repository, $year, $shared = false)
    {
        $start   = new Carbon($year . '-01-01');
        $end     = new Carbon($year . '-12-31');
        $shared  = $shared == 'shared' ? true : false;
        $budgets = $repository->getBudgets();

        // chart properties for cache:
        $chartProperties = new CacheProperties();
        $chartProperties->addProperty($start);
        $chartProperties->addProperty($end);
        $chartProperties->addProperty('budget');
        $chartProperties->addProperty('year');
        $md5 = $chartProperties->md5();


        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached chart [' . $md5 . '].');

            return Response::json(Cache::get($md5));
        }

        // add columns:
        $chart->addColumn(trans('firefly.month'), 'date');
        foreach ($budgets as $budget) {
            $chart->addColumn($budget->name, 'number');
        }

        while ($start < $end) {
            // month is the current end of the period:
            $month = clone $start;
            $month->endOfMonth();
            // make a row:
            $row = [clone $start];

            // each budget, fill the row:
            foreach ($budgets as $budget) {
                $spent = $repository->spentInPeriodCorrected($budget, $start, $month, $shared);
                $row[] = $spent;
            }
            $chart->addRowArray($row);

            $start->addMonth();
        }

        $chart->generate();

        $data = $chart->getData();
        Cache::forever($md5, $data);

        return Response::json($data);
    }
}
