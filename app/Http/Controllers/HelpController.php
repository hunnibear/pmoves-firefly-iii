<?php namespace FireflyIII\Http\Controllers;

use FireflyIII\Helpers\Help\HelpInterface;
use Log;
use Preferences;
use Response;

/**
 * Class HelpController
 *
 * @package FireflyIII\Http\Controllers
 */
class HelpController extends Controller
{
    /**
     * HelpController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param HelpInterface $help
     * @param               $route
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(HelpInterface $help, string $route)
    {
        $content = [
            'text'  => '<p>' . strval(trans('firefly.route_has_no_help')) . '</p>',
            'title' => 'Help',
        ];

        if (!$help->hasRoute($route)) {
            Log::error('No such route: ' . $route);

            return Response::json($content);
        }

        if ($help->inCache($route)) {
            $content = [
                'text'  => $help->getFromCache('help.' . $route . '.text'),
                'title' => $help->getFromCache('help.' . $route . '.title'),
            ];

            return Response::json($content);
        }
        $language = Preferences::get('language', env('DEFAULT_LANGUAGE', 'en_US'))->data;
        Log::debug('Will get help from Github for language "' . $language . '" and route "' . $route . '".');
        $content = $help->getFromGithub($language, $route);

        $help->putInCache($route, $content);

        return Response::json($content);

    }


}
