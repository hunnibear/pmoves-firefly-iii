<?php
/**
 * AccountRepositoryInterface.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms of the
 * Creative Commons Attribution-ShareAlike 4.0 International License.
 *
 * See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Repositories\Account;

use Carbon\Carbon;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Models\Account;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionJournal;
use Illuminate\Support\Collection;

/**
 * Interface AccountRepositoryInterface
 *
 * @package FireflyIII\Repositories\Account
 */
interface AccountRepositoryInterface
{

    /**
     * Moved here from account CRUD.
     *
     * @param array $types
     *
     * @return int
     */
    public function count(array $types): int;

    /**
     * Moved here from account CRUD.
     *
     * @param Account $account
     * @param Account $moveTo
     *
     * @return bool
     */
    public function destroy(Account $account, Account $moveTo): bool;

    /**
     * Returns the transaction from a journal that is related to a given account. Since a journal generally only contains
     * two transactions, this will return one of the two. This method fails horribly when the journal has more than two transactions,
     * but luckily it isn't used for such folly.
     *
     * @param TransactionJournal $journal
     * @param Account            $account
     *
     * @return Transaction
     * @throws FireflyException
     */
    public function getFirstTransaction(TransactionJournal $journal, Account $account): Transaction;

    /**
     * Get the accounts of a user that have piggy banks connected to them.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function getPiggyBankAccounts(Carbon $start, Carbon $end): Collection;

    /**
     * Get savings accounts.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function getSavingsAccounts(Carbon $start, Carbon $end): Collection;

    /**
     * @param Collection $accounts
     * @param array      $types
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return Collection
     */
    public function journalsInPeriod(Collection $accounts, array $types, Carbon $start, Carbon $end): Collection;

    /**
     *
     * @param Account $account
     * @param Carbon  $date
     *
     * @return string
     */
    public function leftOnAccount(Account $account, Carbon $date): string;

    /**
     * Returns the date of the very last transaction in this account.
     *
     * @param Account $account
     *
     * @return Carbon
     */
    public function newestJournalDate(Account $account): Carbon;

    /**
     * Returns the date of the very first transaction in this account.
     *
     * @param Account $account
     *
     * @return Carbon
     */
    public function oldestJournalDate(Account $account): Carbon;

    /**
     *
     * @param Account $account
     *
     * @return TransactionJournal
     */
    public function openingBalanceTransaction(Account $account) : TransactionJournal;

}
