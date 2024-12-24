<?php

/**
 * NoCategoryRepository.php
 * Copyright (c) 2019 james@firefly-iii.org
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

namespace FireflyIII\Repositories\Category;

use Carbon\Carbon;
use FireflyIII\Helpers\Collector\GroupCollectorInterface;
use FireflyIII\Models\TransactionType;
use FireflyIII\Support\Facades\Amount;
use FireflyIII\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class NoCategoryRepository
 */
class NoCategoryRepository implements NoCategoryRepositoryInterface
{
    private User $user;

    /**
     * This method returns a list of all the withdrawal transaction journals (as arrays) set in that period
     * which have no category set to them. It's grouped per currency, with as few details in the array
     * as possible. Amounts are always negative.
     */
    public function listExpenses(Carbon $start, Carbon $end, ?Collection $accounts = null): array
    {
        /** @var GroupCollectorInterface $collector */
        $collector = app(GroupCollectorInterface::class);
        $collector->setUser($this->user)->setRange($start, $end)->setTypes([TransactionType::WITHDRAWAL])->withoutCategory();
        if (null !== $accounts && $accounts->count() > 0) {
            $collector->setAccounts($accounts);
        }
        $journals  = $collector->getExtractedJournals();
        $array     = [];

        foreach ($journals as $journal) {
            $currencyId = (int) $journal['currency_id'];
            $array[$currencyId]                  ??= [
                'categories'              => [],
                'currency_id'             => $currencyId,
                'currency_name'           => $journal['currency_name'],
                'currency_symbol'         => $journal['currency_symbol'],
                'currency_code'           => $journal['currency_code'],
                'currency_decimal_places' => $journal['currency_decimal_places'],
            ];
            // info about the non-existent category:
            $array[$currencyId]['categories'][0] ??= [
                'id'                   => 0,
                'name'                 => (string) trans('firefly.noCategory'),
                'transaction_journals' => [],
            ];

            // add journal to array:
            // only a subset of the fields.
            $journalId  = (int) $journal['transaction_journal_id'];
            $array[$currencyId]['categories'][0]['transaction_journals'][$journalId]
                        = [
                            'amount' => app('steam')->negative($journal['amount']),
                            'date'   => $journal['date'],
                        ];
        }

        return $array;
    }

    public function setUser(null|Authenticatable|User $user): void
    {
        if ($user instanceof User) {
            $this->user = $user;
        }
    }

    /**
     * This method returns a list of all the deposit transaction journals (as arrays) set in that period
     * which have no category set to them. It's grouped per currency, with as few details in the array
     * as possible. Amounts are always positive.
     */
    public function listIncome(Carbon $start, Carbon $end, ?Collection $accounts = null): array
    {
        /** @var GroupCollectorInterface $collector */
        $collector = app(GroupCollectorInterface::class);
        $collector->setUser($this->user)->setRange($start, $end)->setTypes([TransactionType::DEPOSIT])->withoutCategory();
        if (null !== $accounts && $accounts->count() > 0) {
            $collector->setAccounts($accounts);
        }
        $journals  = $collector->getExtractedJournals();
        $array     = [];

        foreach ($journals as $journal) {
            $currencyId = (int) $journal['currency_id'];
            $array[$currencyId]                  ??= [
                'categories'              => [],
                'currency_id'             => $currencyId,
                'currency_name'           => $journal['currency_name'],
                'currency_symbol'         => $journal['currency_symbol'],
                'currency_code'           => $journal['currency_code'],
                'currency_decimal_places' => $journal['currency_decimal_places'],
            ];

            // info about the non-existent category:
            $array[$currencyId]['categories'][0] ??= [
                'id'                   => 0,
                'name'                 => (string) trans('firefly.noCategory'),
                'transaction_journals' => [],
            ];
            // add journal to array:
            // only a subset of the fields.
            $journalId  = (int) $journal['transaction_journal_id'];
            $array[$currencyId]['categories'][0]['transaction_journals'][$journalId]
                        = [
                            'amount' => app('steam')->positive($journal['amount']),
                            'date'   => $journal['date'],
                        ];
        }

        return $array;
    }

