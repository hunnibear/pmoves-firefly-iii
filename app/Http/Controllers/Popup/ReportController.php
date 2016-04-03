<?php
declare(strict_types = 1);
/**
 * ReportController.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Http\Controllers\Popup;


use Carbon\Carbon;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Helpers\Collection\BalanceLine;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Repositories\Category\SingleCategoryRepositoryInterface;
use FireflyIII\Repositories\Tag\TagRepositoryInterface;
use FireflyIII\Support\Binder\AccountList;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Response;

/**
 * Class ReportController
 *
 * @package FireflyIII\Http\Controllers\Popup
 */
class ReportController extends Controller
{

    /**
     * @param Request $request
     *
     * @throws FireflyException
     */
    public function info(Request $request)
    {
        $attributes = $request->get('attributes');
        $attributes = $this->parseAttributes($attributes);
        switch ($attributes['location']) {
            default:
                throw new FireflyException('Firefly cannot handle "' . e($attributes['location']) . '" ');
            case 'budget-spent-amount':
                $html = $this->budgetSpentAmount($attributes);

                break;
            case 'expense-entry':
                $html = $this->expenseEntry($attributes);
                break;
            case 'income-entry':
                $html = $this->incomeEntry($attributes);
                break;
            case 'category-entry':
                $html = $this->categoryEntry($attributes);
                break;
            case 'balance-amount':
                $html = $this->balanceAmount($attributes);
                break;
        }

        return Response::json(['html' => $html]);


    }

    /**
     * @param $attributes
     *
     * @return string
     * @throws FireflyException
     */
    private function balanceAmount(array $attributes): string
    {
        $role = intval($attributes['role']);

        /** @var BudgetRepositoryInterface $budgetRepository */
        $budgetRepository = app('FireflyIII\Repositories\Budget\BudgetRepositoryInterface');
        $budget           = $budgetRepository->find(intval($attributes['budgetId']));

        /** @var AccountRepositoryInterface $accountRepository */
        $accountRepository = app('FireflyIII\Repositories\Account\AccountRepositoryInterface');
        $account           = $accountRepository->find(intval($attributes['accountId']));

        /** @var TagRepositoryInterface $tagRepository */
        $tagRepository = app('FireflyIII\Repositories\Tag\TagRepositoryInterface');

        switch (true) {
            case ($role === BalanceLine::ROLE_DEFAULTROLE && !is_null($budget->id)):
                $journals = $budgetRepository->expensesSplit($budget, $account, $attributes['startDate'], $attributes['endDate']);
                break;
            case ($role === BalanceLine::ROLE_DEFAULTROLE && is_null($budget->id)):
                $journals = $budgetRepository->getAllWithoutBudget($account, $attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
                break;
            case ($role === BalanceLine::ROLE_DIFFROLE):
                // journals no budget, not corrected by a tag.
                $journals = $budgetRepository->getAllWithoutBudget($account, $attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
                $journals = $journals->filter(
                    function (TransactionJournal $journal) {
                        $tags = $journal->tags()->where('tagMode', 'balancingAct')->count();
                        if ($tags === 0) {
                            return $journal;
                        }
                    }
                );
                break;
            case ($role === BalanceLine::ROLE_TAGROLE):
                throw new FireflyException('Firefly cannot handle this type of info-button (BalanceLine::TagRole)');
        }
        $view = view('popup.report.balance-amount', compact('journals'))->render();

        return $view;
    }

    /**
     * Returns all expenses inside the given budget for the given accounts.
     *
     * @param array $attributes
     *
     * @return string
     * @throws FireflyException
     */
    private function budgetSpentAmount(array $attributes): string
    {
        // need to find the budget
        // then search for expenses in the given period
        // list them in some table format.
        /** @var BudgetRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Budget\BudgetRepositoryInterface');
        $budget     = $repository->find(intval($attributes['budgetId']));
        if (is_null($budget->id)) {
            $journals = $repository->getWithoutBudgetForAccounts($attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
        } else {
            // get all expenses in budget in period:
            $journals = $repository->getExpenses($budget, $attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
        }

        $view = view('popup.report.budget-spent-amount', compact('journals'))->render();

        return $view;
    }

    /**
     * Returns all expenses in category in range.
     *
     * @param $attributes
     *
     * @return string
     * @throws FireflyException
     */
    private function categoryEntry(array $attributes): string
    {
        /** @var SingleCategoryRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Category\SingleCategoryRepositoryInterface');
        $category   = $repository->find(intval($attributes['categoryId']));
        $journals   = $repository->getJournalsForAccountsInRange($category, $attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
        $view       = view('popup.report.category-entry', compact('journals'))->render();

        return $view;
    }

    /**
     * Returns all the expenses that went to the given expense account.
     *
     * @param $attributes
     *
     * @return string
     * @throws FireflyException
     */
    private function expenseEntry(array $attributes): string
    {
        /** @var AccountRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Account\AccountRepositoryInterface');
        $account    = $repository->find(intval($attributes['accountId']));
        $journals   = $repository->getExpensesByDestination($account, $attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
        $view       = view('popup.report.expense-entry', compact('journals'))->render();

        return $view;
    }

    /**
     * Returns all the incomes that went to the given asset account.
     *
     * @param $attributes
     *
     * @return string
     * @throws FireflyException
     */
    private function incomeEntry(array $attributes): string
    {
        /** @var AccountRepositoryInterface $repository */
        $repository = app('FireflyIII\Repositories\Account\AccountRepositoryInterface');
        $account    = $repository->find(intval($attributes['accountId']));
        $journals   = $repository->getIncomeByDestination($account, $attributes['accounts'], $attributes['startDate'], $attributes['endDate']);
        $view       = view('popup.report.income-entry', compact('journals'))->render();

        return $view;
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws FireflyException
     */
    private function parseAttributes(array $attributes): array
    {
        $attributes['location'] = $attributes['location'] ?? '';
        $attributes['accounts'] = AccountList::routeBinder($attributes['accounts'] ?? '', '');
        try {
            $attributes['startDate'] = Carbon::createFromFormat('Ymd', $attributes['startDate']);
        } catch (InvalidArgumentException $e) {
            throw new FireflyException('Could not parse start date "' . e($attributes['startDate']) . '".');
        }

        try {
            $attributes['endDate'] = Carbon::createFromFormat('Ymd', $attributes['endDate']);
        } catch (InvalidArgumentException $e) {
            throw new FireflyException('Could not parse start date "' . e($attributes['endDate']) . '".');
        }


        return $attributes;
    }

}