<?php

declare(strict_types=1);

namespace FireflyIII\JsonApi\V2\Accounts;

use FireflyIII\Models\Account;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

/**
 * Class AccountSchema
 *
 * This is the schema of all fields that an account exposes to the world.
 * Fields do not have to have a relation to the actual model.
 * Fields mentioned here still need to be filled in by the AccountResource.
 */
class AccountSchemaOld extends Schema
{
    /**
     * The model the schema corresponds to.
     */
    public static string $model = Account::class;

    /**
     * Get the resource fields.
     */
    public function fields(): array
    {
        return [
            ID::make(),
            DateTime::make('created_at')->sortable()->readOnly(),
            DateTime::make('updated_at')->sortable()->readOnly(),
            Str::make('name')->sortable(),
            //            Str::make('account_type'),
            //            Str::make('virtual_balance'),
            //            Str::make('iban'),
            //            Boolean::make('active'),
            //            Number::make('order'),
            HasOne::make('user')->readOnly(),
            // HasMany::make('account_balances'),
        ];
    }

    /**
     * Filters mentioned here can be used to filter the results.
     * TODO write down exactly how this works.
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
        ];
    }

    /**
     * Get the resource paginator.
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
