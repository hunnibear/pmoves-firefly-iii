<?php

namespace FireflyIII\Repositories\Category;

use Auth;
use Carbon\Carbon;
use Crypt;
use FireflyIII\Models\Category;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Models\TransactionType;
use FireflyIII\Repositories\Shared\ComponentRepository;
use FireflyIII\Support\CacheProperties;
use Illuminate\Support\Collection;

/**
 * Class CategoryRepository
 *
 * @package FireflyIII\Repositories\Category
 */
class CategoryRepository extends ComponentRepository implements CategoryRepositoryInterface
{

    /**
     * @param Category $category
     *
     * @return int
     */
    public function countJournals(Category $category)
    {
        return $category->transactionJournals()->count();

    }

    /**
     * @param Category $category
     *
     * @return boolean
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return true;
    }

    /**
     * @return Collection
     */
    public function getCategories()
    {
        /** @var Collection $set */
        $set = Auth::user()->categories()->orderBy('name', 'ASC')->get();
        $set = $set->sortBy(
            function (Category $category) {
                return strtolower($category->name);
            }
        );

        return $set;
    }

    /**
     * Returns the amount earned without category by accounts in period.
     *
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return string
     */
    public function earnedNoCategoryForAccounts(Collection $accounts, Carbon $start, Carbon $end)
    {

        $accountIds = [];
        foreach ($accounts as $account) {
            $accountIds[] = $account->id;
        }

        // is deposit AND account_from is in the list of $accounts
        // not from any of the accounts in the list?

        return Auth::user()
                   ->transactionjournals()
                   ->leftJoin('category_transaction_journal', 'category_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                   ->whereNull('category_transaction_journal.id')
                   ->before($end)
                   ->after($start)
                   ->leftJoin('transactions', 'transactions.transaction_journal_id', '=', 'transaction_journals.id')
                   ->whereIn('transactions.account_id', $accountIds)
                   ->transactionTypes([TransactionType::DEPOSIT])
                   ->get(['transaction_journals.*'])->sum('amount');
    }


    /**
     * Returns the amount spent without category by accounts in period.
     *
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return string
     */
    public function spentNoCategoryForAccounts(Collection $accounts, Carbon $start, Carbon $end)
    {

        $accountIds = [];
        foreach ($accounts as $account) {
            $accountIds[] = $account->id;
        }

        // is withdrawal or transfer AND account_from is in the list of $accounts


        return Auth::user()
                   ->transactionjournals()
                   ->leftJoin('category_transaction_journal', 'category_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                   ->whereNull('category_transaction_journal.id')
                   ->before($end)
                   ->after($start)
                   ->leftJoin('transactions', 'transactions.transaction_journal_id', '=', 'transaction_journals.id')
                   ->whereIn('transactions.account_id', $accountIds)
                   ->transactionTypes([TransactionType::WITHDRAWAL])
                   ->get(['transaction_journals.*'])->sum('amount');
    }


    /**
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return array
     */
    public function getCategoriesAndExpenses(Carbon $start, Carbon $end)
    {
        $set = Auth::user()->transactionjournals()
                   ->leftJoin(
                       'category_transaction_journal', 'category_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id'
                   )
                   ->leftJoin('categories', 'categories.id', '=', 'category_transaction_journal.category_id')
                   ->before($end)
                   ->where('categories.user_id', Auth::user()->id)
                   ->after($start)
                   ->transactionTypes([TransactionType::WITHDRAWAL])
                   ->get(['categories.id as category_id', 'categories.encrypted as category_encrypted', 'categories.name', 'transaction_journals.*']);

        bcscale(2);
        $result = [];
        foreach ($set as $entry) {
            $categoryId = intval($entry->category_id);
            if (isset($result[$categoryId])) {
                $result[$categoryId]['sum'] = bcadd($result[$categoryId]['sum'], $entry->amount);
            } else {
                $isEncrypted         = intval($entry->category_encrypted) === 1 ? true : false;
                $name                = strlen($entry->name) === 0 ? trans('firefly.no_category') : $entry->name;
                $name                = $isEncrypted ? Crypt::decrypt($name) : $name;
                $result[$categoryId] = [
                    'name' => $name,
                    'sum'  => $entry->amount,
                ];

            }
        }

        return $result;
    }

    /**
     * @param Category $category
     *
     * @return Carbon
     */
    public function getFirstActivityDate(Category $category)
    {
        /** @var TransactionJournal $first */
        $first = $category->transactionjournals()->orderBy('date', 'ASC')->first();
        if ($first) {
            return $first->date;
        }

        return new Carbon;

    }

    /**
     * @param Category $category
     * @param int      $page
     *
     * @return Collection
     */
    public function getJournals(Category $category, $page)
    {
        $offset = $page > 0 ? $page * 50 : 0;

        return $category->transactionJournals()->withRelevantData()->take(50)->offset($offset)
                        ->orderBy('transaction_journals.date', 'DESC')
                        ->orderBy('transaction_journals.order', 'ASC')
                        ->orderBy('transaction_journals.id', 'DESC')
                        ->get(
                            ['transaction_journals.*']
                        );

    }

    /**
     * @param Category $category
     *
     * @return Carbon|null
     */
    public function getLatestActivity(Category $category)
    {
        $latest = $category->transactionjournals()
                           ->orderBy('transaction_journals.date', 'DESC')
                           ->orderBy('transaction_journals.order', 'ASC')
                           ->orderBy('transaction_journals.id', 'DESC')
                           ->first();
        if ($latest) {
            return $latest->date;
        }

        return null;
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     */
    public function getWithoutCategory(Carbon $start, Carbon $end)
    {
        return Auth::user()
                   ->transactionjournals()
                   ->leftJoin('category_transaction_journal', 'category_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                   ->whereNull('category_transaction_journal.id')
                   ->before($end)
                   ->after($start)
                   ->orderBy('transaction_journals.date', 'DESC')
                   ->orderBy('transaction_journals.order', 'ASC')
                   ->orderBy('transaction_journals.id', 'DESC')
                   ->get(['transaction_journals.*']);
    }

    /**
     * @param Category $category
     * @param Carbon   $start
     * @param Carbon   $end
     *
     * @param bool     $shared
     *
     * @return string
     */
    public function balanceInPeriod(Category $category, Carbon $start, Carbon $end, $shared = false)
    {
        return $this->commonBalanceInPeriod($category, $start, $end, $shared);
    }

    /**
     * @param Category   $category
     * @param Carbon     $start
     * @param Carbon     $end
     * @param Collection $accounts
     *
     * @return string
     */
    public function balanceInPeriodForList(Category $category, Carbon $start, Carbon $end, Collection $accounts)
    {
        return $this->commonBalanceInPeriodForList($category, $start, $end, $accounts);
    }

    /**
     * Corrected for tags
     *
     * @param Category $category
     * @param Carbon   $date
     *
     * @return string
     */
    public function spentOnDaySum(Category $category, Carbon $date)
    {
        return $category->transactionjournals()->transactionTypes([TransactionType::WITHDRAWAL])->onDate($date)->get(['transaction_journals.*'])->sum('amount');
    }

    /**
     * @param array $data
     *
     * @return Category
     */
    public function store(array $data)
    {
        $newCategory = new Category(
            [
                'user_id' => $data['user'],
                'name'    => $data['name'],
            ]
        );
        $newCategory->save();

        return $newCategory;
    }

    /**
     * @param Category $category
     * @param array    $data
     *
     * @return Category
     */
    public function update(Category $category, array $data)
    {
        // update the account:
        $category->name = $data['name'];
        $category->save();

        return $category;
    }

    /**
     * This method returns the sum of the journals in the category, optionally
     * limited by a start or end date.
     *
     * @param Category $category
     * @param Carbon   $start
     * @param Carbon   $end
     *
     * @return string
     */
    public function journalsSum(Category $category, Carbon $start = null, Carbon $end = null)
    {
        $query = $category->transactionJournals()
                          ->orderBy('transaction_journals.date', 'DESC')
                          ->orderBy('transaction_journals.order', 'ASC')
                          ->orderBy('transaction_journals.id', 'DESC');
        if (!is_null($start)) {
            $query->after($start);
        }

        if (!is_null($end)) {
            $query->before($end);
        }

        return $query->get(['transaction_journals.*'])->sum('amount');

    }

    /**
     * @param Category       $category
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @param bool           $shared
     *
     * @return string
     */
    public function spentInPeriod(Category $category, Carbon $start, Carbon $end)
    {
        $cache = new CacheProperties; // we must cache this.
        $cache->addProperty($category->id);
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('spentInPeriod');

        if ($cache->has()) {
            return $cache->get(); // @codeCoverageIgnore
        }

        $sum = $category->transactionjournals()->transactionTypes([TransactionType::WITHDRAWAL])->before($end)->after($start)->get(['transaction_journals.*'])
                        ->sum(
                            'amount'
                        );

        $cache->store($sum);

        return $sum;
    }

    /**
     * @param Category       $category
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @param bool           $shared
     *
     * @return string
     */
    public function earnedInPeriod(Category $category, Carbon $start, Carbon $end)
    {
        $cache = new CacheProperties; // we must cache this.
        $cache->addProperty($category->id);
        $cache->addProperty($start);
        $cache->addProperty($end);
        $cache->addProperty('earnedInPeriod');

        if ($cache->has()) {
            return $cache->get(); // @codeCoverageIgnore
        }

        $sum = $category->transactionjournals()->transactionTypes([TransactionType::DEPOSIT])->before($end)->after($start)->get(['transaction_journals.*'])
                        ->sum(
                            'amount'
                        );

        $cache->store($sum);

        return $sum;
    }

    /**
     * @param Category $category
     * @param int      $page
     *
     * @return Collection
     */
    public function getJournalsInRange(Category $category, $page, Carbon $start, Carbon $end)
    {
        $offset = $page > 0 ? $page * 50 : 0;

        return $category->transactionJournals()
                        ->after($start)
                        ->before($end)
                        ->withRelevantData()->take(50)->offset($offset)
                        ->orderBy('transaction_journals.date', 'DESC')
                        ->orderBy('transaction_journals.order', 'ASC')
                        ->orderBy('transaction_journals.id', 'DESC')
                        ->get(
                            ['transaction_journals.*']
                        );
    }

    /**
     * @param Category $category
     *
     * @return int
     */
    public function countJournalsInRange(Category $category, Carbon $start, Carbon $end)
    {
        return $category->transactionJournals()->before($end)->after($start)->count();
    }

    /**
     *
     * Corrected for tags.
     *
     * @param Category $category
     * @param Carbon   $date
     *
     * @return float
     */
    public function earnedOnDaySum(Category $category, Carbon $date)
    {
        return $category->transactionjournals()->transactionTypes([TransactionType::DEPOSIT])->onDate($date)->get(['transaction_journals.*'])->sum('amount');
    }

    /**
     * Calculates how much is spent in this period.
     *
     * @param Category   $category
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return string
     */
    public function spentInPeriodForAccounts(Category $category, Collection $accounts, Carbon $start, Carbon $end)
    {
        $accountIds = [];
        foreach ($accounts as $account) {
            $accountIds[] = $account->id;
        }

        $sum
            = $category
            ->transactionjournals()
            ->leftJoin('transactions', 'transactions.transaction_journal_id', '=', 'transaction_journals.id')
            ->after($start)
            ->before($end)
            ->whereIn('transactions.account_id', $accountIds)
            ->transactionTypes([TransactionType::WITHDRAWAL])
            ->get(['transaction_journals.*'])
            ->sum('amount');
        return $sum;

    }

    /**
     * Calculate how much is earned in this period.
     *
     * @param Category   $category
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return string
     */
    public function earnedInPeriodForAccounts(Category $category, Collection $accounts, Carbon $start, Carbon $end)
    {
        $accountIds = [];
        foreach ($accounts as $account) {
            $accountIds[] = $account->id;
        }
        $sum
            = $category
            ->transactionjournals()
            ->leftJoin('transactions', 'transactions.transaction_journal_id', '=', 'transaction_journals.id')
            ->before($end)
            ->whereIn('transactions.account_id', $accountIds)
            ->transactionTypes([TransactionType::DEPOSIT])
            ->after($start)
            ->get(['transaction_journals.*'])
            ->sum('amount');
        return $sum;

    }
}
