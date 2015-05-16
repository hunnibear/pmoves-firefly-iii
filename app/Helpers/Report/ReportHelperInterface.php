<?php

namespace FireflyIII\Helpers\Report;

use Carbon\Carbon;
use FireflyIII\Helpers\Collection\Account;
use FireflyIII\Helpers\Collection\Expense;
use FireflyIII\Helpers\Collection\Income;
use FireflyIII\Helpers\Collection\Budget as BudgetCollection;
use FireflyIII\Helpers\Collection\Category as CategoryCollection;

/**
 * Interface ReportHelperInterface
 *
 * @package FireflyIII\Helpers\Report
 */
interface ReportHelperInterface
{

    /**
     * This method generates a full report for the given period on all
     * the users asset and cash accounts.
     *
     * @param Carbon  $date
     * @param Carbon  $end
     * @param boolean $shared
     *
     * @return Account
     */
    public function getAccountReport(Carbon $date, Carbon $end, $shared);

    /**
     * @param Carbon  $start
     * @param Carbon  $end
     * @param boolean $shared
     *
     * @return BudgetCollection
     */
    public function getBudgetReport(Carbon $start, Carbon $end, $shared);

    /**
     * @param Carbon  $start
     * @param Carbon  $end
     * @param boolean $shared
     *
     * @return CategoryCollection
     */
    public function getCategoryReport(Carbon $start, Carbon $end, $shared);

    /**
     * Get a full report on the users expenses during the period.
     *
     * @param Carbon  $start
     * @param Carbon  $end
     * @param boolean $shared
     *
     * @return Expense
     */
    public function getExpenseReport($start, $end, $shared);

    /**
     * Get a full report on the users incomes during the period.
     *
     * @param Carbon  $start
     * @param Carbon  $end
     * @param boolean $shared
     *
     * @return Income
     */
    public function getIncomeReport($start, $end, $shared);

    /**
     * @param Carbon $date
     *
     * @return array
     */
    public function listOfMonths(Carbon $date);

}
