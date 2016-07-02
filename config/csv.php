<?php
declare(strict_types = 1);


return [

    /*
     * Configuration for the CSV specifics.
     */
    'import_specifics' => [
        'RabobankDescription' => 'FireflyIII\Import\Specifics\RabobankDescription',
        'AbnAmroDescription'  => 'FireflyIII\Import\Specifics\AbnAmroDescription',
    ],

    /*
     * Configuration for possible column roles.
     */
    'import_roles' => [
        '_ignore'           => [
            'mappable'  => false,
            'converter' => 'Ignore',
            'field'     => 'ignored',
        ],
        'bill-id'           => [
            'mappable'  => false,
            'field'     => 'bill',
            'converter' => 'BillId',
            'mapper'    => 'Bills',
        ],
        'bill-name'         => [
            'mappable'  => true,
            'converter' => 'BillName',
            'field'     => 'bill',
            'mapper'    => 'Bills',
        ],
        'currency-id'       => [
            'mappable'  => true,
            'converter' => 'CurrencyId',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrencies'
        ],
        'currency-name'     => [
            'mappable'  => true,
            'converter' => 'CurrencyName',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrencies'
        ],
        'currency-code'     => [
            'mappable'  => true,
            'converter' => 'CurrencyCode',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrencies'
        ],
        'currency-symbol'   => [
            'mappable'  => true,
            'converter' => 'CurrencySymbol',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrencies'
        ],
        'description'       => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
        'date-transaction'  => [
            'mappable'  => false,
            'converter' => 'Date',
            'field'     => 'date',
        ],
        'date-rent'         => [
            'mappable'  => false,
            'converter' => 'Date',
            'field'     => 'date-rent',
        ],
        'budget-id'         => [
            'mappable'  => true,
            'converter' => 'BudgetId',
            'field'     => 'budget',
            'mapper'    => 'Budgets',
        ],
        'budget-name'       => [
            'mappable'  => true,
            'converter' => 'BudgetName',
            'field'     => 'budget',
            'mapper'    => 'Budgets',
        ],
        'rabo-debet-credit' => [
            'mappable'  => false,
            'converter' => 'RabobankDebetCredit',
            'field'     => 'amount-modifier',
        ],
        'ing-debet-credit' => [
            'mappable'  => false,
            'converter' => 'INGDebetCredit',
            'field'     => 'amount-modifier',
        ],
        'category-id'       => [
            'mappable'  => true,
            'converter' => 'CategoryId',
            'field'     => 'category',
            'mapper'    => 'Categories',
        ],
        'category-name'     => [
            'mappable'  => true,
            'converter' => 'CategoryName',
            'field'     => 'category',
            'mapper'    => 'Categories',
        ],
        'tags-comma'        => [
            'mappable'  => true,
            'field'     => 'tags',
            'converter' => 'TagsComma',
            'mapper'    => 'Tags',
        ],
        'tags-space'        => [
            'mappable'  => true,
            'field'     => 'tags',
            'converter' => 'TagsSpace',
            'mapper'    => 'Tags',
        ],
        'account-id'        => [
            'mappable'  => true,
            'mapper'    => 'AssetAccountId',
            'field'     => 'asset-account-id',
            'converter' => 'AssetAccounts'
        ],
        'account-name'      => [
            'mappable'  => true,
            'mapper'    => 'AssetAccountName',
            'field'     => 'asset-account-name',
            'converter' => 'AssetAccounts'
        ],
        'account-iban'      => [
            'mappable'  => true,
            'converter' => 'AssetAccountIban',
            'field'     => 'asset-account-iban',
            'mapper'    => 'AssetAccounts'
        ],
        'account-number'      => [
            'mappable'  => true,
            'converter' => 'AssetAccountNumber',
            'field'     => 'asset-account-number',
            'mapper'    => 'AssetAccounts'
        ],
        'opposing-id'       => [
            'mappable'  => true,
            'field'     => 'opposing-account-id',
            'converter' => 'OpposingAccountId',
            'mapper'    => 'OpposingAccounts',
        ],
        'opposing-name'     => [
            'mappable'  => true,
            'field'     => 'opposing-account-name',
            'converter' => 'OpposingAccountName',
            'mapper'    => 'OpposingAccounts',
        ],
        'opposing-iban'     => [
            'mappable'  => true,
            'field'     => 'opposing-account-iban',
            'converter' => 'OpposingAccountIban',
            'mapper'    => 'OpposingAccounts',
        ],
        'opposing-number'     => [
            'mappable'  => true,
            'field'     => 'opposing-account-number',
            'converter' => 'OpposingAccountNumber',
            'mapper'    => 'OpposingAccounts',
        ],
        'amount'            => [
            'mappable'  => false,
            'converter' => 'Amount',
            'field'     => 'amount',
        ],
//        'amount-comma-separated' => [
//            'mappable'  => false,
//            'converter' => 'AmountComma',
//            'field'     => 'amount',
//        ],
        'sepa-ct-id'        => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
        'sepa-ct-op'        => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
        'sepa-db'           => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
    ],








    /*


    'specifix'        => [
        'RabobankDescription',
        'AbnAmroDescription',
        'Dummy'
    ],
    'post_processors' => [
        'Description',
        'Amount',
        'Currency',
        'Bill',
        'OpposingAccount', // must be after Amount!
        'AssetAccount',

    ],
    'roles'           => [
        '_ignore'           => [
            'mappable'  => false,
            'converter' => 'Ignore',
            'field'     => 'ignored',
        ],
        'bill-id'           => [
            'mappable'  => false,
            'field'     => 'bill',
            'converter' => 'BillId',
            'mapper'    => 'Bill',
        ],
        'bill-name'         => [
            'mappable'  => true,
            'converter' => 'BillName',
            'field'     => 'bill',
            'mapper'    => 'Bill',
        ],
        'currency-id'       => [
            'mappable'  => true,
            'converter' => 'CurrencyId',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrency'
        ],
        'currency-name'     => [
            'mappable'  => true,
            'converter' => 'CurrencyName',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrency'
        ],
        'currency-code'     => [
            'mappable'  => true,
            'converter' => 'CurrencyCode',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrency'
        ],
        'currency-symbol'   => [
            'mappable'  => true,
            'converter' => 'CurrencySymbol',
            'field'     => 'currency',
            'mapper'    => 'TransactionCurrency'
        ],
        'description'       => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
        'date-transaction'  => [
            'mappable'  => false,
            'converter' => 'Date',
            'field'     => 'date',
        ],
        'date-rent'         => [
            'mappable'  => false,
            'converter' => 'Date',
            'field'     => 'date-rent',
        ],
        'budget-id'         => [
            'mappable'  => true,
            'converter' => 'BudgetId',
            'field'     => 'budget',
            'mapper'    => 'Budget',
        ],
        'budget-name'       => [
            'mappable'  => true,
            'converter' => 'BudgetName',
            'field'     => 'budget',
            'mapper'    => 'Budget',
        ],
        'rabo-debet-credit' => [
            'mappable'  => false,
            'converter' => 'RabobankDebetCredit',
            'field'     => 'amount-modifier',
        ],
        'ing-debet-credit' => [
            'mappable'  => false,
            'converter' => 'INGDebetCredit',
            'field'     => 'amount-modifier',
        ],
        'category-id'       => [
            'mappable'  => true,
            'converter' => 'CategoryId',
            'field'     => 'category',
            'mapper'    => 'Category',
        ],
        'category-name'     => [
            'mappable'  => true,
            'converter' => 'CategoryName',
            'field'     => 'category',
            'mapper'    => 'Category',
        ],
        'tags-comma'        => [
            'mappable'  => true,
            'field'     => 'tags',
            'converter' => 'TagsComma',
            'mapper'    => 'Tag',
        ],
        'tags-space'        => [
            'mappable'  => true,
            'field'     => 'tags',
            'converter' => 'TagsSpace',
            'mapper'    => 'Tag',
        ],
        'account-id'        => [
            'mappable'  => true,
            'mapper'    => 'AssetAccount',
            'field'     => 'asset-account-id',
            'converter' => 'AccountId'
        ],
        'account-name'      => [
            'mappable'  => true,
            'mapper'    => 'AssetAccount',
            'field'     => 'asset-account-name',
            'converter' => 'AssetAccountName'
        ],
        'account-iban'      => [
            'mappable'  => true,
            'converter' => 'AssetAccountIban',
            'field'     => 'asset-account-iban',
            'mapper'    => 'AssetAccount'
        ],
        'account-number'      => [
            'mappable'  => true,
            'converter' => 'AssetAccountNumber',
            'field'     => 'asset-account-number',
            'mapper'    => 'AssetAccount'
        ],
        'opposing-id'       => [
            'mappable'  => true,
            'field'     => 'opposing-account-id',
            'converter' => 'OpposingAccountId',
            'mapper'    => 'AnyAccount',
        ],
        'opposing-name'     => [
            'mappable'  => true,
            'field'     => 'opposing-account-name',
            'converter' => 'OpposingAccountName',
            'mapper'    => 'AnyAccount',
        ],
        'opposing-iban'     => [
            'mappable'  => true,
            'field'     => 'opposing-account-iban',
            'converter' => 'OpposingAccountIban',
            'mapper'    => 'AnyAccount',
        ],
        'opposing-number'     => [
            'mappable'  => true,
            'field'     => 'opposing-account-number',
            'converter' => 'OpposingAccountNumber',
            'mapper'    => 'AnyAccount',
        ],
        'amount'            => [
            'mappable'  => false,
            'converter' => 'Amount',
            'field'     => 'amount',
        ],
        'amount-comma-separated' => [
            'mappable'  => false,
            'converter' => 'AmountComma',
            'field'     => 'amount',
        ],
        'sepa-ct-id'        => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
        'sepa-ct-op'        => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
        'sepa-db'           => [
            'mappable'  => false,
            'converter' => 'Description',
            'field'     => 'description',
        ],
    ]


    */
];
