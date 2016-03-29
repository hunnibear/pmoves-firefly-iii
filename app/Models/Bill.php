<?php namespace FireflyIII\Models;

use Auth;
use Crypt;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FireflyIII\Models\Bill
 *
 * @property integer                                                            $id
 * @property \Carbon\Carbon                                                     $created_at
 * @property \Carbon\Carbon                                                     $updated_at
 * @property integer                                                            $user_id
 * @property string                                                             $name
 * @property string                                                             $match
 * @property float                                                              $amount_min
 * @property float                                                              $amount_max
 * @property \Carbon\Carbon                                                     $date
 * @property boolean                                                            $active
 * @property boolean                                                            $automatch
 * @property string                                                             $repeat_freq
 * @property integer                                                            $skip
 * @property boolean                                                            $name_encrypted
 * @property boolean                                                            $match_encrypted
 * @property-read \Illuminate\Database\Eloquent\Collection|TransactionJournal[] $transactionjournals
 * @property-read \FireflyIII\User                                              $user
 * @property \Carbon\Carbon                                                     $nextExpectedMatch
 * @property \Carbon\Carbon                                                     $lastFoundMatch
 * @property bool                                                               $paidInPeriod
 * @property string                                                             $lastPaidAmount
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereMatch($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereAmountMin($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereAmountMax($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereDate($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereAutomatch($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereRepeatFreq($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereSkip($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereNameEncrypted($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\Bill whereMatchEncrypted($value)
 * @mixin \Eloquent
 */
class Bill extends Model
{

    protected $dates  = ['created_at', 'updated_at', 'date'];
    protected $fillable
                      = ['name', 'match', 'amount_min', 'match_encrypted', 'name_encrypted', 'user_id', 'amount_max', 'date', 'repeat_freq', 'skip',
                         'automatch', 'active',];
    protected $hidden = ['amount_min_encrypted', 'amount_max_encrypted', 'name_encrypted', 'match_encrypted'];

    /**
     * @param Bill $value
     *
     * @return Bill
     */
    public static function routeBinder(Bill $value)
    {
        if (Auth::check()) {
            if ($value->user_id == Auth::user()->id) {
                return $value;
            }
        }
        throw new NotFoundHttpException;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getMatchAttribute($value)
    {

        if (intval($this->match_encrypted) == 1) {
            return Crypt::decrypt($value);
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getNameAttribute($value)
    {

        if (intval($this->name_encrypted) == 1) {
            return Crypt::decrypt($value);
        }

        return $value;
    }

    /**
     * @param $value
     */
    public function setAmountMaxAttribute($value)
    {
        $this->attributes['amount_max'] = strval(round($value, 2));
    }

    /**
     * @param $value
     */
    public function setAmountMinAttribute($value)
    {
        $this->attributes['amount_min'] = strval(round($value, 2));
    }

    /**
     * @param $value
     */
    public function setMatchAttribute($value)
    {
        $this->attributes['match']           = Crypt::encrypt($value);
        $this->attributes['match_encrypted'] = true;
    }

    /**
     * @param $value
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name']           = Crypt::encrypt($value);
        $this->attributes['name_encrypted'] = true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactionjournals()
    {
        return $this->hasMany('FireflyIII\Models\TransactionJournal');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('FireflyIII\User');
    }


}
