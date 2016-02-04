<?php namespace FireflyIII\Models;

use Auth;
use Carbon\Carbon;
use Crypt;
use FireflyIII\Support\CacheProperties;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Watson\Validating\ValidatingTrait;

/**
 * FireflyIII\Models\TransactionJournal
 *
 * @property integer                                                          $id
 * @property \Carbon\Carbon                                                   $created_at
 * @property \Carbon\Carbon                                                   $updated_at
 * @property \Carbon\Carbon                                                   $deleted_at
 * @property integer                                                          $user_id
 * @property integer                                                          $transaction_type_id
 * @property integer                                                          $bill_id
 * @property integer                                                          $transaction_currency_id
 * @property string                                                           $description
 * @property boolean                                                          $completed
 * @property \Carbon\Carbon                                                   $date
 * @property boolean                                                          $encrypted
 * @property integer                                                          $order
 * @property integer                                                          $tag_count
 * @property-read Bill                                                        $bill
 * @property-read \Illuminate\Database\Eloquent\Collection|Budget[]           $budgets
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[]         $categories
 * @property-read mixed                                                       $amount_positive
 * @property-read mixed                                                       $amount
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[]              $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|Transaction[]      $transactions
 * @property-read mixed                                                       $destination_account
 * @property-read mixed                                                       $source_account
 * @property-read \Illuminate\Database\Eloquent\Collection|PiggyBankEvent[]   $piggyBankEvents
 * @property-read \Illuminate\Database\Eloquent\Collection|Attachment[]       $attachments
 * @property-read TransactionCurrency                                         $transactionCurrency
 * @property-read TransactionType                                             $transactionType
 * @property-read \Illuminate\Database\Eloquent\Collection|TransactionGroup[] $transactiongroups
 * @property-read \FireflyIII\User                                            $user
 * @property float                                                            $journalAmount
 * @property int                                                              $account_id
 * @property int                                                              $budget_id
 * @property string                                                           $account_name
 * @method static \Illuminate\Database\Query\Builder|TransactionJournal accountIs($account)
 * @method static \Illuminate\Database\Query\Builder|TransactionJournal after($date)
 * @method static \Illuminate\Database\Query\Builder|TransactionJournal before($date)
 * @method static \Illuminate\Database\Query\Builder|TransactionJournal onDate($date)
 * @method static \Illuminate\Database\Query\Builder|TransactionJournal transactionTypes($types)
 * @method static \Illuminate\Database\Query\Builder|TransactionJournal withRelevantData()
 * @property string                                                           $type
 * @property \Carbon\Carbon                                                   $interest_date
 * @property \Carbon\Carbon                                                   $book_date
 */
class TransactionJournal extends Model
{
    use SoftDeletes, ValidatingTrait;


    /** @var array */
    protected $dates = ['created_at', 'updated_at', 'date', 'deleted_at', 'interest_date', 'book_date'];
    /** @var array */
    protected $fillable
        = ['user_id', 'transaction_type_id', 'bill_id', 'transaction_currency_id', 'description', 'completed', 'date', 'encrypted', 'tag_count'];
    /** @var array */
    protected $hidden = ['encrypted'];
    /** @var array */
    protected $rules
        = [
            'user_id'                 => 'required|exists:users,id',
            'transaction_type_id'     => 'required|exists:transaction_types,id',
            'bill_id'                 => 'exists:bills,id',
            'transaction_currency_id' => 'required|exists:transaction_currencies,id',
            'description'             => 'required|between:1,1024',
            'completed'               => 'required|boolean',
            'date'                    => 'required|date',
            'encrypted'               => 'required|boolean',
        ];

    /** @var  bool */
    private $joinedTransactionTypes;

    /**
     * @param $value
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public static function routeBinder($value)
    {
        if (Auth::check()) {
            $validTypes = [TransactionType::WITHDRAWAL, TransactionType::DEPOSIT, TransactionType::TRANSFER];
            $object     = TransactionJournal::where('transaction_journals.id', $value)
                                            ->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id')
                                            ->whereIn('transaction_types.type', $validTypes)
                                            ->where('user_id', Auth::user()->id)->first(['transaction_journals.*']);
            if ($object) {
                return $object;
            }
        }

        throw new NotFoundHttpException;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany('FireflyIII\Models\Attachment', 'attachable');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bill()
    {
        return $this->belongsTo('FireflyIII\Models\Bill');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function budgets()
    {
        return $this->belongsToMany('FireflyIII\Models\Budget');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany('FireflyIII\Models\Category');
    }

    /**
     * @return float
     */
    public function getAmountAttribute()
    {
        $cache = new CacheProperties();
        $cache->addProperty($this->id);
        $cache->addProperty('amount');
        if ($cache->has()) {
            return $cache->get(); // @codeCoverageIgnore
        }

        bcscale(2);
        $transaction = $this->transactions->sortByDesc('amount')->first();
        $amount      = $transaction->amount;
        if ($this->isWithdrawal()) {
            $amount = $amount * -1;
        }
        $cache->store($amount);

        return $amount;

    }

