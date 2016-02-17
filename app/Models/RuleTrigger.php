<?php
declare(strict_types = 1);
/**
 * RuleTrigger.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FireflyIII\Models\RuleTrigger
 *
 * @property integer                      $id
 * @property \Carbon\Carbon               $created_at
 * @property \Carbon\Carbon               $updated_at
 * @property integer                      $rule_id
 * @property integer                      $order
 * @property string                       $title
 * @property string                       $trigger_type
 * @property string                       $trigger_value
 * @property boolean                      $active
 * @property boolean                      $stop_processing
 * @property-read \FireflyIII\Models\Rule $rule
 */
class RuleTrigger extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rule()
    {
        return $this->belongsTo('FireflyIII\Models\Rule');
    }
    
    /**
     * Checks whether this trigger will match all transactions
     * For example: amount > 0 or description starts with '' 
     */
    public function matchesAnything() {
        return TriggerFactory::getTrigger($this, new TransactionJournal)->matchesAnything();
    }
}
