<?php
declare(strict_types = 1);
/**
 * CsvExporter.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Export\Exporter;

use FireflyIII\Export\Entry;
use FireflyIII\Models\ExportJob;
use League\Csv\Writer;
use SplFileObject;

/**
 * Class CsvExporter
 *
 * @package FireflyIII\Export\Exporter
 */
class CsvExporter extends BasicExporter implements ExporterInterface
{
    /** @var  string */
    private $fileName;

    /** @var  resource */
    private $handler;

    /**
     * CsvExporter constructor.
     */
    public function __construct(ExportJob $job)
    {
        parent::__construct($job);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     *
     */
    public function run()
    {
        // create temporary file:
        $this->tempFile();

        // create CSV writer:
        $writer = Writer::createFromPath(new SplFileObject($this->fileName, 'a+'), 'w');
        //the $writer object open mode will be 'w'!!

        // all rows:
        $rows = [];

        // add header:
        $first  = $this->getEntries()->first();
        $rows[] = array_keys(get_object_vars($first));

        // then the rest:
        /** @var Entry $entry */
        foreach ($this->getEntries() as $entry) {
            $rows[] = array_values(get_object_vars($entry));

        }
        $writer->insertAll($rows);
    }

    private function tempFile()
    {
        $fileName       = $this->job->key . '-records.csv';
        $this->fileName = storage_path('export') . DIRECTORY_SEPARATOR . $fileName;
        $this->handler  = fopen($this->fileName, 'w');
    }
}