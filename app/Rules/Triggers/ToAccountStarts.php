<?php
/**
 * ToAccountStarts.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Rules\Triggers;

use FireflyIII\Models\RuleTrigger;
use FireflyIII\Models\TransactionJournal;
use Log;

/**
 * Class ToAccountStarts
 *
 * @package FireflyIII\Rules\Triggers
 */
class ToAccountStarts implements TriggerInterface
{
    /** @var RuleTrigger */
    protected $trigger;

    /** @var TransactionJournal */
    protected $journal;


    /**
     * TriggerInterface constructor.
     *
     * @param RuleTrigger        $trigger
     * @param TransactionJournal $journal
     */
    public function __construct(RuleTrigger $trigger, TransactionJournal $journal)
    {
        $this->trigger = $trigger;
        $this->journal = $journal;
    }

    /**
     * @return bool
     */
    public function triggered()
    {
        $toAccountName = strtolower($this->journal->destination_account->name);
        $search        = strtolower($this->trigger->trigger_value);

        $part = substr($toAccountName, 0, strlen($search));

        if ($part == $search) {
            Log::debug('"' . $toAccountName . '" starts with "' . $search . '". Return true.');

            return true;
        }
        Log::debug('"' . $toAccountName . '" does not start with "' . $search . '". Return false.');

        return false;

    }
}