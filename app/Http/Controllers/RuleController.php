<?php
/**
 * RuleController.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Http\Controllers;

use Auth;
use FireflyIII\Http\Requests;
use FireflyIII\Http\Requests\RuleFormRequest;
use FireflyIII\Models\Rule;
use FireflyIII\Models\RuleAction;
use FireflyIII\Models\RuleGroup;
use FireflyIII\Models\RuleTrigger;
use FireflyIII\Repositories\Rule\RuleRepositoryInterface;
use Input;
use Preferences;
use Response;
use Session;
use URL;
use View;

/**
 * Class RuleController
 *
 * @package FireflyIII\Http\Controllers
 */
class RuleController extends Controller
{
    /**
     * RuleController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        View::share('title', trans('firefly.rules'));
        View::share('mainTitleIcon', 'fa-random');
    }

    /**
     * @param RuleFormRequest         $request
     * @param RuleRepositoryInterface $repository
     * @param RuleGroup               $ruleGroup
     *
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(RuleFormRequest $request, RuleRepositoryInterface $repository, RuleGroup $ruleGroup)
    {


        // process the rule itself:
        $data = [
            'rule_group_id'       => $ruleGroup->id,
            'title'               => $request->get('title'),
            'trigger'             => $request->get('trigger'),
            'description'         => $request->get('description'),
            'rule-triggers'       => $request->get('rule-trigger'),
            'rule-trigger-values' => $request->get('rule-trigger-value'),
            'rule-trigger-stop'   => $request->get('rule-trigger-stop'),
            'rule-actions'        => $request->get('rule-action'),
            'rule-action-values'  => $request->get('rule-action-value'),
            'rule-action-stop'    => $request->get('rule-action-stop'),
            'stop_processing'     => $request->get('stop_processing'),
        ];

        $rule = $repository->store($data);
        Session::flash('success', trans('firefly.stored_new_rule', ['title' => $rule->title]));
        Preferences::mark();

        if (intval(Input::get('create_another')) === 1) {
            // set value so create routine will not overwrite URL:
            Session::put('rules.rule.create.fromStore', true);

            return redirect(route('rules.rule.create', [$request->input('what')]))->withInput();
        }

        // redirect to previous URL.
        return redirect(Session::get('rules.rule.create.url'));

    }

    /**
     * @param RuleGroup $ruleGroup
     *
     * @return View
     */
    public function create(RuleGroup $ruleGroup)
    {
        // count for possible present previous entered triggers/actions.
        $triggerCount = 0;
        $actionCount  = 0;

        // collection of those triggers/actions.
        $oldTriggers = [];
        $oldActions  = [];

        // has old input?
        if (Input::old()) {
            // process old triggers.
            $oldTriggers  = $this->getPreviousTriggers();
            $triggerCount = count($oldTriggers);

            // process old actions
            $oldActions  = $this->getPreviousActions();
            $actionCount = count($oldActions);
        }

        $subTitleIcon = 'fa-clone';
        $subTitle     = trans('firefly.make_new_rule', ['title' => $ruleGroup->title]);

        // put previous url in session if not redirect from store (not "create another").
        if (Session::get('rules.rule.create.fromStore') !== true) {
            Session::put('rules.rule.create.url', URL::previous());
        }
        Session::forget('rules.rule.create.fromStore');
        Session::flash('gaEventCategory', 'rules');
        Session::flash('gaEventAction', 'create-rule');

        return view(
            'rules.rule.create', compact('subTitleIcon', 'oldTriggers', 'oldActions', 'triggerCount', 'actionCount', 'ruleGroup', 'subTitle')
        );
    }