    /**
     * Sum of withdrawal journals in period without a category, grouped per currency. Amounts are always negative.
     */
    public function sumExpenses(Carbon $start, Carbon $end, ?Collection $accounts = null): array
    {
        /** @var GroupCollectorInterface $collector */
        $collector = app(GroupCollectorInterface::class);
        $collector->setUser($this->user)->setRange($start, $end)->setTypes([TransactionType::WITHDRAWAL])->withoutCategory();

        if (null !== $accounts && $accounts->count() > 0) {
            $collector->setAccounts($accounts);
        }
        $journals  = $collector->getExtractedJournals();
        $array     = [];
        // default currency information for native stuff.
        $convertToNative = app('preferences')->get('convert_to_native', false)->data;
        $default         = app('amount')->getDefaultCurrency();

        foreach ($journals as $journal) {
            // Almost the same as in \FireflyIII\Repositories\Budget\OperationsRepository::sumExpenses
            $amount                = '0';
            $currencyId            = (int) $journal['currency_id'];
            $currencyName          = $journal['currency_name'];
            $currencySymbol        = $journal['currency_symbol'];
            $currencyCode          = $journal['currency_code'];
            $currencyDecimalPlaces = $journal['currency_decimal_places'];
            if ($convertToNative) {
                $useNative = $default->id !== (int) $journal['currency_id'];
                $amount    = Amount::getAmountFromJournal($journal);
                if ($useNative) {
                    $currencyId            = $default->id;
                    $currencyName          = $default->name;
                    $currencySymbol        = $default->symbol;
                    $currencyCode          = $default->code;
                    $currencyDecimalPlaces = $default->decimal_places;
                }
                Log::debug(sprintf('[a] Add amount %s %s', $currencyCode, $amount));
            }
            if (!$convertToNative) {
                // ignore the amount in foreign currency.
                Log::debug(sprintf('[b] Add amount %s %s', $currencyCode, $journal['amount']));
                $amount = $journal['amount'];
            }


            $array[$currencyId]        ??= [
                'sum'                     => '0',
                'currency_id'             => (string) $currencyId,
                'currency_name'           => $currencyName,
                'currency_symbol'         => $currencySymbol,
                'currency_code'           => $currencyCode,
                'currency_decimal_places' => $currencyDecimalPlaces,
            ];
            $array[$currencyId]['sum'] = bcadd($array[$currencyId]['sum'], app('steam')->negative($amount));
        }

        return $array;
    }

    /**
     * Sum of income journals in period without a category, grouped per currency. Amounts are always positive.
     */
    public function sumIncome(Carbon $start, Carbon $end, ?Collection $accounts = null): array
    {
        /** @var GroupCollectorInterface $collector */
        $collector = app(GroupCollectorInterface::class);
        $collector->setUser($this->user)->setRange($start, $end)->setTypes([TransactionType::DEPOSIT])->withoutCategory();

        if (null !== $accounts && $accounts->count() > 0) {
            $collector->setAccounts($accounts);
        }
        $journals  = $collector->getExtractedJournals();
        $array     = [];

        foreach ($journals as $journal) {
            $currencyId                = (int) $journal['currency_id'];
            $array[$currencyId] ??= [
                'sum'                     => '0',
                'currency_id'             => $currencyId,
                'currency_name'           => $journal['currency_name'],
                'currency_symbol'         => $journal['currency_symbol'],
                'currency_code'           => $journal['currency_code'],
                'currency_decimal_places' => $journal['currency_decimal_places'],
            ];
            $array[$currencyId]['sum'] = bcadd($array[$currencyId]['sum'], app('steam')->positive($journal['amount']));
        }

        return $array;
    }

    public function sumTransfers(Carbon $start, Carbon $end, ?Collection $accounts = null): array
    {
        /** @var GroupCollectorInterface $collector */
        $collector = app(GroupCollectorInterface::class);
        $collector->setUser($this->user)->setRange($start, $end)->setTypes([TransactionType::TRANSFER])->withoutCategory();

        if (null !== $accounts && $accounts->count() > 0) {
            $collector->setAccounts($accounts);
        }
        $journals  = $collector->getExtractedJournals();
        $array     = [];

        foreach ($journals as $journal) {
            $currencyId                = (int) $journal['currency_id'];
            $array[$currencyId] ??= [
                'sum'                     => '0',
                'currency_id'             => $currencyId,
                'currency_name'           => $journal['currency_name'],
                'currency_symbol'         => $journal['currency_symbol'],
                'currency_code'           => $journal['currency_code'],
                'currency_decimal_places' => $journal['currency_decimal_places'],
            ];
            $array[$currencyId]['sum'] = bcadd($array[$currencyId]['sum'], app('steam')->positive($journal['amount']));
        }

        return $array;
    }
}
