<?php
/*
 * ListController.php
 * Copyright (c) 2022 james@firefly-iii.org
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

namespace FireflyIII\Api\V2\Controllers\Model\Budget;

use FireflyIII\Api\V2\Controllers\Controller;
use FireflyIII\Transformers\V2\BudgetTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ListController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $collection  = new Collection;
        $paginator   = new LengthAwarePaginator($collection, 0, 50, 1);
        $transformer = new BudgetTransformer();
        return response()
            ->api($this->jsonApiList('budgets', $paginator, $transformer))
            ->header('Content-Type', self::CONTENT_TYPE);
    }

}
