<?php

namespace FireflyIII\Helpers\Csv;

use App;
use Auth;
use Config;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Helpers\Csv\Converter\ConverterInterface;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionCurrency;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Models\TransactionType;
use Illuminate\Support\MessageBag;
use Log;
use Preferences;

set_time_limit(0);

/**
 * Class Importer
 *
 * @package FireflyIII\Helpers\Csv
 */
class Importer
{

    /** @var Data */
    protected $data;
    /** @var array */
    protected $errors;
    /** @var int */
    protected $imported;
    /** @var array */
    protected $map;
    /** @var  array */
    protected $mapped;
    /** @var  array */
    protected $roles;
    /** @var  int */
    protected $rows = 0;

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getImported()
    {
        return $this->imported;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @throws FireflyException
     */
    public function run()
    {
        $this->map    = $this->data->getMap();
        $this->roles  = $this->data->getRoles();
        $this->mapped = $this->data->getMapped();

        foreach ($this->data->getReader() as $index => $row) {
            if ($this->parseRow($index)) {
                $this->rows++;
                $result = $this->importRow($row);
                if (!($result === true)) {
                    Log::error('Caught error at row #' . $index . ': ' . $result);
                    $this->errors[$index] = $result;
                } else {
                    $this->imported++;
                }
            }
        }
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    protected function parseRow($index)
    {
        return (($this->data->getHasHeaders() && $index > 1) || !$this->data->getHasHeaders());
    }

    /**
     * @param $row
     *
     * @throws FireflyException
     * @return string|bool
     */
    protected function importRow($row)
    {
        /*
         * These fields are necessary to create a new transaction journal. Some are optional:
         */
        $data = $this->getFiller();
        foreach ($row as $index => $value) {
            $role  = isset($this->roles[$index]) ? $this->roles[$index] : '_ignore';
            $class = Config::get('csv.roles.' . $role . '.converter');
            $field = Config::get('csv.roles.' . $role . '.field');

            /** @var ConverterInterface $converter */
            $converter = App::make('FireflyIII\Helpers\Csv\Converter\\' . $class);
            $converter->setData($data); // the complete array so far.
            $converter->setField($field);
            $converter->setIndex($index);
            $converter->setMapped($this->mapped);
            $converter->setValue($value);
            $converter->setRole($role);
            $data[$field] = $converter->convert();

        }
        // post processing and validating.
        $data   = $this->postProcess($data);
        $result = $this->validateData($data);
        if ($result === true) {
            $result = $this->createTransactionJournal($data);
        } else {
            Log::error('Validator: ' . $result);
        }
        if ($result instanceof TransactionJournal) {
            return true;
        }

        return 'Not a journal.';

    }

    /**
     * @return array
     */
    protected function getFiller()
    {
        $filler = [];
        foreach (Config::get('csv.roles') as $role) {
            if (isset($role['field'])) {
                $fieldName          = $role['field'];
                $filler[$fieldName] = null;
            }
        }
        // some extra's:
        $filler['bill-id']                 = null;
        $filler['opposing-account-object'] = null;
        $filler['amount-modifier']         = '1';

        return $filler;

    }

    /**
     * Row denotes the original data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function postProcess(array $data)
    {
        // fix two simple fields:
        bcscale(2);
        $data['description'] = trim($data['description']);
        $data['amount']      = bcmul($data['amount'], $data['amount-modifier']);

        if (strlen($data['description']) == 0) {
            $data['description'] = trans('firefly.csv_empty_description');
        }

        // fix currency
        if (is_null($data['currency'])) {
            $currencyPreference = Preferences::get('currencyPreference', 'EUR');
            $data['currency']   = TransactionCurrency::whereCode($currencyPreference->data)->first();
        }

        // get bill id.
        if (!is_null($data['bill'])) {
            $data['bill-id'] = $data['bill']->id;
        }

        // opposing account can be difficult.

        // get opposing account, which is quite complex:
        $opposingAccount                 = new OpposingAccount($data);
        $data['opposing-account-object'] = $opposingAccount->parse();

        // do bank specific fixes:

        //        $specifix = new Specifix();
        //        $specifix->setData($data);
        //        $specifix->setRow($row);
        //$specifix->fix($data, $row); // TODO
        // get data back:
        //$data = $specifix->getData(); // TODO

        return $data;
    }

    /**
     * @param $data
     *
     * @return bool|string
     */
    protected function validateData($data)
    {
        if (is_null($data['date']) && is_null($data['date-rent'])) {
            return 'No date value for this row.';
        }
        if (is_null($data['opposing-account-object'])) {
            return 'Opposing account is null';
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    protected function createTransactionJournal(array $data)
    {
        bcscale(2);
        $date = $data['date'];
        if (is_null($data['date'])) {
            $date = $data['date-rent'];
        }

        // defaults to deposit
        $transactionType = TransactionType::where('type', 'Deposit')->first();
        if ($data['amount'] < 0) {
            $transactionType = TransactionType::where('type', 'Withdrawal')->first();
        }

        if ($data['opposing-account-object']->accountType->type == 'Asset account') {
            $transactionType = TransactionType::where('type', 'Transfer')->first();
        }

        $errors  = new MessageBag;
        $journal = TransactionJournal::create(
            [
                'user_id'                 => Auth::user()->id,
                'transaction_type_id'     => $transactionType->id,
                'transaction_currency_id' => $data['currency']->id,
                'description'             => $data['description'],
                'completed'               => 0,
                'date'                    => $date,
                'bill_id'                 => $data['bill-id'],
            ]
        );
        $errors  = $journal->getErrors()->merge($errors);
        if ($journal->getErrors()->count() == 0) {
            // create both transactions:
            $transaction = Transaction::create(
                [
                    'transaction_journal_id' => $journal->id,
                    'account_id'             => $data['asset-account']->id,
                    'amount'                 => $data['amount']
                ]
            );
            $errors      = $transaction->getErrors()->merge($errors);

            $transaction = Transaction::create(
                [
                    'transaction_journal_id' => $journal->id,
                    'account_id'             => $data['opposing-account-object']->id,
                    'amount'                 => bcmul($data['amount'], -1)
                ]
            );
            $errors      = $transaction->getErrors()->merge($errors);
        }
        if ($errors->count() == 0) {
            $journal->completed = 1;
            $journal->save();
        }

        // add budget:
        if (!is_null($data['budget'])) {
            $journal->budgets()->save($data['budget']);
        }

        // add category:
        if (!is_null($data['category'])) {
            $journal->categories()->save($data['category']);
        }
        if (!is_null($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $journal->tags()->save($tag);
            }
        }

        return $journal;


    }

    /**
     * @param Data $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}