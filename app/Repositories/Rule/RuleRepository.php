<?php
/**
 * RuleRepository.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Repositories\Rule;

use Auth;
use FireflyIII\Models\Rule;
use FireflyIII\Models\RuleAction;
use FireflyIII\Models\RuleGroup;
use FireflyIII\Models\RuleTrigger;

/**
 * Class RuleRepository
 *
 * @package FireflyIII\Repositories\Rule
 */
class RuleRepository implements RuleRepositoryInterface
{

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function destroy(Rule $rule)
    {
        foreach ($rule->ruleTriggers as $trigger) {
            $trigger->delete();
        }
        foreach ($rule->ruleActions as $action) {
            $action->delete();
        }
        $rule->delete();

        return true;
    }

    /**
     * @param RuleGroup $ruleGroup
     *
     * @return int
     */
    public function getHighestOrderInRuleGroup(RuleGroup $ruleGroup)
    {
        return intval($ruleGroup->rules()->max('order'));
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function moveDown(Rule $rule)
    {
        $order = $rule->order;

        // find the rule with order+1 and give it order-1
        $other = $rule->ruleGroup->rules()->where('order', ($order + 1))->first();
        if ($other) {
            $other->order = $other->order - 1;
            $other->save();
        }


        $rule->order = ($rule->order + 1);
        $rule->save();
        $this->resetRulesInGroupOrder($rule->ruleGroup);
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function moveUp(Rule $rule)
    {
        $order = $rule->order;

        // find the rule with order-1 and give it order+1
        $other = $rule->ruleGroup->rules()->where('order', ($order - 1))->first();
        if ($other) {
            $other->order = ($other->order + 1);
            $other->save();
        }

        $rule->order = ($rule->order - 1);
        $rule->save();
        $this->resetRulesInGroupOrder($rule->ruleGroup);
    }

    /**
     * @param Rule  $rule
     * @param array $ids
     *
     * @return bool
     */
    public function reorderRuleActions(Rule $rule, array $ids)
    {
        $order = 1;
        foreach ($ids as $actionId) {
            /** @var RuleTrigger $trigger */
            $action = $rule->ruleActions()->find($actionId);
            if (!is_null($action)) {
                $action->order = $order;
                $action->save();
                $order++;
            }
        }

        return true;
    }

    /**
     * @param Rule  $rule
     * @param array $ids
     *
     * @return bool
     */
    public function reorderRuleTriggers(Rule $rule, array $ids)
    {
        $order = 1;
        foreach ($ids as $triggerId) {
            /** @var RuleTrigger $trigger */
            $trigger = $rule->ruleTriggers()->find($triggerId);
            if (!is_null($trigger)) {
                $trigger->order = $order;
                $trigger->save();
                $order++;
            }
        }

        return true;
    }

    /**
     * @param RuleGroup $ruleGroup
     *
     * @return bool
     */
    public function resetRulesInGroupOrder(RuleGroup $ruleGroup)
    {
        $ruleGroup->rules()->whereNotNull('deleted_at')->update(['order' => 0]);

        $set   = $ruleGroup->rules()
                           ->orderBy('order', 'ASC')
                           ->orderBy('updated_at', 'DESC')
                           ->get();
        $count = 1;
        /** @var Rule $entry */
        foreach ($set as $entry) {
            $entry->order = $count;
            $entry->save();
            $count++;
        }

        return true;

    }

    /**
     * @param array $data
     *
     * @return Rule
     */
    public function store(array $data)
    {
        /** @var RuleGroup $ruleGroup */
        $ruleGroup = Auth::user()->ruleGroups()->find($data['rule_group_id']);

        // get max order:
        $order = $this->getHighestOrderInRuleGroup($ruleGroup);

        // start by creating a new rule:
        $rule = new Rule;
        $rule->user()->associate(Auth::user());

        $rule->rule_group_id   = $data['rule_group_id'];
        $rule->order           = ($order + 1);
        $rule->active          = 1;
        $rule->stop_processing = intval($data['stop_processing']) == 1;
        $rule->title           = $data['title'];
        $rule->description     = strlen($data['description']) > 0 ? $data['description'] : null;

        $rule->save();

        // start storing triggers:
        $order          = 1;
        $stopProcessing = false;
        $this->storeTrigger($rule, 'user_action', $data['trigger'], $stopProcessing, $order);
        foreach ($data['rule-triggers'] as $index => $trigger) {
            $value          = $data['rule-trigger-values'][$index];
            $stopProcessing = isset($data['rule-trigger-stop'][$index]) ? true : false;
            $this->storeTrigger($rule, $trigger, $value, $stopProcessing, $order);
            $order++;
        }

        // same for actions.
        $order = 1;
        foreach ($data['rule-actions'] as $index => $action) {
            $value          = $data['rule-action-values'][$index];
            $stopProcessing = isset($data['rule-action-stop'][$index]) ? true : false;
            $this->storeAction($rule, $action, $value, $stopProcessing, $order);
        }

        return $rule;
    }

    /**
     * @param Rule   $rule
     * @param string $action
     * @param string $value
     * @param bool   $stopProcessing
     * @param int    $order
     *
     * @return RuleAction
     */
    public function storeAction(Rule $rule, $action, $value, $stopProcessing, $order)
    {
        $ruleAction = new RuleAction;
        $ruleAction->rule()->associate($rule);
        $ruleAction->order           = $order;
        $ruleAction->active          = 1;
        $ruleAction->stop_processing = $stopProcessing;
        $ruleAction->action_type     = $action;
        $ruleAction->action_value    = $value;
        $ruleAction->save();


        return $ruleAction;
    }

    /**
     * @param Rule   $rule
     * @param string $action
     * @param string $value
     * @param bool   $stopProcessing
     * @param int    $order
     *
     * @return RuleTrigger
     */
    public function storeTrigger(Rule $rule, $action, $value, $stopProcessing, $order)
    {
        $ruleTrigger = new RuleTrigger;
        $ruleTrigger->rule()->associate($rule);
        $ruleTrigger->order           = $order;
        $ruleTrigger->active          = 1;
        $ruleTrigger->stop_processing = $stopProcessing;
        $ruleTrigger->trigger_type    = $action;
        $ruleTrigger->trigger_value   = $value;
        $ruleTrigger->save();

        return $ruleTrigger;
    }

    /**
     * @param Rule  $rule
     * @param array $data
     *
     * @return Rule
     */
    public function update(Rule $rule, array $data)
    {
        // update rule:
        $rule->active          = $data['active'];
        $rule->stop_processing = $data['stop_processing'];
        $rule->title           = $data['title'];
        $rule->description     = $data['description'];
        $rule->save();

        // delete triggers:
        $rule->ruleTriggers()->delete();

        // delete actions:
        $rule->ruleActions()->delete();

        // recreate triggers:
        $order          = 1;
        $stopProcessing = false;
        $this->storeTrigger($rule, 'user_action', $data['trigger'], $stopProcessing, $order);
        foreach ($data['rule-triggers'] as $index => $trigger) {
            $value          = $data['rule-trigger-values'][$index];
            $stopProcessing = isset($data['rule-trigger-stop'][$index]) ? true : false;
            $this->storeTrigger($rule, $trigger, $value, $stopProcessing, $order);
            $order++;
        }

        // recreate actions:
        $order = 1;
        foreach ($data['rule-actions'] as $index => $action) {
            $value          = $data['rule-action-values'][$index];
            $stopProcessing = isset($data['rule-action-stop'][$index]) ? true : false;
            $this->storeAction($rule, $action, $value, $stopProcessing, $order);
        }


        return $rule;
    }
}