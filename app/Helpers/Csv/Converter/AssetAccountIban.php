<?php
declare(strict_types = 1);
namespace FireflyIII\Helpers\Csv\Converter;

use Auth;
use Carbon\Carbon;
use FireflyIII\Crud\Account\AccountCrudInterface;
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
        $repository = app(AccountRepositoryInterface::class);

        // is mapped? Then it's easy!
        if (isset($this->mapped[$this->index][$this->value])) {
            $account = $repository->find(intval($this->mapped[$this->index][$this->value]));

            return $account;
        }


        if (strlen($this->value) > 0) {
            $account = $this->searchOrCreate($repository);

            return $account;
        }

        return new Account;
    }

    /**
     * @param AccountRepositoryInterface $repository
     * @param AccountCrudInterface       $crud
     *
     * @return Account
     */
    private function searchOrCreate(AccountRepositoryInterface $repository, AccountCrudInterface $crud)
    {
        // find or create new account:
        $set = $repository->getAccountsByType(['Default account', 'Asset account']);
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
            'iban'                   => $this->value,
            'accountNumber'          => $this->value,
            'accountRole'            => null,
            'openingBalance'         => 0,
            'openingBalanceDate'     => new Carbon,
            'openingBalanceCurrency' => 1, // hard coded.
        ];

        $account = $crud->store($accountData);

        return $account;
    }
}
