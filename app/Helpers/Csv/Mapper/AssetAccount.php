<?php
/**
 * AssetAccount.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);
namespace FireflyIII\Helpers\Csv\Mapper;

use Auth;
use FireflyIII\Models\Account;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class AssetAccount
 *
 * @package FireflyIII\Helpers\Csv\Mapper
 */
class AssetAccount implements MapperInterface
{

    /**
     * @return array
     */
    public function getMap(): array
    {
        $result = Auth::user()->accounts()->with(
            ['accountmeta' => function (HasMany $query) {
                $query->where('name', 'accountRole');
            }]
        )->accountTypeIn(['Default account', 'Asset account'])->orderBy('accounts.name', 'ASC')->get(['accounts.*']);

        $list = [];

        /** @var Account $account */
        foreach ($result as $account) {
            $name = $account->name;
            $iban = $account->iban ?? '';
            if (strlen($iban) > 0) {
                $name .= ' (' . $account->iban . ')';
            }
            $list[$account->id] = $name;
        }

        asort($list);

        $list = [0 => trans('firefly.csv_do_not_map')] + $list;

        return $list;
    }
}