    /**
     * @return string
     */
    public function getAmountPositiveAttribute()
    {
        $amount = '0';
        /** @var Transaction $t */
        foreach ($this->transactions as $t) {
            if ($t->amount > 0) {
                $amount = $t->amount;
            }
        }

        return $amount;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     *
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        if ($this->encrypted) {
            return Crypt::decrypt($value);
        }

        return $value;
    }

    /**
     * @return Account
     */
    public function getDestinationAccountAttribute()
    {
        $account = $this->transactions()->where('amount', '>', 0)->first()->account;

        return $account;
    }

    /**
     * @return Account
     */
    public function getSourceAccountAttribute()
    {
        $account = $this->transactions()->where('amount', '<', 0)->first()->account;

        return $account;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType->type;
    }

    /**
     * @return bool
     */
    public function isDeposit()
    {
        if (!is_null($this->type)) {
            return $this->type == TransactionType::DEPOSIT;
        }

        return $this->transactionType->isDeposit();
    }

    /**
     * @return bool
     */
    public function isOpeningBalance()
    {
        if (!is_null($this->type)) {
            return $this->type == TransactionType::OPENING_BALANCE;
        }

        return $this->transactionType->isOpeningBalance();
    }

    /**
     * @return bool
     */
    public function isTransfer()
    {
        if (!is_null($this->type)) {
            return $this->type == TransactionType::TRANSFER;
        }

        return $this->transactionType->isTransfer();
    }

    /**
     * @return bool
     */
    public function isWithdrawal()
    {
        if (!is_null($this->type)) {
            return $this->type == TransactionType::WITHDRAWAL;
        }

        return $this->transactionType->isWithdrawal();
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function piggyBankEvents()
    {
        return $this->hasMany('FireflyIII\Models\PiggyBankEvent');
    }

    /**
     * Save the model to the database.
     *
     * @param  array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        $count           = $this->tags()->count();
        $this->tag_count = $count;

        return parent::save($options);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param EloquentBuilder $query
     * @param Carbon          $date
     *
     * @return EloquentBuilder
     */
    public function scopeAfter(EloquentBuilder $query, Carbon $date)
    {
        return $query->where('transaction_journals.date', '>=', $date->format('Y-m-d 00:00:00'));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param EloquentBuilder $query
     * @param Carbon          $date
     *
     * @return EloquentBuilder
     */
    public function scopeBefore(EloquentBuilder $query, Carbon $date)
    {
        return $query->where('transaction_journals.date', '<=', $date->format('Y-m-d 00:00:00'));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param EloquentBuilder $query
     * @param array           $types
     */
    public function scopeTransactionTypes(EloquentBuilder $query, array $types)
    {
        if (is_null($this->joinedTransactionTypes)) {
            $query->leftJoin(
                'transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id'
            );
            $this->joinedTransactionTypes = true;
        }
        $query->whereIn('transaction_types.type', $types);
    }

    /**
     * @codeCoverageIgnore
     * Automatically includes the 'with' parameters to get relevant related
     * objects.
     *
     * @param EloquentBuilder $query
     */
    public function scopeWithRelevantData(EloquentBuilder $query)
    {
        $query->with(
            ['transactions' => function (HasMany $q) {
                $q->orderBy('amount', 'ASC');
            }, 'transactionType', 'transactionCurrency', 'budgets', 'categories', 'transactions.account.accounttype', 'bill']
        );
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $value
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = Crypt::encrypt($value);
        $this->attributes['encrypted']   = true;
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('FireflyIII\Models\Tag');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionCurrency()
    {
        return $this->belongsTo('FireflyIII\Models\TransactionCurrency');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType()
    {
        return $this->belongsTo('FireflyIII\Models\TransactionType');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactiongroups()
    {
        return $this->belongsToMany('FireflyIII\Models\TransactionGroup');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany('FireflyIII\Models\Transaction');
    }

    /**
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('FireflyIII\User');
    }
}
