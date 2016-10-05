<?php
/**
 * AccountMeta.php
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FireflyIII\Models\AccountMeta
 *
 * @property integer        $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property integer        $account_id
 * @property string         $name
 * @property string         $data
 * @property-read Account   $account
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\AccountMeta whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\AccountMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\AccountMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\AccountMeta whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\AccountMeta whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\AccountMeta whereData($value)
 * @mixin \Eloquent
 */
class AccountMeta extends Model
{

    protected $dates    = ['created_at', 'updated_at'];
    protected $fillable = ['account_id', 'name', 'data'];
    protected $table    = 'account_meta';

    /**
     *
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo('FireflyIII\Models\Account');
    }


    /**
     * @param $value
     *
     * @return mixed
     */
    public function getDataAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

}
