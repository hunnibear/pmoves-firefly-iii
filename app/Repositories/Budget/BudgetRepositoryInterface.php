<?php

namespace FireflyIII\Repositories\Budget;

use Carbon\Carbon;
use FireflyIII\Models\Budget;
use FireflyIII\Models\LimitRepetition;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface BudgetRepositoryInterface
 *
 * @package FireflyIII\Repositories\Budget
 */
interface BudgetRepositoryInterface
{
    /**
     * @return void
     */
    public function cleanupBudgets();

    /**
     * @param Budget $budget
     *
     * @return boolean
     */
    public function destroy(Budget $budget);

    /**
     * Takes tags into account.
     *
     * @param Budget $budget
     * @param Carbon $date
     *
     * @return float
     */
    public function expensesOnDayCorrected(Budget $budget, Carbon $date);

    /**
     * @return Collection
     */
    public function getActiveBudgets();

    /**
     * @param Budget $budget
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function getBudgetLimitRepetitions(Budget $budget, Carbon $start, Carbon $end);

    /**
     * @param Budget $budget
     *
     * @return Collection
     */
    public function getBudgetLimits(Budget $budget);

    /**
     * @return Collection
     */
    public function getBudgets();

    /**
     * @param Budget $budget
     * @param Carbon $date
     *
     * @return LimitRepetition|null
     */
    public function getCurrentRepetition(Budget $budget, Carbon $date);

    /**
     * @param Budget $budget
     *
     * @return Carbon
     */
    public function getFirstBudgetLimitDate(Budget $budget);

    /**
     * @return Collection
     */
    public function getInactiveBudgets();

    /**
     * Returns all the transaction journals for a limit, possibly limited by a limit repetition.
     *
     * @param Budget          $budget
     * @param LimitRepetition $repetition
     * @param int             $take
     *
     * @return LengthAwarePaginator
     */
    public function getJournals(Budget $budget, LimitRepetition $repetition = null, $take = 50);

    /**
     * @param Budget $budget
     *
     * @return Carbon
     */
    public function getLastBudgetLimitDate(Budget $budget);

    /**
     * @param Budget $budget
     * @param Carbon $date
     *
     * @return float
     */
    public function getLimitAmountOnDate(Budget $budget, Carbon $date);

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function getWithoutBudget(Carbon $start, Carbon $end);

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function getWithoutBudgetSum(Carbon $start, Carbon $end);

    /**
     *
     * Same as ::spentInPeriod but corrects journals for their amount (tags).
     *
     * @param Budget  $budget
     * @param Carbon  $start
     * @param Carbon  $end
     * @param boolean $shared
     *
     * @return float
     */
    public function spentInPeriodCorrected(Budget $budget, Carbon $start, Carbon $end, $shared = true);

    /**
     * @param array $data
     *
     * @return Budget
     */
    public function store(array $data);

    /**
     * @param Budget $budget
     * @param array  $data
     *
     * @return Budget
     */
    public function update(Budget $budget, array $data);

    /**
     * @param Budget $budget
     * @param Carbon $date
     * @param        $amount
     *
     * @return mixed
     */
    public function updateLimitAmount(Budget $budget, Carbon $date, $amount);

}
