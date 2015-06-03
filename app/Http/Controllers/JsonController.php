<?php namespace FireflyIII\Http\Controllers;

use Amount;
use Cache;
use Carbon\Carbon;
use FireflyIII\Helpers\Report\ReportQueryInterface;
use FireflyIII\Models\Account;
use FireflyIII\Models\Bill;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Bill\BillRepositoryInterface;
use FireflyIII\Repositories\Category\CategoryRepositoryInterface;
use FireflyIII\Repositories\Journal\JournalRepositoryInterface;
use FireflyIII\Repositories\Tag\TagRepositoryInterface;
use FireflyIII\Support\CacheProperties;
use Illuminate\Support\Collection;
use Log;
use Response;
use Session;
use Steam;

/**
 * Class JsonController
 *
 * @package FireflyIII\Http\Controllers
 */
class JsonController extends Controller
{


    /**
     * @param BillRepositoryInterface    $repository
     *
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boxBillsPaid(BillRepositoryInterface $repository, AccountRepositoryInterface $accountRepository)
    {
        $start = Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());

        // works for json too!
        $prop = new CacheProperties;
        $prop->addProperty($start);
        $prop->addProperty($end);
        $prop->addProperty('box-bills-paid');
        $md5 = $prop->md5();
        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached box bills-paid [' . $md5 . ']');

            return Response::json(Cache::get($md5));
        }

        $amount = 0;


        // these two functions are the same as the chart
        $bills = $repository->getActiveBills();

        /** @var Bill $bill */
        foreach ($bills as $bill) {
            $amount += $repository->billPaymentsInRange($bill, $start, $end);
        }
        unset($bill, $bills);

        /**
         * Find credit card accounts and possibly unpaid credit card bills.
         */
        $creditCards = $accountRepository->getCreditCards();
        // if the balance is not zero, the monthly payment is still underway.
        /** @var Account $creditCard */
        foreach ($creditCards as $creditCard) {
            $balance = Steam::balance($creditCard, $end, true);
            if ($balance == 0) {
                // find a transfer TO the credit card which should account for
                // anything paid. If not, the CC is not yet used.
                $amount += $accountRepository->getTransfersInRange($creditCard, $start, $end)->sum('amount');
            }
        }
        $data = ['box' => 'bills-paid', 'amount' => Amount::format($amount, false), 'amount_raw' => $amount];
        Cache::forever($md5, $data);

