<?php

namespace FireflyIII\Helpers\Report;

use Carbon\Carbon;
use FireflyIII\Models\Account;
use FireflyIII\Models\Budget;
use Illuminate\Support\Collection;

/**
 * Interface ReportQueryInterface
 *
 * @package FireflyIII\Helpers\Report
 */
interface ReportQueryInterface
{

    /**
     * Get a users accounts combined with various meta-data related to the start and end date.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param bool   $includeShared
     *
     * @return Collection
     */
    public function getAllAccounts(Carbon $start, Carbon $end, $includeShared = false);


    /**
     * This method returns all "income" journals in a certain period, which are both transfers from a shared account
     * and "ordinary" deposits. The query used is almost equal to ReportQueryInterface::journalsByRevenueAccount but it does
     * not group and returns different fields.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param bool   $includeShared
     *
     * @return Collection
     *
     */
    public function incomeInPeriod(Carbon $start, Carbon $end, $includeShared = false);

    /**
     * This method returns all "expense" journals in a certain period, which are both transfers to a shared account
     * and "ordinary" withdrawals. The query used is almost equal to ReportQueryInterface::journalsByRevenueAccount but it does
     * not group and returns different fields.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param bool   $includeShared
     *
     * @return Collection
     *
     */
    public function expenseInPeriod(Carbon $start, Carbon $end, $includeShared = false);


    /**
     * @param Account $account
     * @param Budget  $budget
     * @param Carbon  $start
     * @param Carbon  $end
     * @param bool    $shared
     *
     * @return float
     */
    public function spentInBudget(Account $account, Budget $budget, Carbon $start, Carbon $end, $shared = false);

    /**
     * @param Account $account
     * @param Carbon  $start
     * @param Carbon  $end
     * @param bool    $shared
     *
     * @return float
     */
    public function spentNoBudget(Account $account, Carbon $start, Carbon $end, $shared = false);


}
