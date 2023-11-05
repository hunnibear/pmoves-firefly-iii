<?php

/*
 * UserGroup.php
 * Copyright (c) 2021 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Models;

use Carbon\Carbon;
use Eloquent;
use FireflyIII\Enums\UserRoleEnum;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
/**
 * Class UserGroup
 *
 * @property int                                        $id
 * @property Carbon|null                                $created_at
 * @property Carbon|null                                $updated_at
 * @property string|null                                $deleted_at
 * @property string                                     $title
 * @property-read Collection|GroupMembership[]          $groupMemberships
 * @property-read int|null                              $group_memberships_count
 * @method static Builder|UserGroup newModelQuery()
 * @method static Builder|UserGroup newQuery()
 * @method static Builder|UserGroup query()
 * @method static Builder|UserGroup whereCreatedAt($value)
 * @method static Builder|UserGroup whereDeletedAt($value)
 * @method static Builder|UserGroup whereId($value)
 * @method static Builder|UserGroup whereTitle($value)
 * @method static Builder|UserGroup whereUpdatedAt($value)
 * @property-read Collection<int, Account>              $accounts
 * @property-read int|null                              $accounts_count
 * @property-read Collection<int, AvailableBudget>      $availableBudgets
 * @property-read int|null                              $available_budgets_count
 * @property-read Collection<int, Bill>                 $bills
 * @property-read int|null                              $bills_count
 * @property-read Collection<int, Budget>               $budgets
 * @property-read int|null                              $budgets_count
 * @property-read Collection<int, PiggyBank>            $piggyBanks
 * @property-read int|null                              $piggy_banks_count
 * @property-read Collection<int, TransactionJournal>   $transactionJournals
 * @property-read int|null                              $transaction_journals_count
 * @property-read Collection<int, Attachment>           $attachments
 * @property-read int|null                              $attachments_count
 * @property-read Collection<int, Category>             $categories
 * @property-read int|null                              $categories_count
 * @property-read Collection<int, CurrencyExchangeRate> $currencyExchangeRates
 * @property-read int|null                              $currency_exchange_rates_count
 * @property-read Collection<int, ObjectGroup>          $objectGroups
 * @property-read int|null                              $object_groups_count
 * @property-read Collection<int, Recurrence>           $recurrences
 * @property-read int|null                              $recurrences_count
 * @property-read Collection<int, RuleGroup>            $ruleGroups
 * @property-read int|null                              $rule_groups_count
 * @property-read Collection<int, Rule>                 $rules
 * @property-read int|null                              $rules_count
 * @property-read Collection<int, Tag>                  $tags
 * @property-read int|null                              $tags_count
 * @property-read Collection<int, TransactionGroup>     $transactionGroups
 * @property-read int|null                              $transaction_groups_count
 * @property-read Collection<int, Webhook>              $webhooks
 * @property-read int|null                              $webhooks_count
 * @property-read Collection<int, TransactionCurrency>  $currencies
 * @property-read int|null                              $currencies_count
 * @mixin Eloquent
 */
class UserGroup extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['title'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @param string $value
     *
     * @return UserGroup
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $userGroupId = (int)$value;
            /** @var User $user */
            $user = auth()->user();
            /** @var UserGroup|null $userGroup */
            $userGroup = self::find($userGroupId);
            if (null === $userGroup) {
                throw new NotFoundHttpException();
            }
            // need at least ready only to be aware of the user group's existence,
            // but owner/full role (in the group) or global owner role may overrule this.
            if ($user->hasRoleInGroup($userGroup, UserRoleEnum::READ_ONLY, true, true)) {
                return $userGroup;
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * Link to accounts.
     *
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Link to attachments.
     *
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Link to bills.
     *
     * @return HasMany
     */
    public function availableBudgets(): HasMany
    {
        return $this->hasMany(AvailableBudget::class);
    }

    /**
     * Link to bills.
     *
     * @return HasMany
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Link to budgets.
     *
     * @return HasMany
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Link to categories.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Link to currencies
     *
     * @return BelongsToMany
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(TransactionCurrency::class)->withTimestamps()->withPivot('group_default');
    }

    /**
     * Link to exchange rates.
     *
     * @return HasMany
     */
    public function currencyExchangeRates(): HasMany
    {
        return $this->hasMany(CurrencyExchangeRate::class);
    }

    /**
     *
     * @return HasMany
     */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    /**
     * @return HasMany
     */
    public function objectGroups(): HasMany
    {
        return $this->hasMany(ObjectGroup::class);
    }

    /**
     * Link to piggy banks.
     *
     * @return HasManyThrough
     */
    public function piggyBanks(): HasManyThrough
    {
        return $this->hasManyThrough(PiggyBank::class, Account::class);
    }

    /**
     * @return HasMany
     */
    public function recurrences(): HasMany
    {
        return $this->hasMany(Recurrence::class);
    }

    /**
     * @return HasMany
     */
    public function ruleGroups(): HasMany
    {
        return $this->hasMany(RuleGroup::class);
    }

    /**
     * @return HasMany
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    /**
     * @return HasMany
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return HasMany
     */
    public function transactionGroups(): HasMany
    {
        return $this->hasMany(TransactionGroup::class);
    }

    /**
     * Link to transaction journals.
     *
     * @return HasMany
     */
    public function transactionJournals(): HasMany
    {
        return $this->hasMany(TransactionJournal::class);
    }

    /**
     * @return HasMany
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }
}
