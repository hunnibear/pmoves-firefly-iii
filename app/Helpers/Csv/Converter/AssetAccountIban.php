<?php
declare(strict_types = 1);
namespace FireflyIII\Helpers\Csv\Converter;

use Auth;
use FireflyIII\Models\Account;
use FireflyIII\Models\AccountType;

/**
 * Class AssetAccountIban
 *
 * @package FireflyIII\Helpers\Csv\Converter
 */
class AssetAccountIban extends BasicConverter implements ConverterInterface
{

    /**
     * @return Account|null
     */
    public function convert()
    {
        // is mapped? Then it's easy!
        if (isset($this->mapped[$this->index][$this->value])) {
            $account = Auth::user()->accounts()->find($this->mapped[$this->index][$this->value]);

            return $account;
        }
        if (strlen($this->value) > 0) {
            // find or create new account:
            $account     = $this->findAccount();
            $accountType = AccountType::where('type', 'Asset account')->first();

            if (is_null($account)) {
                // create it if doesn't exist.
                $account = Account::firstOrCreateEncrypted(
                    [
                        'name'            => $this->value,
                        'iban'            => $this->value,
                        'user_id'         => Auth::user()->id,
                        'account_type_id' => $accountType->id,
                        'active'          => 1,
                    ]
                );
            }

            return $account;
        }

        return null;
    }

    /**
     * @return Account|null
     */
    protected function findAccount()
    {
        $set = Auth::user()->accounts()->accountTypeIn(['Default account', 'Asset account'])->get(['accounts.*']);
        /** @var Account $entry */
        foreach ($set as $entry) {
            if ($entry->iban == $this->value) {

                return $entry;
            }
        }

        return null;
    }
}
