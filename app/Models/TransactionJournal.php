<?php namespace FireflyIII\Models;

use Auth;
use Carbon\Carbon;
use Crypt;
use FireflyIII\Support\Models\TransactionJournalSupport;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Watson\Validating\ValidatingTrait;

/**
 * FireflyIII\Models\TransactionJournal
 *
 * @property integer                                                                                   $id
 * @property \Carbon\Carbon                                                                            $created_at
 * @property \Carbon\Carbon                                                                            $updated_at
 * @property \Carbon\Carbon                                                                            $deleted_at
 * @property integer                                                                                   $user_id
 * @property integer                                                                                   $transaction_type_id
 * @property integer                                                                                   $bill_id
 * @property integer                                                                                   $transaction_currency_id
 * @property string                                                                                    $description
 * @property boolean                                                                                   $completed
 * @property \Carbon\Carbon                                                                            $date
 * @property \Carbon\Carbon                                                                            $interest_date
 * @property \Carbon\Carbon                                                                            $book_date
 * @property boolean                                                                                   $encrypted
 * @property integer                                                                                   $order
 * @property integer                                                                                   $tag_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\Attachment[]             $attachments
 * @property-read \FireflyIII\Models\Bill                                                              $bill
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\Budget[]                 $budgets
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\Category[]               $categories
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\PiggyBankEvent[]         $piggyBankEvents
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\Tag[]                    $tags
 * @property-read \FireflyIII\Models\TransactionCurrency                                               $transactionCurrency
 * @property-read \FireflyIII\Models\TransactionType                                                   $transactionType
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\TransactionGroup[]       $transactiongroups
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\TransactionJournalMeta[] $transactionjournalmeta
 * @property-read \Illuminate\Database\Eloquent\Collection|\FireflyIII\Models\Transaction[]            $transactions
 * @property-read \FireflyIII\User                                                                     $user
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal after($date)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal before($date)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal transactionTypes($types)
 * @property-read string                                                                               $transaction_type_type
 * @property-read string                                                                               $transaction_currency_code
 * @property-read string                                                                               $destination_amount
 * @property-read string                                                                               $destination_account_id
 * @property-read string                                                                               $destination_account_name
 * @property-read string                                                                               $destination_account_type
 * @property-read string                                                                               $source_amount
 * @property-read string                                                                               $source_account_id
 * @property-read string                                                                               $source_account_name
 * @property-read string                                                                               $source_account_type
 * @property \Carbon\Carbon                                                                            $process_date
 * @property int                                                                                       $account_id
 * @property float                                                                                     $journalAmount
 * @property string                                                                                    $account_name
 * @property int                                                                                       $budget_id
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereTransactionTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereBillId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereTransactionCurrencyId($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereCompleted($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereDate($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereInterestDate($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereBookDate($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereProcessDate($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereEncrypted($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereOrder($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal whereTagCount($value)
 * @method static \Illuminate\Database\Query\Builder|\FireflyIII\Models\TransactionJournal expanded()
 * @mixin \Eloquent
 */
class TransactionJournal extends TransactionJournalSupport
{
    use SoftDeletes, ValidatingTrait;

