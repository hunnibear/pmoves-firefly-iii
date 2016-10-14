<?php
/**
 * RuleGroup.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms of the
 * Creative Commons Attribution-ShareAlike 4.0 International License.
 *
 * See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RuleGroup
 *
 * @package FireflyIII\Models
 * @property integer                                                                 $id
 * @property \Carbon\Carbon                                                          $created_at
 * @property \Carbon\Carbon                                                          $updated_at
 * @property string                                                                  $deleted_at
 * @property integer                                                                 $user_id
 * @property integer                                                                 $order
 * @property string                                                                  $title
 * @property string                                                                  $description
 * @property boolean                                                                 $active
 * @property-read \FireflyIII\User                                                   $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\Rule[] $rules
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereOrder($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\RuleGroup whereActive($value)
 * @mixin \Eloquent
 */
class RuleGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'order', 'title', 'description', 'active'];

    /**
     * @param RuleGroup $value
     *
     * @return RuleGroup
     */
    public static function routeBinder(RuleGroup $value)
    {
        if (auth()->check()) {
            if ($value->user_id == auth()->user()->id) {
                return $value;
            }
        }
        throw new NotFoundHttpException;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rules()
    {
        return $this->hasMany('FireflyIII\Models\Rule');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('FireflyIII\User');
    }
}