    /**
     * @param Rule $rule
     *
     * @return View
     */
    public function edit(Rule $rule)
    {
        // has old input?
        if (Input::old()) {
            // process old triggers.
            $oldTriggers  = $this->getPreviousTriggers();
            $triggerCount = count($oldTriggers);

            // process old actions
            $oldActions  = $this->getPreviousActions();
            $actionCount = count($oldActions);
        } else {
            // get current triggers
            $oldTriggers  = $this->getCurrentTriggers($rule);
            $triggerCount = count($oldTriggers);

            // get current actions
            $oldActions  = $this->getCurrentActions($rule);
            $actionCount = count($oldActions);
        }

        // get rule trigger for update / store-journal:
        $primaryTrigger = $rule->ruleTriggers()->where('trigger_type', 'user_action')->first()->trigger_value;


        $subTitle = trans('firefly.edit_rule', ['title' => $rule->title]);

        // put previous url in session if not redirect from store (not "return_to_edit").
        if (Session::get('rules.rule.edit.fromUpdate') !== true) {
            Session::put('rules.rule.edit.url', URL::previous());
        }
        Session::forget('rules.rule.edit.fromUpdate');
        Session::flash('gaEventCategory', 'rules');
        Session::flash('gaEventAction', 'edit-rule');

        return view(
            'rules.rule.edit', compact(
                                 'rule', 'subTitle', 'primaryTrigger',
                                 'oldTriggers', 'oldActions', 'triggerCount', 'actionCount'
                             )
        );
    }

    /**
     * @param RuleRepositoryInterface $repository
     * @param RuleFormRequest         $request
     * @param Rule                    $rule
     *
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(RuleRepositoryInterface $repository, RuleFormRequest $request, Rule $rule)
    {

        // process the rule itself:
        $data = [
            'title'               => $request->get('title'),
            'active'              => intval($request->get('active')) == 1,
            'trigger'             => $request->get('trigger'),
            'description'         => $request->get('description'),
            'rule-triggers'       => $request->get('rule-trigger'),
            'rule-trigger-values' => $request->get('rule-trigger-value'),
            'rule-trigger-stop'   => $request->get('rule-trigger-stop'),
            'rule-actions'        => $request->get('rule-action'),
            'rule-action-values'  => $request->get('rule-action-value'),
            'rule-action-stop'    => $request->get('rule-action-stop'),
            'stop_processing'     => intval($request->get('stop_processing')) == 1,
        ];
        $repository->update($rule, $data);

        Session::flash('success', trans('firefly.updated_rule', ['title' => $rule->title]));
        Preferences::mark();

        if (intval(Input::get('return_to_edit')) === 1) {
            // set value so edit routine will not overwrite URL:
            Session::put('rules.rule.edit.fromUpdate', true);

            return redirect(route('rules.rule.edit', [$rule->id]))->withInput(['return_to_edit' => 1]);
        }

        // redirect to previous URL.
        return redirect(Session::get('rules.rule.edit.url'));
    }

    /**
     * @param Rule $rule
     *
     * @return View
     * @internal param RuleRepositoryInterface $repository
     */
    public function delete(Rule $rule)
    {
        $subTitle = trans('firefly.delete_rule', ['title' => $rule->title]);

        // put previous url in session
        Session::put('rules.rule.delete.url', URL::previous());
        Session::flash('gaEventCategory', 'rules');
        Session::flash('gaEventAction', 'delete-rule');

        return view('rules.rule.delete', compact('rule', 'subTitle'));
    }


