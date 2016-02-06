<?php
declare(strict_types = 1);

namespace FireflyIII\Support\Search;

use Illuminate\Support\Collection;

/**
 * Interface SearchInterface
 *
 * @package FireflyIII\Support\Search
 */
interface SearchInterface
{
    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchAccounts(array $words): Collection;

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchBudgets(array $words): Collection;

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchCategories(array $words): Collection;

    /**
     *
     * @param array $words
     *
     * @return Collection
     */
    public function searchTags(array $words): Collection;

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchTransactions(array $words): Collection;
}
