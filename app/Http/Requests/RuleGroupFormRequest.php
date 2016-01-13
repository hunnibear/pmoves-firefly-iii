<?php
/**
 * RuleGroupFormRequest.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Http\Requests;

use Auth;
use FireflyIII\Models\RuleGroup;
use Input;

/**
 * Class RuleGroupFormRequest
 *
 * @codeCoverageIgnore
 * @package FireflyIII\Http\Requests
 */
class RuleGroupFormRequest extends Request
{
    /**
     * @return bool
     */
    public function authorize()
    {
        // Only allow logged in users
        return Auth::check();
    }

    /**
     * @return array
     */
    public function rules()
    {

        $titleRule = 'required|between:1,100|uniqueObjectForUser:rule_groups,title';
        if (RuleGroup::find(Input::get('id'))) {
            $titleRule = 'required|between:1,100|uniqueObjectForUser:rule_groups,title,' . intval(Input::get('id'));
        }

        return [
            'title'        => $titleRule,
            'description' => 'between:1,5000',
        ];
    }
}
