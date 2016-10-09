<?php
/**
 * UpdateJournalConnection.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms of the
 * Creative Commons Attribution-ShareAlike 4.0 International License.
 *
 * See the LICENSE file for details.
 */

declare(strict_types = 1);
namespace FireflyIII\Handlers\Events;

use FireflyIII\Events\TransactionJournalUpdated;
use FireflyIII\Models\PiggyBankEvent;
use FireflyIII\Models\PiggyBankRepetition;
use FireflyIII\Models\TransactionJournal;

/**
 * Class UpdateJournalConnection
 *
 * @package FireflyIII\Handlers\Events
 */
class UpdateJournalConnection
{

    /**
     * Handle the event.
     *
     * @param  TransactionJournalUpdated $event
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) // it's exactly 5.
     *
     * @return bool
     */
    public function handle(TransactionJournalUpdated $event):bool
    {
        $journal = $event->journal;

        if (!$journal->isTransfer()) {
            return true;
        }

        // get the event connected to this journal:
        /** @var PiggyBankEvent $event */
        $event = PiggyBankEvent::where('transaction_journal_id', $journal->id)->first();
        if (is_null($event)) {
            return false;
        }
        $piggyBank  = $event->piggyBank()->first();
        $repetition = null;
        if (!is_null($piggyBank)) {
            /** @var PiggyBankRepetition $repetition */
            $repetition = $piggyBank->piggyBankRepetitions()->relevantOnDate($journal->date)->first();
        }

        if (is_null($repetition)) {
            return false;
        }

        $amount = TransactionJournal::amount($journal);
        $diff   = bcsub($amount, $event->amount); // update current repetition

        $repetition->currentamount = bcadd($repetition->currentamount, $diff);
        $repetition->save();


        $event->amount = $amount;
        $event->save();

        return true;
    }

}
