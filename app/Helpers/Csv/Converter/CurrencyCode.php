<?php

namespace FireflyIII\Helpers\Csv\Converter;

use FireflyIII\Models\TransactionCurrency;

/**
 * Class CurrencyCode
 *
 * @package FireflyIII\Helpers\Csv\Converter
 */
class CurrencyCode extends BasicConverter implements ConverterInterface
{

    /**
     * @return mixed|static
     */
    public function convert()
    {
        if (isset($this->mapped[$this->index][$this->value])) {
            $currency = TransactionCurrency::find($this->mapped[$this->index][$this->value]);
        } else {
            $currency = TransactionCurrency::whereCode($this->value)->first();
        }

        return $currency;
    }
}