<?php
/*
 * BillDateCalculatorTest.php
 * Copyright (c) 2023 james@firefly-iii.org
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

namespace Tests\integration\Support\Models;

use Carbon\Carbon;
use FireflyIII\Support\Models\BillDateCalculator;
use Tests\integration\TestCase;

/**
 * Class BillDateCalculatorTest
 */
class BillDateCalculatorTest extends TestCase
{
    private BillDateCalculator $calculator;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->calculator = new BillDateCalculator();
    }

    public static function provideDates(): iterable
    {
        // Carbon $earliest, Carbon $latest, Carbon $billStart, string $period, int $skip, ?Carbon $lastPaid
        return [
            // basic monthly bill.
            '1M' => ['earliest' => Carbon::parse('2023-11-01'), 'latest' => Carbon::parse('2023-11-30'), 'billStart' => Carbon::parse('2023-01-01'), 'period' => 'monthly', 'skip' => 0, 'lastPaid' => null, 'expected' => ['2023-11-01']],
        ];
    }

    /**
     * Stupid long method names I'm not going to do that.
     *
     * @dataProvider provideDates
     */
    public function testGivenSomeDataItWorks(Carbon $earliest, Carbon $latest, Carbon $billStart, string $period, int $skip, ?Carbon $lastPaid, array $expected): void
    {
        $result = $this->calculator->getPayDates($earliest, $latest, $billStart, $period, $skip, $lastPaid);
        self::assertSame($expected, $result);
    }
}
