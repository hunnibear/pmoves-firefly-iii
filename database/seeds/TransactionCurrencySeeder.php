<?php
declare(strict_types = 1);

use FireflyIII\Models\TransactionCurrency;
use Illuminate\Database\Seeder;

/**
 * Class TransactionCurrencySeeder
 */
class TransactionCurrencySeeder extends Seeder
{
    public function run()
    {
        DB::table('transaction_currencies')->delete();

        TransactionCurrency::create(['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']);
        TransactionCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
        TransactionCurrency::create(['code' => 'HUF', 'name' => 'Hungarian forint', 'symbol' => 'Ft']);
        TransactionCurrency::create(['code' => 'BRL', 'name' => 'Real', 'symbol' => 'R$']);
        TransactionCurrency::create(['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£']);
    }

} 
