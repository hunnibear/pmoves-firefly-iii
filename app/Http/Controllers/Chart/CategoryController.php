<?php

namespace FireflyIII\Http\Controllers\Chart;


use Carbon\Carbon;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\Category;
use FireflyIII\Repositories\Category\CategoryRepositoryInterface;
use FireflyIII\Support\CacheProperties;
use Illuminate\Support\Collection;
use Navigation;
use Preferences;
use Response;
use Session;

/**
 * Class CategoryController
 *
 * @package FireflyIII\Http\Controllers\Chart
 */
class CategoryController extends Controller
{
    /** @var  \FireflyIII\Generator\Chart\Category\CategoryChartGenerator */
    protected $generator;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        // create chart generator:
        $this->generator = app('FireflyIII\Generator\Chart\Category\CategoryChartGenerator');
    }


    /**
     * Show an overview for a category for all time, per month/week/year.
     *
     * @param CategoryRepositoryInterface $repository
     * @param Category                    $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function all(CategoryRepositoryInterface $repository, Category $category)
    {
        // oldest transaction in category:
        $start   = $repository->getFirstActivityDate($category);
        $range   = Preferences::get('viewRange', '1M')->data;
        $start   = Navigation::startOfPeriod($start, $range);
        $end     = new Carbon;
        $entries = new Collection;


        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('all');
        $cache->addProperty('categories');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        while ($start <= $end) {
            $currentEnd = Navigation::endOfPeriod($start, $range);
            $spent      = $repository->spentInPeriod($category, $start, $currentEnd);
            $earned     = $repository->earnedInPeriod($category, $start, $currentEnd);
            $date       = Navigation::periodShow($start, $range);
            $entries->push([clone $start, $date, $spent, $earned]);
            $start = Navigation::addPeriod($start, $range, 0);
        }
        // limit the set to the last 40:
        $entries = $entries->reverse();
        $entries = $entries->slice(0, 48);
        $entries = $entries->reverse();

        $data = $this->generator->all($entries);
        $cache->store($data);

        return Response::json($data);


    }

    /**
     * Show this month's category overview.
     *
     * @param CategoryRepositoryInterface $repository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frontpage(CategoryRepositoryInterface $repository)
    {

        $start = Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());

        // chart properties for cache:
        $cache = new CacheProperties;
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('category');
        $cache->addProperty('frontpage');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $array = $repository->getCategoriesAndExpenses($start, $end);
        // sort by callback:
        uasort(
            $array,
            function ($left, $right) {
                if ($left['sum'] == $right['sum']) {
                    return 0;
                }

                return ($left['sum'] < $right['sum']) ? -1 : 1;
            }
        );
        $set  = new Collection($array);
        $data = $this->generator->frontpage($set);
        $cache->store($data);


        return Response::json($data);

    }

    /**
     * @param CategoryRepositoryInterface $repository
     * @param                             $report_type
     * @param Carbon                      $start
     * @param Carbon                      $end
     * @param Collection                  $accounts
     * @param Collection                  $categories
     */
    public function multiYear(CategoryRepositoryInterface $repository, $report_type, Carbon $start, Carbon $end, Collection $accounts, Collection $categories)
    {
        // chart properties for cache:
        $cache = new CacheProperties();
        $cache->addProperty($report_type);
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($accounts);
        $cache->addProperty($categories);
        $cache->addProperty('multiYearCategory');

        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        /**
         *  category
         *   year:
         *    spent: x
         *    earned: x
         *   year
         *    spent: x
         *    earned: x
         */
        $entries = new Collection;
        // go by budget, not by year.
        /** @var Category $category */
        foreach ($categories as $category) {
            $entry = ['name' => '', 'spent' => [], 'earned' => []];

            $currentStart = clone $start;
            while ($currentStart < $end) {
                // fix the date:
                $currentEnd = clone $currentStart;
                $currentEnd->endOfYear();

                // get data:
                if (is_null($category->id)) {
                    $name   = trans('firefly.noCategory');
                    $spent  = $repository->spentNoCategoryForAccounts($accounts, $currentStart, $currentEnd);
                    $earned = $repository->earnedNoCategoryForAccounts($accounts, $currentStart, $currentEnd);
                } else {
                    $name   = $category->name;
                    $spent  = $repository->spentInPeriodForAccounts($category, $accounts, $currentStart, $currentEnd);
                    $earned = $repository->earnedInPeriodForAccounts($category, $accounts, $currentStart, $currentEnd);
                }

                // save to array:
                $year                   = $currentStart->year;
                $entry['name']          = $name;
                $entry['spent'][$year]  = ($spent * -1);
                $entry['earned'][$year] = $earned;

                // jump to next year.
                $currentStart = clone $currentEnd;
                $currentStart->addDay();
            }
            $entries->push($entry);
        }
        // generate chart with data:
        $data = $this->generator->multiYear($entries);
        $cache->store($data);


        return Response::json($data);

    }

    /**
     * @param CategoryRepositoryInterface $repository
     * @param Category                    $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function currentPeriod(CategoryRepositoryInterface $repository, Category $category)
    {
        $start = clone Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());

        // chart properties for cache:
        $cache = new CacheProperties;
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($category->id);
        $cache->addProperty('category');
        $cache->addProperty('currentPeriod');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }
        $entries = new Collection;


        while ($start <= $end) {
            $spent  = $repository->spentOnDaySum($category, $start);
            $earned = $repository->earnedOnDaySum($category, $start);
            $date   = Navigation::periodShow($start, '1D');
            $entries->push([clone $start, $date, $spent, $earned]);
            $start->addDay();
        }

        $data = $this->generator->period($entries);
        $cache->store($data);

        return Response::json($data);


    }

    /**
     * @param CategoryRepositoryInterface $repository
     * @param Category                    $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function specificPeriod(CategoryRepositoryInterface $repository, Category $category, $date)
    {
        $carbon = new Carbon($date);
        $range  = Preferences::get('viewRange', '1M')->data;
        $start  = Navigation::startOfPeriod($carbon, $range);
        $end    = Navigation::endOfPeriod($carbon, $range);

        // chart properties for cache:
        $cache = new CacheProperties;
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($category->id);
        $cache->addProperty('category');
        $cache->addProperty('specificPeriod');
        $cache->addProperty($date);
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }
        $entries = new Collection;


        while ($start <= $end) {
            $spent   = $repository->spentOnDaySum($category, $start);
            $earned  = $repository->earnedOnDaySum($category, $start);
            $theDate = Navigation::periodShow($start, '1D');
            $entries->push([clone $start, $theDate, $spent, $earned]);
            $start->addDay();
        }

        $data = $this->generator->period($entries);
        $cache->store($data);

        return Response::json($data);


    }

    /**
     * This chart will only show expenses.
     *
     * @param CategoryRepositoryInterface $repository
     * @param                             $report_type
     * @param Carbon                      $start
     * @param Carbon                      $end
     * @param Collection                  $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function spentInYear(CategoryRepositoryInterface $repository, $report_type, Carbon $start, Carbon $end, Collection $accounts)
    {

        $cache = new CacheProperties; // chart properties for cache:
        $cache->addProperty($start);
        $cache->addProperty($report_type);
        $cache->addProperty($end);
        $cache->addProperty($accounts);
        $cache->addProperty('category');
        $cache->addProperty('spent-in-year');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $allCategories = $repository->getCategories();
        $entries       = new Collection;
        $categories    = $allCategories->filter(
            function (Category $category) use ($repository, $start, $end, $accounts) {
                $spent = $repository->balanceInPeriod($category, $start, $end, $accounts);
                if ($spent < 0) {
                    return $category;
                }

                return null;
            }
        );

        while ($start < $end) {
            $month = clone $start; // month is the current end of the period
            $month->endOfMonth();
            $row = [clone $start]; // make a row:

            foreach ($categories as $category) { // each budget, fill the row
                $spent = $repository->balanceInPeriod($category, $start, $month, $accounts);
                if ($spent < 0) {
                    $row[] = $spent * -1;
                } else {
                    $row[] = 0;
                }
            }
            $entries->push($row);
            $start->addMonth();
        }
        $data = $this->generator->spentInYear($categories, $entries);
        $cache->store($data);

        return Response::json($data);
    }

    /**
     * This chart will only show income.
     *
     * @param CategoryRepositoryInterface $repository
     * @param                             $report_type
     * @param Carbon                      $start
     * @param Carbon                      $end
     * @param Collection                  $accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function earnedInYear(CategoryRepositoryInterface $repository, $report_type, Carbon $start, Carbon $end, Collection $accounts)
    {
        $cache = new CacheProperties; // chart properties for cache:
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty($report_type);
        $cache->addProperty($accounts);
        $cache->addProperty('category');
        $cache->addProperty('earned-in-year');
        if ($cache->has()) {
            return Response::json($cache->get()); // @codeCoverageIgnore
        }

        $allCategories = $repository->getCategories();
        $allEntries    = new Collection;
        $categories    = $allCategories->filter(
            function (Category $category) use ($repository, $start, $end, $accounts) {
                $spent = $repository->balanceInPeriod($category, $start, $end, $accounts);
                if ($spent > 0) {
                    return $category;
                }

                return null;
            }
        );

        while ($start < $end) {
            $month = clone $start; // month is the current end of the period
            $month->endOfMonth();
            $row = [clone $start]; // make a row:

            foreach ($categories as $category) { // each budget, fill the row
                $spent = $repository->balanceInPeriod($category, $start, $month, $accounts);
                if ($spent > 0) {
                    $row[] = $spent;
                } else {
                    $row[] = 0;
                }
            }
            $allEntries->push($row);
            $start->addMonth();
        }
        $data = $this->generator->earnedInYear($categories, $allEntries);
        $cache->store($data);

        return Response::json($data);
    }
}
