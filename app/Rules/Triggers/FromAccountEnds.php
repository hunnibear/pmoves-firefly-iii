<?php
/**
 * FromAccountEnds.php
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
 * Class FromAccountEnds
 *
 * @package FireflyIII\Rules\Triggers
 */
class FromAccountEnds implements TriggerInterface
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
        $fromAccountName       = strtolower($this->journal->source_account->name);
        $fromAccountNameLength = strlen($fromAccountName);
        $search                = strtolower($this->trigger->trigger_value);
        $searchLength          = strlen($search);

        // if the string to search for is longer than the account name,
        // shorten the search string.
        if ($searchLength > $fromAccountNameLength) {
            Log::debug('Search string "' . $search . '" (' . $searchLength . ') is longer than "' . $fromAccountName . '" (' . $fromAccountNameLength . '). ');
            $search       = substr($search, ($fromAccountNameLength * -1));
            $searchLength = strlen($search);
            Log::debug('Search string is now "' . $search . '" (' . $searchLength . ') instead.');
        }


        $part = substr($fromAccountName, $searchLength * -1);

        if ($part == $search) {
            Log::debug('"' . $fromAccountName . '" ends with "' . $search . '". Return true.');

            return true;
        }
        Log::debug('"' . $fromAccountName . '" does not end with "' . $search . '". Return false.');

        return false;

    }
}