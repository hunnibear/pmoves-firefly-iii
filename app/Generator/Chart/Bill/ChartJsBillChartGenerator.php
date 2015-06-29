<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 27/06/15
 * Time: 17:21
 */

namespace FireflyIII\Generator\Chart\Bill;

use Config;
use FireflyIII\Models\Bill;
use FireflyIII\Models\TransactionJournal;
use Illuminate\Support\Collection;
use Preferences;

/**
 * Class ChartJsBillChartGenerator
 *
 * @package FireflyIII\Generator\Chart\Bill
 */
class ChartJsBillChartGenerator implements BillChartGenerator
{

    /**
     * @param Collection $paid
     * @param Collection $unpaid
     *
     * @return array
     */
    public function frontpage(Collection $paid, Collection $unpaid)
    {

        // loop paid and create single entry:
        $paidDescriptions   = [];
        $paidAmount         = 0;
        $unpaidDescriptions = [];
        $unpaidAmount       = 0;


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

        $data = [
            [
                'value'     => $unpaidAmount,
                'color'     => 'rgba(53, 124, 165,0.7)',
                'highlight' => 'rgba(53, 124, 165,0.9)',
                'label'     => trans('firefly.unpaid'),
            ],
            [
                'value'     => $paidAmount,
                'color'     => 'rgba(0, 141, 76, 0.7)',
                'highlight' => 'rgba(0, 141, 76, 0.9)',
                'label'     => trans('firefly.paid'),
            ]
        ];

        return $data;
    }

    /**
     * @param Bill       $bill
     * @param Collection $entries
     *
     * @return array
     */
    public function single(Bill $bill, Collection $entries)
    {
        // language:
        $language = Preferences::get('language', 'en')->data;
        $format   = Config::get('firefly.month.' . $language);

        $data = [
            'count'    => 3,
            'labels'   => [],
            'datasets' => [],
        ];

        // dataset: max amount
        // dataset: min amount
        // dataset: actual amount

        $minAmount    = [];
        $maxAmount    = [];
        $actualAmount = [];
        foreach ($entries as $entry) {
            $data['labels'][] = $entry->date->formatLocalized($format);
            $minAmount[]      = round($bill->amount_min, 2);
            $maxAmount[]      = round($bill->amount_max, 2);
            $actualAmount[]   = round($entry->amount, 2);
        }

        $data['datasets'][] = [
            'label' => trans('firefly.minAmount'),
            'data'  => $minAmount,
        ];
        $data['datasets'][] = [
            'label' => trans('firefly.billEntry'),
            'data'  => $actualAmount,
        ];
        $data['datasets'][] = [
            'label' => trans('firefly.maxAmount'),
            'data'  => $maxAmount,
        ];

        $data['count'] = count($data['datasets']);

        return $data;
    }
}
