<?php
declare(strict_types = 1);
/**
 * ConfigurationFile.php
 * Copyright (C) 2016 Sander Dorigo
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Export;

use FireflyIII\Models\ExportJob;

/**
 * Class ConfigurationFile
 *
 * @package FireflyIII\Export
 */
class ConfigurationFile
{
    /** @var  ExportJob */
    private $job;

    /**
     * ConfigurationFile constructor.
     *
     * @param ExportJob $job
     */
    public function __construct(ExportJob $job)
    {
        $this->job = $job;
    }

    /**
     * @return bool
     */
    public function make()
    {
        $fields = array_keys(get_class_vars(Entry::class));
        $types  = Entry::getTypes();

        $configuration = [
            'date-format' => 'Y-m-d', // unfortunately, this is hard-coded.
            'has-headers' => true,
            'map'         => [], // we could build a map if necessary for easy re-import.
            'roles'       => [],
            'mapped'      => [],
            'specifix'    => [],
        ];
        foreach ($fields as $field) {
            $configuration['roles'][] = $types[$field];
        }

        $file = storage_path('export') . DIRECTORY_SEPARATOR . $this->job->key . '-configuration.json';
        file_put_contents($file, json_encode($configuration, JSON_PRETTY_PRINT));

        return $file;
    }

}
