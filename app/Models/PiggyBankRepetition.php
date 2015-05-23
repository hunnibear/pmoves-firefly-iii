<?php namespace FireflyIII\Models;

use Carbon\Carbon;
use Crypt;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PiggyBankRepetition
 *
 * @codeCoverageIgnore
 * @package FireflyIII\Models
 */
class PiggyBankRepetition extends Model
{

    protected $fillable = ['piggy_bank_id', 'startdate', 'targetdate', 'currentamount'];

    /**
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'startdate', 'targetdate'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function piggyBank()
    {
        return $this->belongsTo('FireflyIII\Models\PiggyBank');
    }

    /**
     * @param EloquentBuilder $query
     * @param Carbon          $start
     * @param Carbon          $target
     *
     * @return $this
     */
    public function scopeOnDates(EloquentBuilder $query, Carbon $start, Carbon $target)
    {
        return $query->where('startdate', $start->format('Y-m-d'))->where('targetdate', $target->format('Y-m-d'));
    }

    /**
     * @param EloquentBuilder $query
     * @param Carbon          $date
     *
     * @return mixed
     */
    public function scopeRelevantOnDate(EloquentBuilder $query, Carbon $date)
    {
        return $query->where(
            function (EloquentBuilder $q) use ($date) {
                $q->where('startdate', '<=', $date->format('Y-m-d 00:00:00'));
                $q->orWhereNull('startdate');
            }
        )
                     ->where(
                         function (EloquentBuilder $q) use ($date) {

                             $q->where('targetdate', '>=', $date->format('Y-m-d 00:00:00'));
                             $q->orWhereNull('targetdate');
                         }
                     );
    }

    /**
     * @param $value
     *
     * @return float|int
     */
    public function getCurrentamountAttribute($value)
    {
        if (is_null($this->currentamount_encrypted)) {
            return $value;
        }
        $value = intval(Crypt::decrypt($this->currentamount_encrypted));
        $value = $value / 100;

        return $value;
    }

    /**
     * @param $value
     */
    public function setCurrentamountAttribute($value)
    {
        // save in cents:
        $value                                       = intval($value * 100);
        $this->attributes['currentamount_encrypted'] = Crypt::encrypt($value);
        $this->attributes['currentamount']           = ($value / 100);
    }

}
