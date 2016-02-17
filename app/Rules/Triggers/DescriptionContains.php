<?php
declare(strict_types = 1);
/**
 * DescriptionContains.php
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
 * Class DescriptionContains
 *
 * @package FireflyIII\Rules\Triggers
 */
class DescriptionContains implements TriggerInterface
{


    /** @var RuleTrigger */
    protected $trigger;

    /**
     * TriggerInterface constructor.
     *
     * @param RuleTrigger $trigger
     */
    public function __construct(RuleTrigger $trigger)
    {
        $this->trigger = $trigger;

    }

    /**
     * A trigger is said to "match anything", or match any given transaction,
     * when the trigger value is very vague or has no restrictions. Easy examples
     * are the "AmountMore"-trigger combined with an amount of 0: any given transaction
     * has an amount of more than zero! Other examples are all the "Description"-triggers
     * which have hard time handling empty trigger values such as "" or "*" (wild cards).
     *
     * If the user tries to create such a trigger, this method MUST return true so Firefly III
     * can stop the storing / updating the trigger. If the trigger is in any way restrictive
     * (even if it will still include 99.9% of the users transactions), this method MUST return
     * false.
     *
     * @param null $value
     *
     * @return bool
     */
    public static function willMatchEverything($value = null)
    {
        if (!is_null($value)) {
            return strval($value) === "";
        }

        return true;
    }

    /**
     * @param TransactionJournal $journal
     *
     * @return bool
     */
    public function triggered(TransactionJournal $journal)
    {
        $search = strtolower($this->trigger->trigger_value);
        $source = strtolower($journal->description);

        $strpos = strpos($source, $search);
        if (!($strpos === false)) {
            // found something
            Log::debug('"' . $source . '" contains the text "' . $search . '". Return true.');

            return true;
        }

        // found nothing.
        Log::debug('"' . $source . '" does not contain the text "' . $search . '". Return false.');

        return false;

    }
}
