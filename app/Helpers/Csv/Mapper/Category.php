<?php

namespace FireflyIII\Helpers\Csv\Mapper;

use Auth;
use FireflyIII\Models\Category as CategoryModel;

/**
 * Class Category
 *
 * @package FireflyIII\Helpers\Csv\Mapper
 */
class Category implements MapperInterface
{

    /**
     * @return array
     */
    public function getMap()
    {
        $result = Auth::user()->categories()->get(['categories.*']);
        $list   = [];

        /** @var CategoryModel $category */
        foreach ($result as $category) {
            $list[$category->id] = $category->name;
        }
        asort($list);

        array_unshift($list, trans('firefly.csv_do_not_map'));

        return $list;
    }
}