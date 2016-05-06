<?php
declare(strict_types = 1);

namespace FireflyIII\Http\Controllers\Chart;

use Carbon\Carbon;
use FireflyIII\Generator\Chart\Budget\BudgetChartGeneratorInterface;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\Budget;
use FireflyIII\Models\LimitRepetition;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Repositories\Account\AccountRepositoryInterface as ARI;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Support\CacheProperties;
use Illuminate\Support\Collection;
use Log;
use Navigation;
use Preferences;
use Response;

/**
 * Class BudgetController
 *
 * @package FireflyIII\Http\Controllers\Chart
 */
class BudgetController extends Controller
{

    /** @var  \FireflyIII\Generator\Chart\Budget\BudgetChartGeneratorInterface */
    protected $generator;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        // create chart generator:
        $this->generator = app(BudgetChartGeneratorInterface::class);
    }

    /**
     * checked
     *
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function budget(BudgetRepositoryInterface $repository, Budget $budget)
    {
        $first = $repository->firstUseDate($budget);
        $range = Preferences::get('viewRange', '1M')->data;
        $last  = session('end', new Carbon);

        $cache = new CacheProperties();
        $cache->addProperty($first);
        $cache->addProperty($last);
        $cache->addProperty('budget');

        if ($cache->has()) {
            return Response::json($cache->get());
        }

        $final = clone $last;
        $final->addYears(2);

        $budgetCollection = new Collection([$budget]);
        $last             = Navigation::endOfX($last, $range, $final); // not to overshoot.
        $entries          = new Collection;
        Log::debug('---- now at chart');
        while ($first < $last) {

            // periodspecific dates:
            $currentStart = Navigation::startOfPeriod($first, $range);
            $currentEnd   = Navigation::endOfPeriod($first, $range);
            // sub another day because reasons.
            $currentEnd->subDay();
            $spent = $repository->spentInPeriod($budgetCollection, new Collection, $currentStart, $currentEnd);
            $entry = [$first, ($spent * -1)];

            $entries->push($entry);
            $first = Navigation::addPeriod($first, $range, 0);
        }

        $data = $this->generator->budgetLimit($entries, 'month');
        $cache->store($data);

        return Response::json($data);
    }

    /**
     * Shows the amount left in a specific budget limit.
     *
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     * @param LimitRepetition           $repetition
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function budgetLimit(BudgetRepositoryInterface $repository, Budget $budget, LimitRepetition $repetition)
    {
        $start = clone $repetition->startdate;
        $end   = $repetition->enddate;
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('budget-limit');
        $cache->addProperty($budget->id);
        $cache->addProperty($repetition->id);

        if ($cache->has()) {
            // return Response::json($cache->get());
        }

        $entries          = new Collection;
        $amount           = $repetition->amount;
        $budgetCollection = new Collection([$budget]);
        Log::debug('amount starts ' . $amount);
        while ($start <= $end) {
            $spent  = $repository->spentInPeriod($budgetCollection, new Collection, $start, $start);
            $amount = bcadd($amount, $spent);
            $entries->push([clone $start, round($amount, 2)]);

            $start->addDay();
        }
        $data = $this->generator->budgetLimit($entries, 'monthAndDay');
        $cache->store($data);

        return Response::json($data);
    }

    /**
     * Shows a budget list with spent/left/overspent.
     *
     * @param BudgetRepositoryInterface $repository
     *
     * @param ARI                       $accountRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frontpage(BudgetRepositoryInterface $repository, ARI $accountRepository)
    {
        $start = session('start', Carbon::now()->startOfMonth());
        $end   = session('end', Carbon::now()->endOfMonth());
        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('budget');
        $cache->addProperty('all');
        if ($cache->has()) {
            return Response::json($cache->get());
        }
        $budgets     = $repository->getActiveBudgets();
        $repetitions = $repository->getAllBudgetLimitRepetitions($start, $end);
        $allEntries  = new Collection;
        $format      = strval(trans('config.month_and_day'));

        /** @var Budget $budget */
        foreach ($budgets as $budget) {
            // get relevant repetitions:
            $name = $budget->name;
            $reps = $repetitions->filter(
                function (LimitRepetition $repetition) use ($budget, $start, $end) {
                    if ($repetition->startdate < $end && $repetition->enddate > $start && $repetition->budget_id === $budget->id) {
                        return $repetition;
                    }
                }
            );
            if ($reps->count() === 0) {
                $amount    = '0';
                $left      = '0';
                $spent     = $repository->spentInPeriod(new Collection([$budget]), new Collection, $start, $end);
                $overspent = '0';
                $allEntries->push([$name, $left, $spent, $overspent, $amount, $spent]);
            }
            /** @var LimitRepetition $repetition */
            foreach ($reps as $repetition) {
                $expenses = $repository->spentInPeriod(new Collection([$budget]), new Collection, $repetition->startdate, $repetition->enddate);
                if ($reps->count() > 1) {
                    $name = $budget->name . ' ' . trans(
                            'firefly.between_dates',
                            ['start' => $repetition->startdate->formatLocalized($format), 'end' => $repetition->enddate->formatLocalized($format)]
                        );
                }
                $amount    = $repetition->amount;
                $left      = bccomp(bcadd($amount, $expenses), '0') < 1 ? '0' : bcadd($amount, $expenses);
                $spent     = bccomp(bcadd($amount, $expenses), '0') < 1 ? bcmul($amount, '-1') : $expenses;
                $overspent = bccomp(bcadd($amount, $expenses), '0') < 1 ? bcadd($amount, $expenses) : '0';
                $allEntries->push([$name, $left, $spent, $overspent, $amount, $spent]);
            }

        }

        $list = $repository->journalsInPeriodWithoutBudget(new Collection, $start, $end);
        $sum  = '0';
        /** @var TransactionJournal $entry */
        foreach ($list as $entry) {
            $sum = bcadd(TransactionJournal::amount($entry), $sum);
        }
        $allEntries->push([trans('firefly.no_budget'), '0', '0', $sum, '0', '0']);
        $data = $this->generator->frontpage($allEntries);
        $cache->store($data);

        return Response::json($data);
    }

    /**
     *
     * @param BudgetRepositoryInterface $repository
     * @param Carbon                    $start
     * @param Carbon                    $end
     * @param Collection                $accounts
     * @param Collection                $budgets
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function multiYear(BudgetRepositoryInterface $repository, Carbon $start, Carbon $end, Collection $accounts, Collection $budgets)
    {

        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($accounts);
        $cache->addProperty($budgets);
        $cache->addProperty('multiYearBudget');

        if ($cache->has()) {
            //return Response::json($cache->get());
        }
        $budgetIds   = $budgets->pluck('id')->toArray();
        $repetitions = $repository->getAllBudgetLimitRepetitions($start, $end);
        $budgeted    = [];
        $entries     = new Collection;
        // filter budgets once:
        $repetitions = $repetitions->filter(
            function (LimitRepetition $repetition) use ($budgetIds) {
                if (in_array(strval($repetition->budget_id), $budgetIds)) {
                    return $repetition;
                }
            }
        );
        /** @var LimitRepetition $repetition */
        foreach ($repetitions as $repetition) {
            $year = $repetition->startdate->year;
            if (isset($budgeted[$repetition->budget_id][$year])) {
                $budgeted[$repetition->budget_id][$year] = bcadd($budgeted[$repetition->budget_id][$year], $repetition->amount);
                continue;
            }
            $budgeted[$repetition->budget_id][$year] = $repetition->amount;
        }

        foreach ($budgets as $budget) {
            $currentStart = clone $start;
            $entry        = ['name' => $budget->name, 'spent' => [], 'budgeted' => []];
            while ($currentStart < $end) {
                // fix the date:
                $currentEnd = clone $currentStart;
                $year       = $currentStart->year;
                $currentEnd->endOfYear();

                $spent = $repository->spentInPeriod(new Collection([$budget]), $accounts, $currentStart, $currentEnd);

                // jump to next year.
                $currentStart = clone $currentEnd;
                $currentStart->addDay();

                $entry['spent'][$year]    = round($spent * -1, 2);
                $entry['budgeted'][$year] = isset($budgeted[$budget->id][$year]) ? round($budgeted[$budget->id][$year], 2) : 0;
            }
            $entries->push($entry);
        }
        $data = $this->generator->multiYear($entries);
        $cache->store($data);

        return Response::json($data);
    }

    /**
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     * @param Carbon                    $start
     * @param Carbon                    $end
     * @param Collection                $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function period(BudgetRepositoryInterface $repository, Budget $budget, Carbon $start, Carbon $end, Collection $accounts)
    {
        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($accounts);
        $cache->addProperty($budget->id);
        $cache->addProperty('budget');
        $cache->addProperty('period');
        if ($cache->has()) {
            //return Response::json($cache->get());
        }
        // loop over period, add by users range:
        $current     = clone $start;
        $viewRange   = Preferences::get('viewRange', '1M')->data;
        $set         = new Collection;
        $repetitions = $repository->getAllBudgetLimitRepetitions($start, $end);


        while ($current < $end) {
            $currentStart = clone $current;
            $currentEnd   = Navigation::endOfPeriod($currentStart, $viewRange);
            $reps         = $repetitions->filter(
                function (LimitRepetition $repetition) use ($budget, $currentStart) {
                    if ($repetition->budget_id === $budget->id && $repetition->startdate == $currentStart) {
                        return $repetition;
                    }
                }
            );
            $budgeted     = $reps->sum('amount');
            $spent        = $repository->spentInPeriod(new Collection([$budget]), $accounts, $currentStart, $currentEnd);
            $entry        = [
                'date'     => clone $currentStart,
                'budgeted' => $budgeted,
                'spent'    => $spent,
            ];
            $set->push($entry);
            $currentEnd->addDay();
            $current = clone $currentEnd;

        }
        $data = $this->generator->period($set, $viewRange);
        $cache->store($data);

        return Response::json($data);

    }

    /**
     *
     * @param BudgetRepositoryInterface $repository
     * @param                           $reportType
     * @param Carbon                    $start
     * @param Carbon                    $end
     * @param Collection                $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function year(BudgetRepositoryInterface $repository, string $reportType, Carbon $start, Carbon $end, Collection $accounts)
    {
        
        /**
         * // chart properties for cache:
         * $cache = new CacheProperties();
         * $cache->addProperty($start);
         * $cache->addProperty($end);
         * $cache->addProperty($reportType);
         * $cache->addProperty($accounts);
         * $cache->addProperty('budget');
         * $cache->addProperty('year');
         * if ($cache->has()) {
         * return Response::json($cache->get());
         * }
         *
         * $budgetInformation = $repository->getBudgetsAndExpensesPerMonth($accounts, $start, $end);
         * $budgets           = new Collection;
         * $entries           = new Collection;
         *
         * // @var array $row
         * foreach ($budgetInformation as $row) {
         * $budgets->push($row['budget']);
         * }
         * while ($start < $end) {
         * // month is the current end of the period:
         * $month = clone $start;
         * $month->endOfMonth();
         * $row           = [clone $start];
         * $dateFormatted = $start->format('Y-m');
         *
         * // each budget, check if there is an entry for this month:
         * // @var array $row
         * foreach ($budgetInformation as $budgetRow) {
         * $spent = 0; // nothing spent.
         * if (isset($budgetRow['entries'][$dateFormatted])) {
         * $spent = $budgetRow['entries'][$dateFormatted] * -1; // to fit array
         * }
         * $row[] = $spent;
         * }
         *
         * // add "no budget" thing.
         * $row[] = round(bcmul($repository->getWithoutBudgetSum($accounts, $start, $month), '-1'), 4);
         *
         * $entries->push($row);
         * $start->endOfMonth()->addDay();
         * }
         * $data = $this->generator->year($budgets, $entries);
         * $cache->store($data);
         *
         * return Response::json($data);
         */
    }
}
