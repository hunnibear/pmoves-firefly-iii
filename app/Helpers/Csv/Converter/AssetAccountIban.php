<?php
declare(strict_types = 1);
namespace FireflyIII\Helpers\Csv\Converter;

use Auth;
use Carbon\Carbon;
use FireflyIII\Models\Account;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;

/**
 * Class AssetAccountIban
 *
 * @package FireflyIII\Helpers\Csv\Converter
 */
class AssetAccountIban extends BasicConverter implements ConverterInterface
{

    /**
     * @return Account
     */
    public function convert(): Account
    {
        /** @var AccountRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Account\AccountRepositoryInterface');

        // is mapped? Then it's easy!
        if (isset($this->mapped[$this->index][$this->value])) {
            $account = $repository->find($this->mapped[$this->index][$this->value]);

            return $account;
        }

        if (strlen($this->value) > 0) {
            // find or create new account:
            $set = $repository->getAccounts(['Default account', 'Asset account']);
            /** @var Account $entry */
            foreach ($set as $entry) {
                if ($entry->iban == $this->value) {

                    return $entry;
                }
            }
            // create it if doesn't exist.
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

            $account = $repository->store($accountData);

            return $account;
        }

        return new Account;
    }
}
