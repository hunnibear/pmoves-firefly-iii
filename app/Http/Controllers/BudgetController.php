<?php namespace FireflyIII\Http\Controllers;

use Amount;
use Auth;
use Carbon\Carbon;
use FireflyIII\Http\Requests\BudgetFormRequest;
use FireflyIII\Models\Budget;
use FireflyIII\Models\LimitRepetition;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use Input;
use Preferences;
use Redirect;
use Response;
use Session;
use URL;
use View;

/**
 * Class BudgetController
 *
 * @package FireflyIII\Http\Controllers
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class BudgetController extends Controller
{

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        View::share('title', trans('firefly.budgets'));
        View::share('mainTitleIcon', 'fa-tasks');
        View::share('hideBudgets', true);
    }

    /**
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function amount(BudgetRepositoryInterface $repository, Budget $budget)
    {
        $amount          = intval(Input::get('amount'));
        $date            = Session::get('start', Carbon::now()->startOfMonth());
        $limitRepetition = $repository->updateLimitAmount($budget, $date, $amount);
        if ($amount == 0) {
            $limitRepetition = null;
        }

        return Response::json(['name' => $budget->name, 'repetition' => $limitRepetition ? $limitRepetition->id : 0]);

    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // put previous url in session if not redirect from store (not "create another").
        if (Session::get('budgets.create.fromStore') !== true) {
            Session::put('budgets.create.url', URL::previous());
        }
        Session::forget('budgets.create.fromStore');
        Session::flash('gaEventCategory', 'budgets');
        Session::flash('gaEventAction', 'create');
        $subTitle = trans('firefly.create_new_budget');

        return view('budgets.create', compact('subTitle'));
    }

    /**
     * @param Budget $budget
     *
     * @return \Illuminate\View\View
     */
    public function delete(Budget $budget)
    {
        $subTitle = trans('firefly.delete_budget', ['name' => $budget->name]);

        // put previous url in session
        Session::put('budgets.delete.url', URL::previous());
        Session::flash('gaEventCategory', 'budgets');
        Session::flash('gaEventAction', 'delete');

        return view('budgets.delete', compact('budget', 'subTitle'));
    }

    /**
     * @param Budget                    $budget
     * @param BudgetRepositoryInterface $repository
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Budget $budget, BudgetRepositoryInterface $repository)
    {

        $name = $budget->name;
        $repository->destroy($budget);


        Session::flash('success', 'The  budget "' . e($name) . '" was deleted.');


        return Redirect::to(Session::get('budgets.delete.url'));
    }

    /**
     * @param Budget $budget
     *
     * @return \Illuminate\View\View
     */
    public function edit(Budget $budget)
    {
        $subTitle = 'Edit budget "' . e($budget->name) . '"';

        // put previous url in session if not redirect from store (not "return_to_edit").
        if (Session::get('budgets.edit.fromUpdate') !== true) {
            Session::put('budgets.edit.url', URL::previous());
        }
        Session::forget('budgets.edit.fromUpdate');
        Session::flash('gaEventCategory', 'budgets');
        Session::flash('gaEventAction', 'edit');

        return view('budgets.edit', compact('budget', 'subTitle'));

    }

    /**
     * @param BudgetRepositoryInterface $repository
     *
     * @return \Illuminate\View\View
     */
    public function index(BudgetRepositoryInterface $repository)
    {
        $budgets  = $repository->getActiveBudgets();
        $inactive = $repository->getInactiveBudgets();
        $spent    = '0';
        $budgeted = '0';
        bcscale(2);
        /**
         * Do some cleanup:
         */
        $repository->cleanupBudgets();


        // loop the budgets:
        /** @var Budget $budget */
        foreach ($budgets as $budget) {
            $date               = Session::get('start', Carbon::now()->startOfMonth());
            $end                = Session::get('end', Carbon::now()->endOfMonth());
            $budget->spent      = $repository->spentInPeriodCorrected($budget, $date, $end);
            $budget->currentRep = $repository->getCurrentRepetition($budget, $date);
            if ($budget->currentRep) {
                $budgeted = bcadd($budgeted, $budget->currentRep->amount);
            }
            $spent = bcadd($spent, $budget->spent);

        }

        $dateAsString      = Session::get('start', Carbon::now()->startOfMonth())->format('FY');
        $budgetIncomeTotal = Preferences::get('budgetIncomeTotal' . $dateAsString, 1000)->data;
        $budgetMaximum     = Preferences::get('budgetMaximum', 1000)->data;
        $defaultCurrency   = Amount::getDefaultCurrency();

        return view(
            'budgets.index', compact('budgetMaximum', 'budgetIncomeTotal', 'defaultCurrency', 'inactive', 'budgets', 'spent', 'budgeted')
        );
    }

    /**
     * @param BudgetRepositoryInterface $repository
     *
     * @return \Illuminate\View\View
     */
    public function noBudget(BudgetRepositoryInterface $repository)
    {
        $start    = Session::get('start', Carbon::now()->startOfMonth());
        $end      = Session::get('end', Carbon::now()->startOfMonth());
        $list     = $repository->getWithoutBudget($start, $end);
        $subTitle = trans(
            'firefly.without_budget_between',
            ['start' => $start->formatLocalized($this->monthAndDayFormat), 'end' => $end->formatLocalized($this->monthAndDayFormat)]
        );

        return view('budgets.noBudget', compact('list', 'subTitle'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUpdateIncome()
    {

        $date = Session::get('start', Carbon::now()->startOfMonth())->format('FY');
        Preferences::set('budgetIncomeTotal' . $date, intval(Input::get('amount')));

        return Redirect::route('budgets.index');
    }

    /**
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     * @param LimitRepetition           $repetition
     *
     * @return \Illuminate\View\View
     */
    public function show(BudgetRepositoryInterface $repository, Budget $budget, LimitRepetition $repetition = null)
    {
        if (!is_null($repetition->id) && $repetition->budgetLimit->budget->id != $budget->id) {
            $message = 'Invalid selection.';

            return view('error', compact('message'));
        }

        $journals = $repository->getJournals($budget, $repetition);
        $limits   = !is_null($repetition->id) ? [$repetition->budgetLimit] : $repository->getBudgetLimits($budget);
        $subTitle = !is_null($repetition->id)
            ?
            trans('firefly.budget_in_month', ['name' => $budget->name, 'month' => $repetition->startdate->formatLocalized($this->monthFormat)])
            :
            e($budget->name);
        $journals->setPath('/budgets/show/' . $budget->id);

        return view('budgets.show', compact('limits', 'budget', 'repetition', 'journals', 'subTitle'));
    }

    /**
     * @param BudgetFormRequest         $request
     * @param BudgetRepositoryInterface $repository
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BudgetFormRequest $request, BudgetRepositoryInterface $repository)
    {
        $budgetData = [
            'name' => $request->input('name'),
            'user' => Auth::user()->id,
        ];
        $budget = $repository->store($budgetData);

        Session::flash('success', 'New budget "' . $budget->name . '" stored!');

        if (intval(Input::get('create_another')) === 1) {
            // set value so create routine will not overwrite URL:
            Session::put('budgets.create.fromStore', true);

            return Redirect::route('budgets.create')->withInput();
        }

        // redirect to previous URL.
        return Redirect::to(Session::get('budgets.create.url'));

    }

    /**
     * @param BudgetFormRequest         $request
     * @param BudgetRepositoryInterface $repository
     * @param Budget                    $budget
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BudgetFormRequest $request, BudgetRepositoryInterface $repository, Budget $budget)
    {
        $budgetData = [
            'name'   => $request->input('name'),
            'active' => intval($request->input('active')) == 1
        ];

        $repository->update($budget, $budgetData);

        Session::flash('success', 'Budget "' . $budget->name . '" updated.');

        if (intval(Input::get('return_to_edit')) === 1) {
            // set value so edit routine will not overwrite URL:
            Session::put('budgets.edit.fromUpdate', true);

            return Redirect::route('budgets.edit', $budget->id)->withInput(['return_to_edit' => 1]);
        }

        // redirect to previous URL.
        return Redirect::to(Session::get('budgets.edit.url'));

    }

    /**
     * @return \Illuminate\View\View
     */
    public function updateIncome()
    {
        $date   = Session::get('start', Carbon::now()->startOfMonth())->format('FY');
        $amount = Preferences::get('budgetIncomeTotal' . $date, 1000);

        return view('budgets.income', compact('amount'));
    }

}
