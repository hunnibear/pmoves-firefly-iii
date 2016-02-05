<?php
declare(strict_types = 1);

namespace FireflyIII\Support;

use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionCurrency;
use FireflyIII\Models\TransactionJournal;
use Illuminate\Support\Collection;
use NumberFormatter;
use Preferences as Prefs;

/**
 * Class Amount
 *
 * @package FireflyIII\Support
 */
class Amount
{

    /**
     * @param string $amount
     * @param bool   $coloured
     *
     * @return string
     */
    public function format(string $amount, bool $coloured = true)
    {
        return $this->formatAnything($this->getDefaultCurrency(), $amount, $coloured);
    }

    /**
     * This method will properly format the given number, in color or "black and white",
     * as a currency, given two things: the currency required and the current locale.
     *
     * @param TransactionCurrency $format
     * @param string              $amount
     * @param bool                $coloured
     *
     * @return string
     */
    public function formatAnything(TransactionCurrency $format, string $amount, bool $coloured = true)
    {
        $locale    = setlocale(LC_MONETARY, 0);
        $float     = floatval($amount);
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $result    = $formatter->formatCurrency($float, $format->code);

        if ($coloured === true) {
            if ($amount == 0) {
                return '<span style="color:#999">' . $result . '</span>';
            }
            if ($amount > 0) {
                return '<span class="text-success">' . $result . '</span>';
            }

            return '<span class="text-danger">' . $result . '</span>';

        }

        return $result;
    }

    /**
     *
     * @param TransactionJournal $journal
     * @param bool               $coloured
     *
     * @return string
     */
    public function formatJournal(TransactionJournal $journal, bool $coloured = true)
    {
        $cache = new CacheProperties;
        $cache->addProperty($journal->id);
        $cache->addProperty('formatJournal');

        if ($cache->has()) {
            return $cache->get(); // @codeCoverageIgnore
        }

        if ($journal->isTransfer() && $coloured) {
            $txt = '<span class="text-info">' . $this->formatAnything($journal->transactionCurrency, $journal->amount_positive, false) . '</span>';
            $cache->store($txt);

            return $txt;
        }
        if ($journal->isTransfer() && !$coloured) {
            $txt = $this->formatAnything($journal->transactionCurrency, $journal->amount_positive, false);
            $cache->store($txt);

            return $txt;
        }

        $txt = $this->formatAnything($journal->transactionCurrency, $journal->amount, $coloured);
        $cache->store($txt);

        return $txt;
    }

    /**
     * @param Transaction $transaction
     * @param bool        $coloured
     *
     * @return string
     */
    public function formatTransaction(Transaction $transaction, bool $coloured = true)
    {
        $currency = $transaction->transactionJournal->transactionCurrency;

        return $this->formatAnything($currency, $transaction->amount, $coloured);
    }

    /**
     * @param string $symbol
     * @param float  $amount
     * @param bool   $coloured
     *
     * @return string
     */
    public function formatWithSymbol(string $symbol, string $amount, $coloured = true)
    {
        return $this->formatAnything($this->getDefaultCurrency(), $amount, $coloured);
    }

    /**
     * @return Collection
     */
    public function getAllCurrencies()
    {
        return TransactionCurrency::orderBy('code', 'ASC')->get();
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {

        $cache = new CacheProperties;
        $cache->addProperty('getCurrencyCode');
        if ($cache->has()) {
            return $cache->get();
        } else {
            $currencyPreference = Prefs::get('currencyPreference', env('DEFAULT_CURRENCY', 'EUR'));

            $currency = TransactionCurrency::whereCode($currencyPreference->data)->first();
            if ($currency) {

                $cache->store($currency->code);

                return $currency->code;
            }
            $cache->store(env('DEFAULT_CURRENCY', 'EUR'));

            return env('DEFAULT_CURRENCY', 'EUR'); // @codeCoverageIgnore
        }
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        $cache = new CacheProperties;
        $cache->addProperty('getCurrencySymbol');
        if ($cache->has()) {
            return $cache->get();
        } else {
            $currencyPreference = Prefs::get('currencyPreference', env('DEFAULT_CURRENCY', 'EUR'));
            $currency           = TransactionCurrency::whereCode($currencyPreference->data)->first();

            $cache->store($currency->symbol);

            return $currency->symbol;
        }
    }

    /**
     * @return TransactionCurrency
     */
    public function getDefaultCurrency()
    {
        $cache = new CacheProperties;
        $cache->addProperty('getDefaultCurrency');
        if ($cache->has()) {
            return $cache->get();
        }
        $currencyPreference = Prefs::get('currencyPreference', 'EUR');
        $currency           = TransactionCurrency::whereCode($currencyPreference->data)->first();
        $cache->store($currency);

        return $currency;
    }
}
