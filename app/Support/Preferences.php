<?php

namespace FireflyIII\Support;

use Auth;
use FireflyIII\Models\Preference;
use Log;
/**
 * Class Preferences
 *
 * @package FireflyIII\Support
 */
class Preferences
{
    /**
     * @return string
     */
    public function lastActivity()
    {
        $preference = $this->get('lastActivity', microtime())->data;

        return md5($preference);
    }

    /**
     * @return bool
     */
    public function mark() {
        Log::debug('MARK!');
        $this->set('lastActivity',microtime());
        return true;
    }

    /**
     * @param      $name
     * @param null $default
     *
     * @return null|\FireflyIII\Models\Preference
     */
    public function get($name, $default = null)
    {
        $preferences = Preference::where('user_id', Auth::user()->id)->get();

        /** @var Preference $preference */
        foreach ($preferences as $preference) {
            if ($preference->name == $name) {
                return $preference;
            }
        }
        // no preference found and default is null:
        if (is_null($default)) {
            // return NULL
            return null;
        }

        return $this->set($name, $default);

    }

    /**
     * @param $name
     * @param $value
     *
     * @return Preference
     */
    public function set($name, $value)
    {
        $preferences = Preference::where('user_id', Auth::user()->id)->get();
        /** @var Preference $preference */
        foreach ($preferences as $preference) {
            if ($preference->name == $name) {
                $preference->data = $value;
                $preference->save();

                return $preference;
            }
        }
        $pref       = new Preference;
        $pref->name = $name;
        $pref->data = $value;

        if (!is_null(Auth::user()->id)) {
            $pref->user()->associate(Auth::user());
            $pref->save();
        }

        return $pref;

    }
}
