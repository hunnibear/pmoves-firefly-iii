<?php namespace FireflyIII\Http\Controllers;

use Carbon\Carbon;
use Crypt;
use FireflyIII\Helpers\Report\ReportQueryInterface;
use FireflyIII\Models\Account;
use FireflyIII\Models\Bill;
use FireflyIII\Models\Budget;
use FireflyIII\Models\Category;
use FireflyIII\Models\LimitRepetition;
use FireflyIII\Models\PiggyBank;
use FireflyIII\Models\Preference;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Bill\BillRepositoryInterface;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Repositories\Category\CategoryRepositoryInterface;
use FireflyIII\Repositories\PiggyBank\PiggyBankRepositoryInterface;
use Grumpydictator\Gchart\GChart;
use Illuminate\Support\Collection;
use Navigation;
use Preferences;
use Response;
use Session;
use Steam;

/**
 * Class GoogleChartController
 *
 * @package FireflyIII\Http\Controllers
 */
class GoogleChartController extends Controller
{


    /**
     * @param Account $account
     * @param string  $view
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountBalanceChart(Account $account, GChart $chart)
    {
        $chart->addColumn('Day of month', 'date');
        $chart->addColumn('Balance for ' . $account->name, 'number');
        $chart->addCertainty(1);

        $start   = Session::get('start', Carbon::now()->startOfMonth());
        $end     = Session::get('end', Carbon::now()->endOfMonth());
        $current = clone $start;
        $today   = new Carbon;

        while ($end >= $current) {
            $certain = $current < $today;
            $chart->addRow(clone $current, Steam::balance($account, $current), $certain);
            $current->addDay();
        }


        $chart->generate();

        return Response::json($chart->getData());
    }

    /**
     * @param GChart $chart
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allAccountsBalanceChart(GChart $chart, AccountRepositoryInterface $repository)
    {
        $chart->addColumn('Day of the month', 'date');

        $frontPage = Preferences::get('frontPageAccounts', []);
        $start     = Session::get('start', Carbon::now()->startOfMonth());
        $end       = Session::get('end', Carbon::now()->endOfMonth());
        $accounts  = $repository->getFrontpageAccounts($frontPage);

        $index = 1;
        /** @var Account $account */
        foreach ($accounts as $account) {
            $chart->addColumn('Balance for ' . $account->name, 'number');
            $chart->addCertainty($index);
            $index++;
        }
        $current = clone $start;
        $current->subDay();
        $today = Carbon::now();
        while ($end >= $current) {
            $row     = [clone $current];
            $certain = $current < $today;
            foreach ($accounts as $account) {
                $row[] = Steam::balance($account, $current);
                $row[] = $certain;
            }
            $chart->addRowArray($row);
            $current->addDay();
        }
        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     * @param int $year
     *
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function allBudgetsAndSpending($year, GChart $chart, BudgetRepositoryInterface $repository)
    {
        $budgets = $repository->getBudgets();
        $chart->addColumn('Month', 'date');
        foreach ($budgets as $budget) {
            $chart->addColumn($budget->name, 'number');
        }

        $start = Carbon::createFromDate(intval($year), 1, 1);
        $end   = clone $start;
        $end->endOfYear();

        while ($start <= $end) {
            $row = [clone $start];
            foreach ($budgets as $budget) {
                $spent = $repository->spentInMonth($budget, $start);
                $row[] = $spent;
            }
            $chart->addRowArray($row);
            $start->addMonth();
        }

        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     * @param GChart $chart
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allBudgetsHomeChart(GChart $chart, BudgetRepositoryInterface $repository)
    {
        $chart->addColumn('Budget', 'string');
        $chart->addColumn('Left', 'number');
        //$chart->addColumn('Spent', 'number');

        $budgets    = $repository->getBudgets();
        $start      = Session::get('start', Carbon::now()->startOfMonth());
        $end        = Session::get('end', Carbon::now()->endOfMonth());
        $allEntries = new Collection;

        foreach ($budgets as $budget) {
            $repetitions = $repository->getBudgetLimitRepetitions($budget, $start, $end);
            if ($repetitions->count() == 0) {
                $expenses = $repository->sumBudgetExpensesInPeriod($budget, $start, $end);
                $allEntries->push([$budget->name, 0, $expenses]);
                continue;
            }
            /** @var LimitRepetition $repetition */
            foreach ($repetitions as $repetition) {
                $expenses = $repository->sumBudgetExpensesInPeriod($budget, $repetition->startdate, $repetition->enddate);
                $allEntries->push([$budget->name . ' (' . $repetition->startdate->format('j M Y') . ')', floatval($repetition->amount), $expenses]);
            }
        }

        $noBudgetExpenses = $repository->getWithoutBudgetSum($start, $end);
        $allEntries->push(['(no budget)', 0, $noBudgetExpenses]);

        foreach ($allEntries as $entry) {
            if ($entry[2] > 0) {
                $left = $entry[1] - $entry[2];
                $chart->addRow($entry[0], $left);
            }
        }

        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     * @param GChart $chart
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allCategoriesHomeChart(GChart $chart, CategoryRepositoryInterface $repository)
    {
        $chart->addColumn('Category', 'string');
        $chart->addColumn('Spent', 'number');

        $start = Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());
        $set   = $repository->getCategoriesAndExpenses($start, $end);

        foreach ($set as $entry) {
            $isEncrypted = intval($entry->encrypted) == 1 ? true : false;
            $name        = strlen($entry->name) == 0 ? '(no category)' : $entry->name;
            $name        = $isEncrypted ? Crypt::decrypt($name) : $name;
            $chart->addRow($name, floatval($entry->sum));
        }

        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     * @param Bill   $bill
     * @param GChart $chart
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function billOverview(Bill $bill, GChart $chart, BillRepositoryInterface $repository)
    {

        $chart->addColumn('Date', 'date');
        $chart->addColumn('Max amount', 'number');
        $chart->addColumn('Min amount', 'number');
        $chart->addColumn('Recorded bill entry', 'number');

        // get first transaction or today for start:
        $results = $repository->getJournals($bill);
        /** @var TransactionJournal $result */
        foreach ($results as $result) {
            $chart->addRow(clone $result->date, floatval($bill->amount_max), floatval($bill->amount_min), floatval($result->amount));
        }

        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     * @param GChart $chart
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function billsOverview(GChart $chart, BillRepositoryInterface $repository, AccountRepositoryInterface $accounts)
    {
        $chart->addColumn('Name', 'string');
        $chart->addColumn('Amount', 'number');

        $start  = Session::get('start', Carbon::now()->startOfMonth());
        $end    = Session::get('end', Carbon::now()->endOfMonth());
        $bills  = $repository->getActiveBills();
        $paid   = new Collection; // journals.
        $unpaid = new Collection; // bills
        // loop paid and create single entry:
        $paidDescriptions   = [];
        $paidAmount         = 0;
        $unpaidDescriptions = [];
        $unpaidAmount       = 0;

        /** @var Bill $bill */
        foreach ($bills as $bill) {
            $ranges = $repository->getRanges($bill, $start, $end);

            foreach ($ranges as $range) {
                // paid a bill in this range?
                $journals = $repository->getJournalsInRange($bill, $range['start'], $range['end']);
                if ($journals->count() == 0) {
                    $unpaid->push([$bill, $range['start']]);
                } else {
                    $paid = $paid->merge($journals);
                }

            }
        }

        $creditCards = $accounts->getCreditCards();
        foreach ($creditCards as $creditCard) {
            $balance = Steam::balance($creditCard, null, true);
            $date    = new Carbon($creditCard->getMeta('ccMonthlyPaymentDate'));
            if ($balance < 0) {
                // unpaid! create a fake bill that matches the amount.
                $description = $creditCard->name;
                $amount      = $balance * -1;
                $fakeBill    = $repository->createFakeBill($description, $date, $amount);
                unset($description, $amount);
                $unpaid->push([$fakeBill, $date]);
            }
            if ($balance == 0) {
                // find transfer(s) TO the credit card which should account for
                // anything paid. If not, the CC is not yet used.
                $journals = $accounts->getTransfersInRange($creditCard, $start, $end);
                $paid     = $paid->merge($journals);
            }
        }


        /** @var TransactionJournal $entry */
        foreach ($paid as $entry) {

            $paidDescriptions[] = $entry->description;
            $paidAmount += floatval($entry->amount);
        }

        // loop unpaid:
        /** @var Bill $entry */
        foreach ($unpaid as $entry) {
            $description          = $entry[0]->name . ' (' . $entry[1]->format('jS M Y') . ')';
            $amount               = ($entry[0]->amount_max + $entry[0]->amount_min) / 2;
            $unpaidDescriptions[] = $description;
            $unpaidAmount += $amount;
            unset($amount, $description);
        }

        $chart->addRow('Unpaid: ' . join(', ', $unpaidDescriptions), $unpaidAmount);
        $chart->addRow('Paid: ' . join(', ', $paidDescriptions), $paidAmount);
        $chart->generate();

        return Response::json($chart->getData());
    }

    /**
     *
     * @param Budget          $budget
     * @param LimitRepetition $repetition
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function budgetLimitSpending(Budget $budget, LimitRepetition $repetition, GChart $chart, BudgetRepositoryInterface $repository)
    {
        $start = clone $repetition->startdate;
        $end   = $repetition->enddate;

        $chart->addColumn('Day', 'date');
        $chart->addColumn('Left', 'number');


        $amount = $repetition->amount;

        while ($start <= $end) {
            /*
             * Sum of expenses on this day:
             */
            $sum = $repository->expensesOnDay($budget, $start);
            $amount += $sum;
            $chart->addRow(clone $start, $amount);
            $start->addDay();
        }
        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     * @param Budget                    $budget
     * @param int                       $year
     * @param GChart                    $chart
     * @param BudgetRepositoryInterface $repository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function budgetsAndSpending(GChart $chart, BudgetRepositoryInterface $repository, Budget $budget, $year = 0)
    {
        $chart->addColumn('Month', 'date');
        $chart->addColumn('Budgeted', 'number');
        $chart->addColumn('Spent', 'number');

        if ($year == 0) {
            $start = $repository->getFirstBudgetLimitDate($budget);
            $end   = $repository->getLastBudgetLimitDate($budget);
        } else {
            $start = Carbon::createFromDate(intval($year), 1, 1);
            $end   = clone $start;
            $end->endOfYear();
        }

        while ($start <= $end) {
            $spent    = $repository->spentInMonth($budget, $start);
            $budgeted = $repository->getLimitAmountOnDate($budget, $start);
            $chart->addRow(clone $start, $budgeted, $spent);
            $start->addMonth();
        }

        $chart->generate();

        return Response::json($chart->getData());


    }

    /**
     *
     * @param Category $category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryOverviewChart(Category $category, GChart $chart, CategoryRepositoryInterface $repository)
    {
        // oldest transaction in category:
        $start = $repository->getFirstActivityDate($category);

        /** @var Preference $range */
        $range = Preferences::get('viewRange', '1M');
        // jump to start of week / month / year / etc (TODO).
        $start = Navigation::startOfPeriod($start, $range->data);

        $chart->addColumn('Period', 'date');
        $chart->addColumn('Spent', 'number');

        $end = new Carbon;
        while ($start <= $end) {

            $currentEnd = Navigation::endOfPeriod($start, $range->data);
            $spent      = $repository->spentInPeriodSum($category, $start, $currentEnd);
            $chart->addRow(clone $start, $spent);

            $start = Navigation::addPeriod($start, $range->data, 0);
        }

        $chart->generate();

        return Response::json($chart->getData());


    }

    /**
     *
     * @param Category $category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryPeriodChart(Category $category, GChart $chart, CategoryRepositoryInterface $repository)
    {
        $start = clone Session::get('start', Carbon::now()->startOfMonth());
        $chart->addColumn('Period', 'date');
        $chart->addColumn('Spent', 'number');

        $end = Session::get('end', Carbon::now()->endOfMonth());
        while ($start <= $end) {
            $spent = $repository->spentOnDaySum($category, $start);
            $chart->addRow(clone $start, $spent);
            $start->addDay();
        }

        $chart->generate();

        return Response::json($chart->getData());


    }


    /**
     * @param PiggyBank $piggyBank
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function piggyBankHistory(PiggyBank $piggyBank, GChart $chart, PiggyBankRepositoryInterface $repository)
    {
        $chart->addColumn('Date', 'date');
        $chart->addColumn('Balance', 'number');

        /** @var Collection $set */
        $set = $repository->getEventSummarySet($piggyBank);
        $sum = 0;

        foreach ($set as $entry) {
            $sum += floatval($entry->sum);
            $chart->addRow(new Carbon($entry->date), $sum);
        }

        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     *
     * @param $year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearInExp($year, GChart $chart, ReportQueryInterface $query)
    {
        $start = new Carbon('01-01-' . $year);
        $chart->addColumn('Month', 'date');
        $chart->addColumn('Income', 'number');
        $chart->addColumn('Expenses', 'number');

        $pref              = Preferences::get('showSharedReports', false);
        $showSharedReports = $pref->data;

        // get report query interface.

        $end = clone $start;
        $end->endOfYear();
        while ($start < $end) {
            $currentEnd = clone $start;
            $currentEnd->endOfMonth();
            // total income && total expenses:
            $incomeSum  = floatval($query->incomeByPeriod($start, $currentEnd, $showSharedReports)->sum('queryAmount'));
            $expenseSum = floatval($query->journalsByExpenseAccount($start, $currentEnd, $showSharedReports)->sum('queryAmount'));

            $chart->addRow(clone $start, $incomeSum, $expenseSum);
            $start->addMonth();
        }


        $chart->generate();

        return Response::json($chart->getData());

    }

    /**
     *
     * @param $year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearInExpSum($year, GChart $chart, ReportQueryInterface $query)
    {
        $start = new Carbon('01-01-' . $year);
        $chart->addColumn('Summary', 'string');
        $chart->addColumn('Income', 'number');
        $chart->addColumn('Expenses', 'number');

        $pref              = Preferences::get('showSharedReports', false);
        $showSharedReports = $pref->data;

        $income  = 0;
        $expense = 0;
        $count   = 0;

        $end = clone $start;
        $end->endOfYear();
        while ($start < $end) {
            $currentEnd = clone $start;
            $currentEnd->endOfMonth();
            // total income:
            $incomeSum = floatval($query->incomeByPeriod($start, $currentEnd, $showSharedReports)->sum('queryAmount'));
            // total expenses:
            $expenseSum = floatval($query->journalsByExpenseAccount($start, $currentEnd, $showSharedReports)->sum('queryAmount'));

            $income += $incomeSum;
            $expense += $expenseSum;
            $count++;
            $start->addMonth();
        }


        $chart->addRow('Sum', $income, $expense);
        $count = $count > 0 ? $count : 1;
        $chart->addRow('Average', ($income / $count), ($expense / $count));

        $chart->generate();

        return Response::json($chart->getData());

    }


}