        return Response::json($data);
    }

    /**
     * @param BillRepositoryInterface    $repository
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boxBillsUnpaid(BillRepositoryInterface $repository, AccountRepositoryInterface $accountRepository)
    {
        $amount = 0;
        $start  = Session::get('start', Carbon::now()->startOfMonth());
        $end    = Session::get('end', Carbon::now()->endOfMonth());

        // works for json too!
        $prop = new CacheProperties;
        $prop->addProperty($start);
        $prop->addProperty($end);
        $prop->addProperty('box-bills-unpaid');
        $md5 = $prop->md5();
        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached box bills-unpaid [' . $md5 . ']');

            return Response::json(Cache::get($md5));
        }

        $bills  = $repository->getActiveBills();
        $unpaid = new Collection; // bills

        /** @var Bill $bill */
        foreach ($bills as $bill) {
            $ranges = $repository->getRanges($bill, $start, $end);

            foreach ($ranges as $range) {
                $journals = $repository->getJournalsInRange($bill, $range['start'], $range['end']);
                if ($journals->count() == 0) {
                    $unpaid->push([$bill, $range['start']]);
                }
            }
        }
        unset($bill, $bills, $range, $ranges);

        $creditCards = $accountRepository->getCreditCards();
        foreach ($creditCards as $creditCard) {
            $balance = Steam::balance($creditCard, $end, true);
            $date    = new Carbon($creditCard->getMeta('ccMonthlyPaymentDate'));
            if ($balance < 0) {
                // unpaid! create a fake bill that matches the amount.
                $description = $creditCard->name;
                $fakeAmount  = $balance * -1;
                $fakeBill    = $repository->createFakeBill($description, $date, $fakeAmount);
                $unpaid->push([$fakeBill, $date]);
            }
        }
        /** @var Bill $entry */
        foreach ($unpaid as $entry) {
            $current = ($entry[0]->amount_max + $entry[0]->amount_min) / 2;
            $amount += $current;
        }

        $data = ['box' => 'bills-unpaid', 'amount' => Amount::format($amount, false), 'amount_raw' => $amount];
        Cache::forever($md5, $data);

        return Response::json($data);
    }

    /**
     * @param ReportQueryInterface $reportQuery
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boxIn(ReportQueryInterface $reportQuery)
    {
        $start = Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());

        // works for json too!
        $prop = new CacheProperties;
        $prop->addProperty($start);
        $prop->addProperty($end);
        $prop->addProperty('box-in');
        $md5 = $prop->md5();
        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached box in [' . $md5 . ']');

            return Response::json(Cache::get($md5));
        }


        $amount = $reportQuery->incomeInPeriodCorrected($start, $end, true)->sum('amount');

        $data = ['box' => 'in', 'amount' => Amount::format($amount, false), 'amount_raw' => $amount];
        Cache::forever($md5, $data);

        return Response::json($data);
    }

    /**
     * @param ReportQueryInterface $reportQuery
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boxOut(ReportQueryInterface $reportQuery)
    {
        $start = Session::get('start', Carbon::now()->startOfMonth());
        $end   = Session::get('end', Carbon::now()->endOfMonth());


        // works for json too!
        $prop = new CacheProperties;
        $prop->addProperty($start);
        $prop->addProperty($end);
        $prop->addProperty('box-out');
        $md5 = $prop->md5();
        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached box out [' . $md5 . ']');

            return Response::json(Cache::get($md5));
        }

        $amount = $reportQuery->expenseInPeriodCorrected($start, $end, true)->sum('amount');

        $data = ['box' => 'out', 'amount' => Amount::format($amount, false), 'amount_raw' => $amount];
        Cache::forever($md5, $data);

        return Response::json($data);
    }

    /**
     * Returns a list of categories.
     *
     * @param CategoryRepositoryInterface $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(CategoryRepositoryInterface $repository)
    {
        $list   = $repository->getCategories();
        $return = [];
        foreach ($list as $entry) {
            $return[] = $entry->name;
        }
        sort($return);

        return Response::json($return);
    }

    /**
     * Returns a JSON list of all beneficiaries.
     *
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function expenseAccounts(AccountRepositoryInterface $accountRepository)
    {
        $list   = $accountRepository->getAccounts(['Expense account', 'Beneficiary account']);
        $return = [];
        foreach ($list as $entry) {
            $return[] = $entry->name;
        }

        return Response::json($return);

    }

    /**
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revenueAccounts(AccountRepositoryInterface $accountRepository)
    {
        $list   = $accountRepository->getAccounts(['Revenue account']);
        $return = [];
        foreach ($list as $entry) {
            $return[] = $entry->name;
        }

        return Response::json($return);

    }

    /**
     * Returns a JSON list of all beneficiaries.
     *
     * @param TagRepositoryInterface $tagRepository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tags(TagRepositoryInterface $tagRepository)
    {
        $list   = $tagRepository->get();
        $return = [];
        foreach ($list as $entry) {
            $return[] = $entry->tag;
        }

        return Response::json($return);

    }

    /**
     * @param JournalRepositoryInterface $repository
     * @param                            $what
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transactionJournals(JournalRepositoryInterface $repository, $what)
    {
        $descriptions = [];
        $dbType       = $repository->getTransactionType($what);

        $journals = $repository->getJournalsOfType($dbType);
        foreach ($journals as $j) {
            $descriptions[] = $j->description;
        }

        $descriptions = array_unique($descriptions);
        sort($descriptions);

        return Response::json($descriptions);


    }

}