    /**
     * Fields which queries must load.
     * ['transaction_journals.*', 'transaction_currencies.symbol', 'transaction_types.type']
     */
    const QUERYFIELDS
        = [
            'transaction_journals.*',
            'transaction_types.type AS transaction_type_type', // the other field is called "transaction_type_id" so this is pretty consistent.
            'transaction_currencies.code AS transaction_currency_code',
            // all for destination:
            'destination.amount AS destination_amount', // is always positive
            'destination_account.id AS destination_account_id',
            'destination_account.name AS destination_account_name',
            'destination_acct_type.type AS destination_account_type',
            // all for source:
            'source.amount AS source_amount', // is always negative
            'source_account.id AS source_account_id',
            'source_account.name AS source_account_name',
            'source_acct_type.type AS source_account_type',

        ];
    /** @var array */
    protected $dates = ['created_at', 'updated_at', 'date', 'deleted_at', 'interest_date', 'book_date', 'process_date'];
    /** @var array */
    protected $fillable
        = ['user_id', 'transaction_type_id', 'bill_id', 'interest_date', 'book_date', 'process_date',
           'transaction_currency_id', 'description', 'completed',
           'date', 'rent_date', 'encrypted', 'tag_count'];
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bill()
    {
        return $this->belongsTo('FireflyIII\Models\Bill');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function budgets()
    {
        return $this->belongsToMany('FireflyIII\Models\Budget');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany('FireflyIII\Models\Category');
    }

    /**
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
     * @param $value
     *
     * @return string
     */
    public function getDestinationAccountNameAttribute($value)
    {
        if (!is_null($value) && strlen(strval($value)) > 0) {
            return Crypt::decrypt($value);
        }

        return null;
    }

    /**
     *
     * @param string $fieldName
     *
     * @return string
     */
    public function getMeta($fieldName)
    {
        foreach ($this->transactionjournalmeta as $meta) {
            if ($meta->name == $fieldName) {
                return $meta->data;
            }
        }

        return '';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getSourceAccountNameAttribute($value)
    {
        if (!is_null($value) && strlen(strval($value)) > 0) {
            return Crypt::decrypt($value);
        }

        return null;

    }

    /**
     * @return bool
     */
    public function isDeposit()
    {
        if (!is_null($this->transaction_type_type)) {
            return $this->transaction_type_type == TransactionType::DEPOSIT;
        }

        return $this->transactionType->isDeposit();
    }

    /**
     *
     * @return bool
     */
    public function isOpeningBalance()
    {
        if (!is_null($this->transaction_type_type)) {
            return $this->transaction_type_type == TransactionType::OPENING_BALANCE;
        }

        return $this->transactionType->isOpeningBalance();
    }

    /**
     *
     * @return bool
     */
    public function isTransfer()
    {
        if (!is_null($this->transaction_type_type)) {
            return $this->transaction_type_type == TransactionType::TRANSFER;
        }

        return $this->transactionType->isTransfer();
    }

    /**
     *
     * @return bool
     */
    public function isWithdrawal()
    {
        if (!is_null($this->transaction_type_type)) {
            return $this->transaction_type_type == TransactionType::WITHDRAWAL;
        }

        return $this->transactionType->isWithdrawal();
    }

    /**
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
     * @param EloquentBuilder $query
     */
    public function scopeExpanded(EloquentBuilder $query)
    {
        // left join transaction type:
        if (!self::isJoined($query, 'transaction_types')) {
            $query->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        }

        // left join transaction currency:
        $query->leftJoin('transaction_currencies', 'transaction_currencies.id', '=', 'transaction_journals.transaction_currency_id');

        // left join destination (for amount and account info).
        $query->leftJoin(
            'transactions as destination', function (JoinClause $join) {
            $join->on('destination.transaction_journal_id', '=', 'transaction_journals.id')
                 ->where('destination.amount', '>', 0);
        }
        );
        // join destination account
        $query->leftJoin('accounts as destination_account', 'destination_account.id', '=', 'destination.account_id');
        // join destination account type
        $query->leftJoin('account_types as destination_acct_type', 'destination_account.account_type_id', '=', 'destination_acct_type.id');

        // left join source (for amount and account info).
        $query->leftJoin(
            'transactions as source', function (JoinClause $join) {
            $join->on('source.transaction_journal_id', '=', 'transaction_journals.id')
                 ->where('source.amount', '<', 0);
        }
        );
        // join destination account
        $query->leftJoin('accounts as source_account', 'source_account.id', '=', 'source.account_id');
        // join destination account type
        $query->leftJoin('account_types as source_acct_type', 'source_account.account_type_id', '=', 'source_acct_type.id')
              ->orderBy('transaction_journals.date', 'DESC')->orderBy('transaction_journals.order', 'ASC')->orderBy('transaction_journals.id', 'DESC');

        $query->with(['categories', 'budgets', 'attachments', 'bill']);


    }

    /**
     *
     * @param EloquentBuilder $query
     * @param array           $types
     */
    public function scopeTransactionTypes(EloquentBuilder $query, array $types)
    {

        if (!self::isJoined($query, 'transaction_types')) {
            $query->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        }
        $query->whereIn('transaction_types.type', $types);
    }

    /**
     *
     * @param $value
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = Crypt::encrypt($value);
        $this->attributes['encrypted']   = true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('FireflyIII\Models\Tag');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionCurrency()
    {
        return $this->belongsTo('FireflyIII\Models\TransactionCurrency');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionType()
    {
        return $this->belongsTo('FireflyIII\Models\TransactionType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactiongroups()
    {
        return $this->belongsToMany('FireflyIII\Models\TransactionGroup');
    }

    /**
     * @return HasMany
     */
    public function transactionjournalmeta(): HasMany
    {
        return $this->hasMany('FireflyIII\Models\TransactionJournalMeta');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany('FireflyIII\Models\Transaction');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('FireflyIII\User');
    }
}
