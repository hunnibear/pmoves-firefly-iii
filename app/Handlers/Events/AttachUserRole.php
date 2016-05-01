<?php
declare(strict_types = 1);
/**
 * AttachUserRole.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace FireflyIII\Handlers\Events;


use FireflyIII\Events\UserRegistration;
use FireflyIII\Repositories\User\UserRepositoryInterface;
use Log;

/**
 * Class AttachUserRole
 *
 * @package FireflyIII\Handlers\Events
 */
class AttachUserRole
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRegistration $event
     */
    public function handle(UserRegistration $event)
    {
        Log::debug('Trigger attachuserrole');
        /** @var UserRepositoryInterface $repository */
        $repository = app(UserRepositoryInterface::class);

        // first user ever?
        if ($repository->count() == 1) {
            Log::debug('Will attach role.');
            $repository->attachRole($event->user, 'owner');
        }
    }

}