    /**
     * @param Rule                    $rule
     * @param RuleRepositoryInterface $repository
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(RuleRepositoryInterface $repository, Rule $rule)
    {

        $title = $rule->title;
        $repository->destroy($rule);

        Session::flash('success', trans('firefly.deleted_rule', ['title' => $title]));
        Preferences::mark();


        return redirect(Session::get('rules.rule.delete.url'));
    }

    /**
     * @param RuleRepositoryInterface $repository
     * @param Rule                    $rule
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderRuleTriggers(RuleRepositoryInterface $repository, Rule $rule)
    {
        $ids = Input::get('triggers');
        if (is_array($ids)) {
            $repository->reorderRuleTriggers($rule, $ids);
        }

        return Response::json(true);

    }

    /**
     * @param RuleRepositoryInterface $repository
     * @param Rule                    $rule
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderRuleActions(RuleRepositoryInterface $repository, Rule $rule)
    {
        $ids = Input::get('actions');
        if (is_array($ids)) {
            $repository->reorderRuleActions($rule, $ids);
        }

        return Response::json(true);

    }


    /**
     * @return View
     */
    public function index()
    {
        $ruleGroups = Auth::user()
                          ->ruleGroups()
                          ->orderBy('active', 'DESC')
                          ->orderBy('order', 'ASC')
                          ->with(
                              [
                                  'rules'              => function ($query) {
                                      $query->orderBy('active', 'DESC');
                                      $query->orderBy('order', 'ASC');

                                  },
                                  'rules.ruleTriggers' => function ($query) {
                                      $query->orderBy('order', 'ASC');
                                  },
                                  'rules.ruleActions'  => function ($query) {
                                      $query->orderBy('order', 'ASC');
                                  },
                              ]
                          )->get();

        return view('rules.index', compact('ruleGroups'));
    }


    /**
     * @param RuleRepositoryInterface $repository
     * @param Rule                    $rule
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function up(RuleRepositoryInterface $repository, Rule $rule)
    {
        $repository->moveUp($rule);

        return redirect(route('rules.index'));

    }

    /**
     * @param RuleRepositoryInterface $repository
     * @param Rule                    $rule
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function down(RuleRepositoryInterface $repository, Rule $rule)
    {
        $repository->moveDown($rule);

        return redirect(route('rules.index'));

    }

    /**
     * @param Rule $rule
     */
    private function getCurrentActions(Rule $rule)
    {
        $index   = 0;
        $actions = [];

        /** @var RuleAction $entry */
        foreach ($rule->ruleActions as $entry) {
            $count     = ($index + 1);
            $actions[] = view(
                'rules.partials.action',
                [
                    'oldTrigger' => $entry->action_type,
                    'oldValue'   => $entry->action_value,
                    'oldChecked' => $entry->stop_processing,
                    'count'      => $count,
                ]
            )->render();
            $index++;
        }

        return $actions;
    }

    /**
     * @param Rule $rule
     *
     * @return array
     */
    private function getCurrentTriggers(Rule $rule)
    {
        $index    = 0;
        $triggers = [];

        /** @var RuleTrigger $entry */
        foreach ($rule->ruleTriggers as $entry) {
            if ($entry->trigger_type != 'user_action') {
                $count      = ($index + 1);
                $triggers[] = view(
                    'rules.partials.trigger',
                    [
                        'oldTrigger' => $entry->trigger_type,
                        'oldValue'   => $entry->trigger_value,
                        'oldChecked' => $entry->stop_processing,
                        'count'      => $count,
                    ]
                )->render();
                $index++;
            }
        }

        return $triggers;
    }

    /**
     * @return array
     */
    private function getPreviousActions()
    {
        $newIndex = 0;
        $actions  = [];
        foreach (Input::old('rule-action') as $index => $entry) {
            $count     = ($newIndex + 1);
            $checked   = isset(Input::old('rule-action-stop')[$index]) ? true : false;
            $actions[] = view(
                'rules.partials.action',
                [
                    'oldTrigger' => $entry,
                    'oldValue'   => Input::old('rule-action-value')[$index],
                    'oldChecked' => $checked,
                    'count'      => $count,
                ]
            )->render();
            $newIndex++;
        }

        return $actions;
    }

    /**
     * @return array
     */
    private function getPreviousTriggers()
    {
        $newIndex = 0;
        $triggers = [];
        foreach (Input::old('rule-trigger') as $index => $entry) {
            $count      = ($newIndex + 1);
            $oldChecked = isset(Input::old('rule-trigger-stop')[$index]) ? true : false;
            $triggers[] = view(
                'rules.partials.trigger',
                [
                    'oldTrigger' => $entry,
                    'oldValue'   => Input::old('rule-trigger-value')[$index],
                    'oldChecked' => $oldChecked,
                    'count'      => $count,
                ]
            )->render();
            $newIndex++;
        }

        return $triggers;
    }


}
