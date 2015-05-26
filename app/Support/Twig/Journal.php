<?php

namespace FireflyIII\Support\Twig;


use App;
use FireflyIII\Models\TransactionJournal;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Class Journal
 *
 * @package FireflyIII\Support\Twig
 */
class Journal extends Twig_Extension
{

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return array
     */
    public function getFilters()
    {
        $filters = [];

        $filters[] = new Twig_SimpleFilter(
            'typeIcon', function(TransactionJournal $journal) {
            $type = $journal->transactionType->type;

            switch ($type) {
                case 'Withdrawal':
                    return '<span class="glyphicon glyphicon-arrow-left" title="' . trans('firefly.withdrawal') . '"></span>';
                case 'Deposit':
                    return '<span class="glyphicon glyphicon-arrow-right" title="' . trans('firefly.deposit') . '"></span>';
                case 'Transfer':
                    return '<i class="fa fa-fw fa-exchange" title="' . trans('firefly.transfer') . '"></i>';
                case 'Opening balance':
                    return '<span class="glyphicon glyphicon-ban-circle" title="' . trans('firefly.openingBalance') . '"></span>';
                default:
                    return '';
            }


        }, ['is_safe' => ['html']]
        );

        return $filters;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return array
     */
    public function getFunctions()
    {
        $functions = [];

        $functions[] = new Twig_SimpleFunction(
            'invalidJournal', function(TransactionJournal $journal) {
            if (!isset($journal->transactions[1]) || !isset($journal->transactions[0])) {
                return true;
            }

            return false;
        }
        );

        $functions[] = new Twig_SimpleFunction(
            'relevantTags', function(TransactionJournal $journal) {
            if ($journal->tags->count() == 0) {
                return App::make('amount')->formatJournal($journal);
            }


            foreach ($journal->tags as $tag) {
                if ($tag->tagMode == 'balancingAct') {
                    // return tag formatted for a "balancing act", even if other
                    // tags are present.
                    $amount = App::make('amount')->format($journal->actual_amount, false);

                    return '<a href="' . route('tags.show', [$tag->id]) . '" class="label label-success" title="' . $amount
                            . '"><i class="fa fa-fw fa-refresh"></i> ' . $tag->tag . '</a>';
                }

                /*
                 * AdvancePayment with a deposit will show the tag instead of the amount:
                 */
                if ($tag->tagMode == 'advancePayment' && $journal->transactionType->type == 'Deposit') {
                    $amount = App::make('amount')->formatJournal($journal, false);

                    return '<a href="' . route('tags.show', [$tag->id]) . '" class="label label-success" title="' . $amount
                            . '"><i class="fa fa-fw fa-sort-numeric-desc"></i> ' . $tag->tag . '</a>';
                }
                /*
                 * AdvancePayment with a withdrawal will show the amount with a link to
                 * the tag. The TransactionJournal should properly calculate the amount.
                 */
                if ($tag->tagMode == 'advancePayment' && $journal->transactionType->type == 'Withdrawal') {
                    $amount = App::make('amount')->formatJournal($journal);

                    return '<a href="' . route('tags.show', [$tag->id]) . '">' . $amount . '</a>';
                }


                if ($tag->tagMode == 'nothing') {
                    // return the amount:
                    return App::make('amount')->formatJournal($journal);
                }
            }


            return 'TODO: ' . $journal->amount;
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
        return 'FireflyIII\Support\Twig\Journals';
    }
}
