<?php

namespace FireflyIII\Repositories\Category;

use Carbon\Carbon;
use FireflyIII\Models\Category;
use Illuminate\Support\Collection;

/**
 * Interface CategoryRepositoryInterface
 *
 * @package FireflyIII\Repositories\Category
 */
interface CategoryRepositoryInterface
{
    /**
     * @param Category $category
     *
     * @return int
     */
    public function countJournals(Category $category);

    /**
     * @param Category $category
     *
     * @return int
     */
    public function countJournalsInRange(Category $category, Carbon $start, Carbon $end);

    /**
     * @param Category $category
     *
     * @return boolean
     */
    public function destroy(Category $category);

    /**
     * @return Collection
     */
    public function getCategories();

    /**
     * Corrected for tags.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return array
     */
    public function getCategoriesAndExpenses(Carbon $start, Carbon $end);

    /**
     * @param Category $category
     *
     * @return Carbon
     */
    public function getFirstActivityDate(Category $category);

    /**
     * @param Category $category
     * @param int      $page
     *
     * @return Collection
     */
    public function getJournals(Category $category, $page);

    /**
     * @param Category $category
     * @param int      $page
     *
     * @return Collection
     */
    public function getJournalsInRange(Category $category, $page, Carbon $start, Carbon $end);

    /**
     * This method returns the sum of the journals in the category, optionally
     * limited by a start or end date.
     *
     * @param Category $category
     * @param Carbon   $start
     * @param Carbon   $end
     *
     * @return string
     */
    public function journalsSum(Category $category, Carbon $start = null, Carbon $end = null);

    /**
     * @param Category $category
     *
     * @return Carbon|null
     */
    public function getLatestActivity(Category $category);

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function getWithoutCategory(Carbon $start, Carbon $end);

    /**
     * Corrected for tags.
     *
     * @param Category       $category
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @param bool           $shared
     *
     * @return string
     */
    public function balanceInPeriod(Category $category, Carbon $start, Carbon $end, $shared = false);


    /**
     * Corrected for tags.
     *
     * @param Category       $category
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @param Collection     $accounts
     *
     * @return string
     */
    public function balanceInPeriodForList(Category $category, Carbon $start, Carbon $end, Collection $accounts);

    /**
     * @param Category       $category
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @param bool           $shared
     *
     * @return string
     */
    public function spentInPeriod(Category $category, Carbon $start, Carbon $end);

    /**
     * @param Category       $category
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @param bool           $shared
     *
     * @return string
     */
    public function earnedInPeriod(Category $category, Carbon $start, Carbon $end);

    /**
     *
     * Corrected for tags.
     *
     * @param Category $category
     * @param Carbon   $date
     *
     * @return float
     */
    public function spentOnDaySum(Category $category, Carbon $date);

    /**
     *
     * Corrected for tags.
     *
     * @param Category $category
     * @param Carbon   $date
     *
     * @return float
     */
    public function earnedOnDaySum(Category $category, Carbon $date);

    /**
     * @param array $data
     *
     * @return Category
     */
    public function store(array $data);

    /**
     * @param Category $category
     * @param array    $data
     *
     * @return Category
     */
    public function update(Category $category, array $data);

}
