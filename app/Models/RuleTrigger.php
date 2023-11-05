<?php

/**
 * RuleTrigger.php
 * Copyright (c) 2019 james@firefly-iii.org
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
declare(strict_types=1);

namespace FireflyIII\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
/**
 * FireflyIII\Models\RuleTrigger
 *
 * @property int         $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int  $rule_id
 * @property string|null $trigger_type
 * @property string|null $trigger_value
 * @property int|string  $order
 * @property bool        $active
 * @property bool        $stop_processing
 * @property-read Rule   $rule
 * @method static Builder|RuleTrigger newModelQuery()
 * @method static Builder|RuleTrigger newQuery()
 * @method static Builder|RuleTrigger query()
 * @method static Builder|RuleTrigger whereActive($value)
 * @method static Builder|RuleTrigger whereCreatedAt($value)
 * @method static Builder|RuleTrigger whereId($value)
 * @method static Builder|RuleTrigger whereOrder($value)
 * @method static Builder|RuleTrigger whereRuleId($value)
 * @method static Builder|RuleTrigger whereStopProcessing($value)
 * @method static Builder|RuleTrigger whereTriggerType($value)
 * @method static Builder|RuleTrigger whereTriggerValue($value)
 * @method static Builder|RuleTrigger whereUpdatedAt($value)
 * @mixin Eloquent
 */
class RuleTrigger extends Model
{
    use ReturnsIntegerIdTrait;

    protected $casts
        = [
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'active'          => 'boolean',
            'order'           => 'int',
            'stop_processing' => 'boolean',
        ];


    protected $fillable = ['rule_id', 'trigger_type', 'trigger_value', 'order', 'active', 'stop_processing'];

    /**
     * @return BelongsTo
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    /**
     * @return Attribute
     */
    protected function ruleId(): Attribute
    {
        return Attribute::make(
            get: static fn($value) => (int)$value,
        );
    }
}
