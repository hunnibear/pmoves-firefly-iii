<?php
return [
    'roles' => [
        '_ignore'           => [
            'name'     => '(ignore this column)',
            'mappable' => false,
        ],
        'bill-id'           => [
            'name'     => 'Bill ID (matching Firefly)',
            'mappable' => true,
        ],
        'bill-name'         => [
            'name'     => 'Bill name',
            'mappable' => true,
        ],
        'currency-id'       => [
            'name'     => 'Currency ID (matching Firefly)',
            'mappable' => true,
        ],
        'currency-name'     => [
            'name'     => 'Currency name (matching Firefly)',
            'mappable' => true,
        ],
        'currency-code'     => [
            'name'     => 'Currency code (ISO 4217)',
            'mappable' => true,
        ],
        'currency-symbol'   => [
            'name'     => 'Currency symbol (matching Firefly)',
            'mappable' => true,
        ],
        'description'       => [
            'name'     => 'Description',
            'mappable' => false,
        ],
        'date-transaction'  => [
            'name'     => 'Date',
            'mappable' => false,
        ],
        'date-rent'         => [
            'name'     => 'Rent calculation date',
            'mappable' => false,
        ],
        'budget-id'         => [
            'name'     => 'Budget ID (matching Firefly)',
            'mappable' => true,
        ],
        'budget-name'       => [
            'name'     => 'Budget name',
            'mappable' => true,
        ],
        'rabo-debet-credet' => [
            'name'     => 'Rabobank specific debet/credet indicator',
            'mappable' => false,
        ],
        'category-id'       => [
            'name'     => 'Category ID (matching Firefly)',
            'mappable' => true,
        ],
        'category-name'     => [
            'name'     => 'Category name',
            'mappable' => true,
        ],
        'tags-comma'        => [
            'name'     => 'Tags (comma separated)',
            'mappable' => true,
        ],
        'tags-space'        => [
            'name'     => 'Tags (space separated)',
            'mappable' => true,
        ],
        'account-id'        => [
            'name'     => 'Asset account ID (matching Firefly)',
            'mappable' => true,
        ],
        'account-name'      => [
            'name'     => 'Asset account name',
            'mappable' => true,
        ],
        'account-iban'      => [
            'name'     => 'Asset account IBAN',
            'mappable' => true,
        ],
        'opposing-id'       => [
            'name'     => 'Expense or revenue account ID (matching Firefly)',
            'mappable' => true,
        ],
        'opposing-name'     => [
            'name'     => 'Expense or revenue account name',
            'mappable' => true,
        ],
        'opposing-iban'     => [
            'name'     => 'Expense or revenue account IBAN',
            'mappable' => true,
        ],
        'amount'            => [
            'name'     => 'Amount',
            'mappable' => false,
        ],
        'sepa-ct-id'        => [
            'name'     => 'SEPA Credit Transfer end-to-end ID',
            'mappable' => false,
        ],
        'sepa-ct-op'        => [
            'name'     => 'SEPA Credit Transfer opposing account',
            'mappable' => false,
        ],
        'sepa-db'           => [
            'name'     => 'SEPA Direct Debet',
            'mappable' => false,
        ],
    ]
];