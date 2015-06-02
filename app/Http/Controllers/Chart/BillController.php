<?php

namespace FireflyIII\Http\Controllers\Chart;

use Carbon\Carbon;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\Bill;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Bill\BillRepositoryInterface;
use FireflyIII\Support\ChartProperties;
use Grumpydictator\Gchart\GChart;
use Illuminate\Support\Collection;
use Response;
use Session;
use Steam;
use Cache;
use Log;
/**
 * Class BillController
 *
 * @package FireflyIII\Http\Controllers\Chart
 */
class BillController extends Controller
{
    /**
     * Shows the overview for a bill. The min/max amount and matched journals.
     *
     * @param GChart                  $chart
     * @param BillRepositoryInterface $repository
     * @param Bill                    $bill
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function single(GChart $chart, BillRepositoryInterface $repository, Bill $bill)
    {

        $chart->addColumn(trans('firefly.date'), 'date');
        $chart->addColumn(trans('firefly.maxAmount'), 'number');
        $chart->addColumn(trans('firefly.minAmount'), 'number');
        $chart->addColumn(trans('firefly.billEntry'), 'number');

        $chartProperties = new ChartProperties;
        $chartProperties->addProperty('single');
        $chartProperties->addProperty('bill');
        $chartProperties->addProperty($bill->id);
        $md5 = $chartProperties->md5();

        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached chart [' . $md5 . '].');

            return Response::json(Cache::get($md5));
        }

        // get first transaction or today for start:
        $results = $repository->getJournals($bill);
        /** @var TransactionJournal $result */
        foreach ($results as $result) {
            $chart->addRow(clone $result->date, floatval($bill->amount_max), floatval($bill->amount_min), floatval($result->amount));
        }

        $chart->generate();

        $data = $chart->getData();
        Cache::forever($md5, $data);

        return Response::json($data);
    }

    /**
     * Shows all bills and whether or not theyve been paid this month (pie chart).
     *
     * @param GChart                     $chart
     *
     * @param BillRepositoryInterface    $repository
     * @param AccountRepositoryInterface $accounts
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frontpage(GChart $chart, BillRepositoryInterface $repository, AccountRepositoryInterface $accounts)
    {
        $chart->addColumn(trans('firefly.name'), 'string');
        $chart->addColumn(trans('firefly.amount'), 'number');

        $start  = Session::get('start', Carbon::now()->startOfMonth());
        $end    = Session::get('end', Carbon::now()->endOfMonth());


        // chart properties for cache:
        $chartProperties = new ChartProperties();
        $chartProperties->addProperty($start);
        $chartProperties->addProperty($end);
        $chartProperties->addProperty('bills');
        $chartProperties->addProperty('frontpage');
        $md5 = $chartProperties->md5();

        if (Cache::has($md5)) {
            Log::debug('Successfully returned cached chart [' . $md5 . '].');

            return Response::json(Cache::get($md5));
        }

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
            $balance = Steam::balance($creditCard, $end, true);
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

        $chart->addRow(trans('firefly.unpaid') . ': ' . join(', ', $unpaidDescriptions), $unpaidAmount);
        $chart->addRow(trans('firefly.paid') . ': ' . join(', ', $paidDescriptions), $paidAmount);

        $chart->generate();

        $data = $chart->getData();
        Cache::forever($md5, $data);

        return Response::json($data);
    }
}
