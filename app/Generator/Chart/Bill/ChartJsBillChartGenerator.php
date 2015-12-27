<?php

namespace FireflyIII\Generator\Chart\Bill;

use FireflyIII\Models\Bill;
use Illuminate\Support\Collection;

/**
 * Class ChartJsBillChartGenerator
 *
 * @package FireflyIII\Generator\Chart\Bill
 */
class ChartJsBillChartGenerator implements BillChartGenerator
{

    /**
     * @param string $paid
     * @param string $unpaid
     *
     * @return array
     */
    public function frontpage($paid, $unpaid)
    {
        bcscale(2);
        $data = [
            [
                'value'     => $unpaid,
                'color'     => 'rgba(53, 124, 165,0.7)',
                'highlight' => 'rgba(53, 124, 165,0.9)',
                'label'     => trans('firefly.unpaid'),
            ],
            [
                'value'     => $paid * -1, // paid is negative, must be positive.
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
        $format = trans('config.month');

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
            $actualAmount[]   = round(($entry->amount * -1), 2);
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
