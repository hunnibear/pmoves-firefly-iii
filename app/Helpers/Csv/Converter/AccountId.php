<?php
declare(strict_types = 1);
namespace FireflyIII\Helpers\Csv\Converter;

use FireflyIII\Models\Account;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;

/**
 * Class AccountId
 *
 * @package FireflyIII\Helpers\Csv\Converter
 */
class AccountId extends BasicConverter implements ConverterInterface
{

    /**
     * @return Account
     */
    public function convert(): Account
    {
        /** @var AccountRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Account\AccountRepositoryInterface');
        $account    = isset($this->mapped[$this->index][$this->value])
            ? $repository->find($this->mapped[$this->index][$this->value])
            : $repository->find($this->value);

        return $account;
    }
}
