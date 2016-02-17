<?php
declare(strict_types = 1);
/**
 * SetBudget.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Rules\Actions;


use FireflyIII\Models\Budget;
use FireflyIII\Models\RuleAction;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use Log;

/**
 * Class SetBudget
 *
 * @package FireflyIII\Rules\Action
 */
class SetBudget implements ActionInterface
{

    private $action;


    /**
     * TriggerInterface constructor.
     *
     * @param RuleAction         $action
     */
    public function __construct(RuleAction $action)
    {
        $this->action  = $action;
    }

    /**
     * @param TransactionJournal $journal
     *
     * @return bool
     */
    public function act(TransactionJournal $journal)
    {
        /** @var BudgetRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Budget\BudgetRepositoryInterface');
        $search     = $this->action->action_value;
        $budgets    = $repository->getActiveBudgets();
        $budget     = $budgets->filter(
            function (Budget $current) use ($search) {
                return $current->name == $search;
            }
        )->first();
        if (!is_null($budget)) {
            Log::debug('Will set budget "' . $search . '" (#' . $budget->id . ') on journal #' . $journal->id . '.');
            $journal->budgets()->sync([$budget->id]);
        } else {
            Log::debug('Could not find budget "' . $search . '". Failed.');
        }

        return true;
    }
}
