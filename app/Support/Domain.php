<?php
declare(strict_types = 1);
/**
 * Domain.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Support;

use Config;

/**
 * Class Domain
 *
 * @package FireflyIII\Support
 */
class Domain
{
    /**
     * @return array
     */
    public static function getBindables(): array
    {
        return Config::get('firefly.bindables');

    }

    /**
     * @return array
     */
    public static function getRuleActions(): array
    {
        return Config::get('firefly.rule-actions');
    }

    /**
     * @return array
     */
    public static function getRuleTriggers(): array
    {
        return Config::get('firefly.rule-triggers');
    }
}
