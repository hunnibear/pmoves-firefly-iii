<?php

namespace FireflyIII\Helpers\Help;

use Cache;
use ErrorException;
use League\CommonMark\CommonMarkConverter;
use Log;
use Route;

/**
 * Class Help
 *
 * @package FireflyIII\Helpers\Help
 */
class Help implements HelpInterface
{

    /**
     * @codeCoverageIgnore
     *
     * @param $key
     *
     * @return string
     */
    public function getFromCache($key)
    {
        return Cache::get($key);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $route
     *
     * @return array
     */
    public function getFromGithub($route)
    {
        $uri        = 'https://raw.githubusercontent.com/JC5/firefly-iii-help/master/en/' . e($route) . '.md';
        $routeIndex = str_replace('.', '-', $route);
        $title      = trans('help.' . $routeIndex);
        $content    = [
            'text'  => '<p>There is no help for this route!</p>',
            'title' => $title,
        ];
        try {
            $content['text'] = file_get_contents($uri);
        } catch (ErrorException $e) {
            Log::error(trim($e->getMessage()));
        }
        if (strlen(trim($content['text'])) == 0) {
            $content['text'] = '<p>There is no help for this route.</p>';
        }
        $converter       = new CommonMarkConverter();
        $content['text'] = $converter->convertToHtml($content['text']);

        return $content;

    }

    /**
     * @codeCoverageIgnore
     *
     * @param $route
     *
     * @return bool
     */
    public function hasRoute($route)
    {
        return Route::has($route);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $route
     *
     * @return bool
     */
    public function inCache($route)
    {
        return Cache::has('help.' . $route . '.title') && Cache::has('help.' . $route . '.text');
    }

    /**
     * @codeCoverageIgnore
     *
     * @param       $route
     * @param array $content
     *
     * @internal param $title
     */
    public function putInCache($route, array $content)
    {
        Cache::put('help.' . $route . '.text', $content['text'], 10080); // a week.
        Cache::put('help.' . $route . '.title', $content['title'], 10080);
    }
}
