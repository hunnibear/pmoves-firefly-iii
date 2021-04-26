<?php
/*
 * CreditRecalculateService.php
 * Copyright (c) 2021 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace FireflyIII\Services\Internal\Support;


use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Factory\AccountMetaFactory;
use FireflyIII\Models\Account;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionGroup;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Models\TransactionType;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use Log;

class CreditRecalculateService
{
    private ?Account                   $account;
    private ?TransactionGroup          $group;
    private array                      $work;
    private AccountRepositoryInterface $repository;

    /**
     * CreditRecalculateService constructor.
     */
    public function __construct()
    {
        $this->group   = null;
        $this->account = null;
        $this->work    = [];
    }

    /**
     *
     */
    public function recalculate(): void
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        if (true !== config('firefly.feature_flags.handle_debts')) {
            Log::debug('handle_debts is disabled.');

            return;
        }
        if (null !== $this->group && null === $this->account) {
            $this->processGroup();
        }
        if (null !== $this->account && null === $this->group) {
            // work based on account.
            $this->processAccount();
        }
        if (0 === count($this->work)) {
            Log::debug('No work accounts, do not do CreditRecalculationService');

            return;
        }
        Log::debug('Will now do CreditRecalculationService');
        $this->processWork();
    }

    /**
     *
     */
    private function processWork(): void
    {
        $this->repository = app(AccountRepositoryInterface::class);
        Log::debug(sprintf('Now in %s', __METHOD__));
        foreach ($this->work as $account) {
            $this->processWorkAccount($account);
        }
        Log::debug(sprintf('Done with %s', __METHOD__));
    }

    /**
     * @param Account $account
     */
    private function processWorkAccount(Account $account): void
    {
        Log::debug(sprintf('Now in %s(#%d)', __METHOD__, $account->id));

        // get opening balance (if present)
        $this->repository->setUser($account->user);
        $startOfDebt = $this->repository->getOpeningBalanceAmount($account) ?? '0';
        $leftOfDebt  = app('steam')->positive($startOfDebt);
        /** @var AccountMetaFactory $factory */
        $factory = app(AccountMetaFactory::class);
        $factory->crud($account, 'start_of_debt', $startOfDebt);

        // now loop all transactions (except opening balance and credit thing)
        $transactions = $account->transactions()->get();
        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $leftOfDebt = $this->processTransaction($account, $transaction, $leftOfDebt);
        }
        $factory->crud($account, 'current_debt', $leftOfDebt);


        Log::debug(sprintf('Done with %s(#%d)', __METHOD__, $account->id));
    }


    /**
     *
     */
    private function processGroup(): void
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        /** @var TransactionJournal $journal */
        foreach ($this->group->transactionJournals as $journal) {
            if (0 === count($this->work)) {
                try {
                    $this->findByJournal($journal);
                } catch (FireflyException $e) {
                    Log::error($e->getTraceAsString());
                    Log::error(sprintf('Could not find work account for transaction group #%d.', $this->group->id));
                }
            }
        }
        Log::debug(sprintf('Done with %s', __METHOD__));
    }

    /**
     * @param TransactionJournal $journal
     *
     * @throws FireflyException
     */
    private function findByJournal(TransactionJournal $journal): void
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        $source      = $this->getSourceAccount($journal);
        $destination = $this->getDestinationAccount($journal);

        // destination or source must be liability.
        $valid = config('firefly.valid_liabilities');
        if (in_array($destination->accountType->type, $valid)) {
            Log::debug(sprintf('Dest account type is "%s", include it.', $destination->accountType->type));
            $this->work[] = $destination;
        }
        if (in_array($source->accountType->type, $valid)) {
            Log::debug(sprintf('Src account type is "%s", include it.', $source->accountType->type));
            $this->work[] = $source;
        }
    }

    /**
     * @param TransactionJournal $journal
     *
     * @return Account
     * @throws FireflyException
     */
    private function getSourceAccount(TransactionJournal $journal): Account
    {
        return $this->getAccountByDirection($journal, '<');
    }

    /**
     * @param TransactionJournal $journal
     * @param string             $direction
     *
     * @return Account
     * @throws FireflyException
     */
    private function getAccountByDirection(TransactionJournal $journal, string $direction): Account
    {
        /** @var Transaction $transaction */
        $transaction = $journal->transactions()->where('amount', $direction, '0')->first();
        if (null === $transaction) {
            throw new FireflyException(sprintf('Cannot find "%s"-transaction of journal #%d', $direction, $journal->id));
        }
        $account = $transaction->account;
        if (null === $account) {
            throw new FireflyException(sprintf('Cannot find "%s"-account of transaction #%d of journal #%d', $direction, $transaction->id, $journal->id));
        }

        return $account;
    }

    /**
     * @param TransactionJournal $journal
     *
     * @return Account
     * @throws FireflyException
     */
    private function getDestinationAccount(TransactionJournal $journal): Account
    {
        return $this->getAccountByDirection($journal, '>');
    }

    /**
     *
     */
    private function processAccount(): void
    {
        Log::debug(sprintf('Now in %s', __METHOD__));
        $valid = config('firefly.valid_liabilities');
        if (in_array($this->account->accountType->type, $valid)) {
            Log::debug(sprintf('Account type is "%s", include it.', $this->account->accountType->type));
            $this->work[] = $this->account;
        }
    }

    /**
     * @param Account|null $account
     */
    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @param TransactionGroup $group
     */
    public function setGroup(TransactionGroup $group): void
    {
        $this->group = $group;
    }

    /**
     * @param Transaction $transaction
     * @param string      $amount
     *
     * @return string
     */
    private function processTransaction(Account $account, Transaction $transaction, string $amount): string
    {
        Log::debug(sprintf('Now in %s(#%d, %s)', __METHOD__, $transaction->id, $amount));
        $journal = $transaction->transactionJournal;
        $type    = $journal->transactionType->type;

        Log::debug(sprintf('Type is "%s"', $type));
        if (in_array($type, [TransactionType::WITHDRAWAL]) && (int)$account->id === (int)$transaction->account_id && 1 === bccomp($transaction->amount, '0')) {
            Log::debug(sprintf('Transaction #%d is withdrawal into liability #%d, does not influence the amount left.', $account->id, $transaction->account_id));

            return $amount;
        }
        if (in_array($type, [TransactionType::DEPOSIT]) && (int)$account->id === (int)$transaction->account_id && -1 === bccomp($transaction->amount, '0')) {
            Log::debug(sprintf('Transaction #%d is deposit from liability #%d,does not influence the amount left.', $account->id, $transaction->account_id));

            return $amount;
        }

        if (in_array($type, [TransactionType::WITHDRAWAL, TransactionType::DEPOSIT, TransactionType::TRANSFER], true)) {
            $amount = bcadd($amount, bcmul($transaction->amount, '-1'));
        }
        Log::debug(sprintf('Amount is now %s', $amount));

        return $amount;
    }


}