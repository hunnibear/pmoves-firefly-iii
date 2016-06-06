<?php
/**
 * AssetAccountNumber.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);
namespace FireflyIII\Helpers\Csv\Converter;

use Auth;
use Carbon\Carbon;
use FireflyIII\Models\Account;
use FireflyIII\Models\AccountType;

/**
 * Class AssetAccountNumber
 *
 * @package FireflyIII\Helpers\Csv\Converter
 */
class AssetAccountNumber extends BasicConverter implements ConverterInterface
{

    /**
     * @return Account|null
     */
    public function convert(): Account
    {
        $crud = app('FireflyIII\Crud\Account\AccountCrudInterface');

        // is mapped? Then it's easy!
        if (isset($this->mapped[$this->index][$this->value])) {
            $account = $crud->find(intval($this->mapped[$this->index][$this->value]));

            return $account;
        }
        // if not, search for it (or create it):
        $value = $this->value ?? '';
        if (strlen($value) > 0) {
            // find or create new account:
            $set = $crud->getAccountsByType([AccountType::DEFAULT, AccountType::ASSET]);
            /** @var Account $entry */
            foreach ($set as $entry) {
                $accountNumber = $entry->getMeta('accountNumber');
                if ($accountNumber == $this->value) {

                    return $entry;
                }
            }

            $accountData = [
                'name'                   => $this->value,
                'accountType'            => 'asset',
                'virtualBalance'         => 0,
                'virtualBalanceCurrency' => 1, // hard coded.
                'active'                 => true,
                'user'                   => Auth::user()->id,
                'iban'                   => null,
                'accountNumber'          => $this->value,
                'accountRole'            => null,
                'openingBalance'         => 0,
                'openingBalanceDate'     => new Carbon,
                'openingBalanceCurrency' => 1, // hard coded.

            ];

            $account = $crud->store($accountData);

            return $account;
        }

        return null; // is this accepted?
    }

}
