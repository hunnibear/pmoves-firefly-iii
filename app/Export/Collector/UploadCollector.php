<?php
declare(strict_types = 1);
/**
 * UploadCollector.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Export\Collector;

use Auth;
use Crypt;
use FireflyIII\Models\ExportJob;
use Illuminate\Contracts\Encryption\DecryptException;
use Log;
use Storage;

/**
 * Class UploadCollector
 *
 * @package FireflyIII\Export\Collector
 */
class UploadCollector extends BasicCollector implements CollectorInterface
{
    /** @var string */
    private $expected;
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    private $exportDisk;
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    private $uploadDisk;

    /**
     * AttachmentCollector constructor.
     *
     * @param ExportJob $job
     */
    public function __construct(ExportJob $job)
    {
        parent::__construct($job);

        // make storage:
        $this->uploadDisk = Storage::disk('upload');
        $this->exportDisk = Storage::disk('export');
        $this->expected   = 'csv-upload-' . Auth::user()->id . '-';
    }

    /**
     *
     */
    public function run()
    {
        // grab upload directory.
        $files = $this->uploadDisk->files();
        Log::debug('Found ' . count($files) . ' files in the upload directory.');

        foreach ($files as $entry) {
            $this->processOldUpload($entry);
        }
    }

    /**
     * @param string $entry
     *
     * @return string
     */
    private function getOriginalUploadDate(string $entry): string
    {
        // this is an original upload.
        $parts          = explode('-', str_replace(['.csv.encrypted', $this->expected], '', $entry));
        $originalUpload = intval($parts[1]);
        $date           = date('Y-m-d \a\t H-i-s', $originalUpload);

        return $date;
    }

    /**
     * @param string $entry
     *
     * @return bool
     */
    private function isValidFile(string $entry): bool
    {
        $len = strlen($this->expected);
        if (substr($entry, 0, $len) === $this->expected) {
            Log::debug($entry . ' is part of this users original uploads.');

            return true;
        }
        Log::debug($entry . ' is not part of this users original uploads.');

        return false;
    }

    /**
     * @param $entry
     */
    private function processOldUpload(string $entry)
    {
        $content = '';

        if ($this->isValidFile($entry)) {
            try {
                $content = Crypt::decrypt($this->uploadDisk->get($entry));
            } catch (DecryptException $e) {
                Log::error('Could not decrypt old CSV import file ' . $entry . '. Skipped because ' . $e->getMessage());
            }
        }
        if (strlen($content) > 0) {
            // continue with file:
            $date = $this->getOriginalUploadDate($entry);
            $file = $this->job->key . '-Old CSV import dated ' . $date . '.csv';
            Log::debug('Will put "' . $file . '" in the zip file.');
            $this->exportDisk->put($file, $content);
            $this->getFiles()->push($file);
        }
    }
}
