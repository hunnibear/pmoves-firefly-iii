<?php

namespace FireflyIII\Generator\Chart\Account;

use Carbon\Carbon;
use Config;
use FireflyIII\Models\Account;
use Illuminate\Support\Collection;
use Preferences;
use Steam;

/**
 * Class ChartJsAccountChartGenerator
 *
 * @package FireflyIII\Generator\Chart\Account
 */
class ChartJsAccountChartGenerator implements AccountChartGenerator
{


    /**
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return array
     */
    public function all(Collection $accounts, Carbon $start, Carbon $end)
    {
        return $this->frontpage($accounts, $start, $end);
    }

    /**
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return array
     */
    public function frontpage(Collection $accounts, Carbon $start, Carbon $end)
    {
        // language:
        $language = Preferences::get('language', 'en')->data;
        $format   = Config::get('firefly.monthAndDay.' . $language);
        $data     = [
            'count'    => 0,
            'labels'   => [],
            'datasets' => [],
        ];
        $current  = clone $start;
        while ($current <= $end) {
            $data['labels'][] = $current->formatLocalized($format);
            $current->addDay();
        }

        foreach ($accounts as $account) {
            $set     = [
                'label'                => $account->name,
                'fillColor'            => 'rgba(220,220,220,0.2)',
                'strokeColor'          => 'rgba(220,220,220,1)',
                'pointColor'           => 'rgba(220,220,220,1)',
                'pointStrokeColor'     => '#fff',
                'pointHighlightFill'   => '#fff',
                'pointHighlightStroke' => 'rgba(220,220,220,1)',
                'data'                 => [],
            ];
            $current = clone $start;
            while ($current <= $end) {
                $set['data'][] = Steam::balance($account, $current);
                $current->addDay();
            }
            $data['datasets'][] = $set;
            $data['count']++;
        }

        return $data;
    }

    /**
     * @param Account $account
     * @param Carbon  $start
     * @param Carbon  $end
     *
     * @return array
     */
    public function single(Account $account, Carbon $start, Carbon $end)
    {
        // language:
        $language = Preferences::get('language', 'en')->data;
        $format   = Config::get('firefly.monthAndDay.' . $language);

        $data = [
            'count'    => 1,
            'labels'   => [],
            'datasets' => [
                [
                    'label' => $account->name,
                    'data'  => []
                ]
            ],
        ];

        $current = clone $start;

        while ($end >= $current) {
            $data['labels'][]              = $current->formatLocalized($format);
            $data['datasets'][0]['data'][] = Steam::balance($account, $current);
            $current->addDay();
        }

        return $data;
    }
}