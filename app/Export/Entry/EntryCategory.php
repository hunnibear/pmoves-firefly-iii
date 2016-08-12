<?php
/**
 * EntryCategory.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Export\Entry;

use FireflyIII\Models\Category;

/**
 * Class EntryCategory
 *
 * @package FireflyIII\Export\Entry
 */
class EntryCategory
{
    /** @var  int */
    public $categoryId = '';
    /** @var  string */
    public $name = '';

    /**
     * EntryCategory constructor.
     *
     * @param Category $category
     */
    public function __construct(Category $category = null)
    {
        if (!is_null($category)) {
            $this->categoryId = $category->id;
            $this->name       = $category->name;
        }
    }
}
