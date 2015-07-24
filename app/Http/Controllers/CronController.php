<?php

namespace FireflyIII\Http\Controllers;

use FireflyIII\User;

/**
 * Class WebhookController
 *
 * @package FireflyIII\Http\Controllers
 */
class CronController extends Controller
{

    /**
     * Firefly doesn't have anything that should be in the a cron job, except maybe this one, and it's fairly exceptional.
     *
     * If you use SendGrid like I do, you can detect bounces and thereby check if users gave an invalid address. If they did,
     * it's easy to block them and change their password. Optionally, you could notify yourself about it and send them a message.
     *
     * But thats something not supported right now.
     */
    public function sendgrid()
    {

        $URL        = 'https://api.sendgrid.com/api/bounces.get.json';
        $parameters = [
            'api_user' => env('SENDGRID_USERNAME'),
            'api_key'  => env('SENDGRID_PASSWORD'),
            'date'     => 1,
            'days'     => 7
        ];
        $fullURL    = $URL . '?' . http_build_query($parameters);
        $data       = json_decode(file_get_contents($fullURL));

        /*
         * Loop the result, if any.
         */
        if (is_array($data)) {
            foreach ($data as $entry) {
                $address = $entry->email;
                $user    = User::where('email', $address)->where('blocked', 0)->first();
                if (!is_null($user)) {
                    $user->blocked  = 1;
                    $user->password = 'bounced';
                    $user->save();
                }
            }
        }
    }

}