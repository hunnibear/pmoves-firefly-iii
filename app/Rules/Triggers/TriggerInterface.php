<?php
declare(strict_types = 1);
/**
 * TriggerInterface.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Rules\Triggers;

use FireflyIII\Models\RuleTrigger;
use FireflyIII\Models\TransactionJournal;

/**
 * Interface TriggerInterface
 *
 * @package FireflyIII\Rules\Triggers
 */
interface TriggerInterface
{
    /**
     * TriggerInterface constructor.
     *
     * @param RuleTrigger        $trigger
     * @param TransactionJournal $journal
     */
    public function __construct(RuleTrigger $trigger, TransactionJournal $journal);

    /**
     * Checks whether this trigger will match all transactions
     * For example: amount > 0 or description starts with ''
     *
     * @return bool
     */
    public function matchesAnything();

    /**
     * @return bool
     */
    public function triggered();
}
