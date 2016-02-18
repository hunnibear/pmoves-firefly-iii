<?php
/**
 * TriggerFactory.php
 * Copyright (C) 2016 Robert Horlings
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Rules\Factory;

use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Models\RuleTrigger;
use FireflyIII\Rules\Triggers\AbstractTrigger;
use FireflyIII\Rules\Triggers\TriggerInterface;
use FireflyIII\Support\Domain;

/**
 * Interface TriggerInterface
 *
 * @package FireflyIII\Rules\Triggers
 */
class TriggerFactory
{
    protected static $triggerTypes = null;

    /**
     * Returns the trigger for the given type and journal
     *
     * @param RuleTrigger $trigger
     *
     * @return AbstractTrigger
     */
    public static function getTrigger(RuleTrigger $trigger)
    {
        $triggerType = $trigger->trigger_type;

        /** @var AbstractTrigger $class */
        $class = self::getTriggerClass($triggerType);
        $obj   = $class::makeFromTriggerValue($trigger->trigger_value);

        return $obj;
    }

    /**
     * @param string $triggerType
     * @param string $triggerValue
     *
     * @param bool   $stopProcessing
     *
     * @return AbstractTrigger
     * @throws FireflyException
     */
    public static function makeTriggerFromStrings(string $triggerType, string $triggerValue, bool $stopProcessing)
    {
        /** @var AbstractTrigger $class */
        $class = self::getTriggerClass($triggerType);
        $obj   = $class::makeFromStrings($triggerValue, $stopProcessing);

        return $obj;
    }

    /**
     * Returns a map with triggertypes, mapped to the class representing that type
     */
    protected static function getTriggerTypes()
    {
        if (!self::$triggerTypes) {
            self::$triggerTypes = Domain::getRuleTriggers();
        }

        return self::$triggerTypes;
    }

    /**
     * Returns the class name to be used for triggers with the given name
     *
     * @param string $triggerType
     *
     * @return TriggerInterface|string
     * @throws FireflyException
     */
    private static function getTriggerClass(string $triggerType): string
    {
        $triggerTypes = self::getTriggerTypes();

        if (!array_key_exists($triggerType, $triggerTypes)) {
            throw new FireflyException('No such trigger exists ("' . e($triggerType) . '").');
        }

        $class = $triggerTypes[$triggerType];
        if (!class_exists($class)) {
            throw new FireflyException('Could not instantiate class for rule trigger type "' . e($triggerType) . '" (' . e($class) . ').');
        }

        return $class;
    }
}
