<?php
/**
 * RuleRepositoryInterface.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Repositories\Rule;

use FireflyIII\Models\Rule;
use FireflyIII\Models\RuleGroup;

/**
 * Interface RuleRepositoryInterface
 *
 * @package FireflyIII\Repositories\Rule
 */
interface RuleRepositoryInterface
{
    /**
     * @param array $data
     *
     * @return RuleGroup
     */
    public function storeRuleGroup(array $data);

    /**
     * @return int
     */
    public function getHighestOrderRuleGroup();


    /**
     * @param RuleGroup $ruleGroup
     * @param array     $data
     *
     * @return RuleGroup
     */
    public function updateRuleGroup(RuleGroup $ruleGroup, array $data);


    /**
     * @param RuleGroup $ruleGroup
     *
     * @return boolean
     */
    public function destroyRuleGroup(RuleGroup $ruleGroup);

    /**
     * @return bool
     */
    public function resetRuleGroupOrder();

    /**
     * @return bool
     */
    public function resetRulesInGroupOrder(RuleGroup $ruleGroup);

    /**
     * @param Rule $rule
     * @return bool
     */
    public function moveRuleUp(Rule $rule);

    /**
     * @param Rule $rule
     * @return bool
     */
    public function moveRuleDown(Rule $rule);

}