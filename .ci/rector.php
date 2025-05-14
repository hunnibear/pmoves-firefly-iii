<?php


/*
 * rector.php
 * Copyright (c) 2025 james@firefly-iii.org.
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
 * along with this program.  If not, see https://www.gnu.org/licenses/.
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
                   ->withPaths([
//                                   __DIR__ . '/../app',
__DIR__ . '/../app/Http',
//                                   __DIR__ . '/../bootstrap',
//                                   __DIR__ . '/../config',
//                                   __DIR__ . '/../public',
//                                   __DIR__ . '/../resources',
//                                   __DIR__ . '/../routes',
// __DIR__ . '/../tests',
                               ])
                                                   // uncomment to reach your current PHP version
                   ->withPhpSets()
                   ->withPreparedSets(
                       codingStyle        : false, // leave false
                       privatization: false, // leave false.
                       naming             : false, // leave false
                       instanceOf         : true,
                       earlyReturn        : false,
                       strictBooleans     : false,
                       carbon             : false,
                       rectorPreset       : false,
                       phpunitCodeQuality : false,
                       doctrineCodeQuality: false,
                       symfonyCodeQuality : false,
                       symfonyConfigs     : false

                   )
                   ->withComposerBased(
                       twig: true,
                       doctrine: true,
                       phpunit: true,
                       symfony: true)
                   ->withTypeCoverageLevel(0)
                   ->withDeadCodeLevel(0)
                   ->withCodeQualityLevel(0)
                   ->withImportNames(removeUnusedImports: true);// import statements instead of full classes.
