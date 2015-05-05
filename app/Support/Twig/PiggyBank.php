<?php

namespace FireflyIII\Support\Twig;

use FireflyIII\Models\PiggyBank as PB;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class PiggyBank
 *
 * @package FireflyIII\Support\Twig
 */
class PiggyBank extends Twig_Extension
{

    /**
     *
     */
    public function getFunctions()
    {
        $functions = [];

        $functions[] = new Twig_SimpleFunction(
            'currentRelevantRepAmount', function (PB $piggyBank) {
            return $piggyBank->currentRelevantRep()->currentamount;
        }
        );

        return $functions;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'FireflyIII\Support\Twig\PiggyBank';
    }
}
