<?php
/**
 * ImporterInterface.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Import\Importer;
use FireflyIII\Models\ImportJob;
use Illuminate\Support\Collection;

/**
 * Interface ImporterInterface
 *
 * @package FireflyIII\Import\Importer
 */
interface ImporterInterface
{
    /**
     * Run the actual import
     *
     * @return Collection
     */
    public function createImportEntries(): Collection;

    /**
     * @param ImportJob $job
     *
     */
    public function setJob(ImportJob $job);
}