<?php namespace FireflyIII\Handlers\Events;

use Auth;
use FireflyIII\Events\JournalCreated;
use FireflyIII\Models\PiggyBank;
use FireflyIII\Models\PiggyBankEvent;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionJournal;

/**
 * Class ConnectJournalToPiggyBank
 *
 * @package FireflyIII\Handlers\Events
 */
class ConnectJournalToPiggyBank
{

    /**
     * Create the event handler.
     * @codeCoverageIgnore
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event when journal is saved.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param  JournalCreated $event
     *
     * @return boolean
     */
    public function handle(JournalCreated $event)
    {
        /** @var TransactionJournal $journal */
        $journal     = $event->journal;
        $piggyBankId = $event->piggyBankId;

        /** @var PiggyBank $piggyBank */
        $piggyBank = Auth::user()->piggybanks()->where('piggy_banks.id', $piggyBankId)->first(['piggy_banks.*']);

        if (is_null($piggyBank)) {
            return false;
        }
        // update piggy bank rep for date of transaction journal.
        $repetition = $piggyBank->piggyBankRepetitions()->relevantOnDate($journal->date)->first();
        if (is_null($repetition)) {
            return false;
        }

        $amount = $journal->amount;
        /** @var Transaction $transaction */
        foreach ($journal->transactions()->get() as $transaction) {
            if ($transaction->account_id == $piggyBank->account_id) {
                if ($transaction->amount < 0) {
                    $amount = $transaction->amount * -1;
                }
            }
        }

        $repetition->currentamount += $amount;
        $repetition->save();

        PiggyBankEvent::create(['piggy_bank_id' => $piggyBank->id, 'transaction_journal_id' => $journal->id, 'date' => $journal->date, 'amount' => $amount]);

        return true;

    }


}
