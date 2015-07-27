<?php
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Generator;
use FireflyIII\Models\Account;
use FireflyIII\Models\Bill;
use FireflyIII\Models\Budget;
use FireflyIII\Models\Category;
use FireflyIII\Models\LimitRepetition;
use FireflyIII\Models\PiggyBank;
use FireflyIII\Models\Tag;
use FireflyIII\Models\TransactionCurrency;
use FireflyIII\Models\TransactionJournal;

/*
 * Back home.
 */
Breadcrumbs::register(
    'home',
    function (Generator $breadcrumbs) {

        $breadcrumbs->push(trans('breadcrumbs.home'), route('index'));
    }
);

Breadcrumbs::register(
    'index',
    function (Generator $breadcrumbs) {

        $breadcrumbs->push(trans('breadcrumbs.home'), route('index'));
    }
);
//trans('breadcrumbs.')

// accounts
Breadcrumbs::register(
    'accounts.index', function (Generator $breadcrumbs, $what) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('firefly.' . strtolower(e($what)) . '_accounts'), route('accounts.index', [$what]));
}
);

Breadcrumbs::register(
    'accounts.create', function (Generator $breadcrumbs, $what) {
    $breadcrumbs->parent('accounts.index', $what);
    $breadcrumbs->push(trans('breadcrumbs.new_' . strtolower(e($what)) . '_account'), route('accounts.create', [$what]));
}
);

Breadcrumbs::register(
    'accounts.show', function (Generator $breadcrumbs, Account $account) {

    $what = Config::get('firefly.shortNamesByFullName.' . $account->accountType->type);


    $breadcrumbs->parent('accounts.index', $what);
    $breadcrumbs->push(e($account->name), route('accounts.show', [$account->id]));
}
);
Breadcrumbs::register(
    'accounts.delete', function (Generator $breadcrumbs, Account $account) {
    $breadcrumbs->parent('accounts.show', $account);
    $breadcrumbs->push(trans('firefly.delete_account', ['name' => e($account->name)]), route('accounts.delete', [$account->id]));
}
);


Breadcrumbs::register(
    'accounts.edit', function (Generator $breadcrumbs, Account $account) {
    $breadcrumbs->parent('accounts.show', $account);
    $what = Config::get('firefly.shortNamesByFullName.' . $account->accountType->type);

    $breadcrumbs->push(trans('firefly.edit_' . $what . '_account', ['name' => e($account->name)]), route('accounts.edit', [$account->id]));
}
);

// budgets.
Breadcrumbs::register(
    'budgets.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('firefly.budgets'), route('budgets.index'));
}
);
Breadcrumbs::register(
    'budgets.create', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('budgets.index');
    $breadcrumbs->push(trans('firefly.create_new_budget'), route('budgets.create'));
}
);

Breadcrumbs::register(
    'budgets.edit', function (Generator $breadcrumbs, Budget $budget) {
    $breadcrumbs->parent('budgets.show', $budget);
    $breadcrumbs->push(trans('firefly.edit_budget', ['name' => e($budget->name)]), route('budgets.edit', [$budget->id]));
}
);
Breadcrumbs::register(
    'budgets.delete', function (Generator $breadcrumbs, Budget $budget) {
    $breadcrumbs->parent('budgets.show', $budget);
    $breadcrumbs->push(trans('firefly.delete_budget', ['name' => e($budget->name)]), route('budgets.delete', [$budget->id]));
}
);

Breadcrumbs::register(
    'budgets.noBudget', function (Generator $breadcrumbs, $subTitle) {
    $breadcrumbs->parent('budgets.index');
    $breadcrumbs->push($subTitle, route('budgets.noBudget'));
}
);

Breadcrumbs::register(
    'budgets.show', function (Generator $breadcrumbs, Budget $budget, LimitRepetition $repetition = null) {
    $breadcrumbs->parent('budgets.index');
    $breadcrumbs->push(e($budget->name), route('budgets.show', [$budget->id]));
    if (!is_null($repetition) && !is_null($repetition->id)) {
        $breadcrumbs->push(
            Navigation::periodShow($repetition->startdate, $repetition->budgetLimit->repeat_freq), route('budgets.show', [$budget->id, $repetition->id])
        );
    }
}
);

// categories
Breadcrumbs::register(
    'categories.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('firefly.categories'), route('categories.index'));
}
);
Breadcrumbs::register(
    'categories.create', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('categories.index');
    $breadcrumbs->push(trans('breadcrumbs.newCategory'), route('categories.create'));
}
);

Breadcrumbs::register(
    'categories.edit', function (Generator $breadcrumbs, Category $category) {
    $breadcrumbs->parent('categories.show', $category);
    $breadcrumbs->push(trans('firefly.edit_category', ['name' => e($category->name)]), route('categories.edit', [$category->id]));
}
);
Breadcrumbs::register(
    'categories.delete', function (Generator $breadcrumbs, Category $category) {
    $breadcrumbs->parent('categories.show', $category);
    $breadcrumbs->push(trans('firefly.delete_category', ['name' => e($category->name)]), route('categories.delete', [$category->id]));
}
);

Breadcrumbs::register(
    'categories.show', function (Generator $breadcrumbs, Category $category) {
    $breadcrumbs->parent('categories.index');
    $breadcrumbs->push(e($category->name), route('categories.show', [$category->id]));

}
);

Breadcrumbs::register(
    'categories.noCategory', function (Generator $breadcrumbs, $subTitle) {
    $breadcrumbs->parent('categories.index');
    $breadcrumbs->push($subTitle, route('categories.noCategory'));
}
);

// CSV:
Breadcrumbs::register(
    'csv.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('firefly.csv_index_title'), route('csv.index'));
}
);

Breadcrumbs::register(
    'csv.column-roles', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('csv.index');
    $breadcrumbs->push(trans('firefly.csv_define_column_roles'), route('csv.column-roles'));
}
);

Breadcrumbs::register(
    'csv.map', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('csv.index');
    $breadcrumbs->push(trans('firefly.csv_map_values'), route('csv.map'));
}
);

Breadcrumbs::register(
    'csv.download-config-page', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('csv.index');
    $breadcrumbs->push(trans('firefly.csv_download_config'), route('csv.download-config-page'));
}
);

Breadcrumbs::register(
    'csv.process', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('csv.index');
    $breadcrumbs->push(trans('firefly.csv_process_title'), route('csv.process'));
}
);


// currencies.
Breadcrumbs::register(
    'currency.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('firefly.currencies'), route('currency.index'));
}
);

Breadcrumbs::register(
    'currency.create', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('currency.index');
    $breadcrumbs->push(trans('firefly.create_currency'), route('currency.create'));
}
);

Breadcrumbs::register(
    'currency.edit', function (Generator $breadcrumbs, TransactionCurrency $currency) {
    $breadcrumbs->parent('currency.index');
    $breadcrumbs->push(trans('breadcrumbs.edit_currency', ['name' => e($currency->name)]), route('currency.edit', [$currency->id]));
}
);
Breadcrumbs::register(
    'currency.delete', function (Generator $breadcrumbs, TransactionCurrency $currency) {
    $breadcrumbs->parent('currency.index');
    $breadcrumbs->push(trans('breadcrumbs.delete_currency', ['name' => e($currency->name)]), route('currency.delete', [$currency->id]));
}
);


// piggy banks
Breadcrumbs::register(
    'piggy-banks.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.piggyBanks'), route('piggy-banks.index'));
}
);
Breadcrumbs::register(
    'piggy-banks.create', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('piggy-banks.index');
    $breadcrumbs->push(trans('breadcrumbs.newPiggyBank'), route('piggy-banks.create'));
}
);

Breadcrumbs::register(
    'piggy-banks.edit', function (Generator $breadcrumbs, PiggyBank $piggyBank) {
    $breadcrumbs->parent('piggy-banks.show', $piggyBank);
    $breadcrumbs->push(trans('breadcrumbs.edit_piggyBank', ['name' => e($piggyBank->name)]), route('piggy-banks.edit', [$piggyBank->id]));
}
);
Breadcrumbs::register(
    'piggy-banks.delete', function (Generator $breadcrumbs, PiggyBank $piggyBank) {
    $breadcrumbs->parent('piggy-banks.show', $piggyBank);
    $breadcrumbs->push(trans('breadcrumbs.delete_piggyBank', ['name' => e($piggyBank->name)]), route('piggy-banks.delete', [$piggyBank->id]));
}
);

Breadcrumbs::register(
    'piggy-banks.show', function (Generator $breadcrumbs, PiggyBank $piggyBank) {
    $breadcrumbs->parent('piggy-banks.index');
    $breadcrumbs->push(e($piggyBank->name), route('piggy-banks.show', [$piggyBank->id]));

}
);

// preferences
Breadcrumbs::register(
    'preferences', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.preferences'), route('preferences'));

}
);

// profile
Breadcrumbs::register(
    'profile', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.profile'), route('profile'));

}
);
Breadcrumbs::register(
    'profile.change-password', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('profile');
    $breadcrumbs->push(trans('breadcrumbs.changePassword'), route('profile.change-password'));

}
);
Breadcrumbs::register(
    'profile.delete-account', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('profile');
    $breadcrumbs->push(trans('firefly.delete_account'), route('profile.delete-account'));

}
);

// bills
Breadcrumbs::register(
    'bills.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.bills'), route('bills.index'));
}
);
Breadcrumbs::register(
    'bills.create', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('bills.index');
    $breadcrumbs->push(trans('breadcrumbs.newBill'), route('bills.create'));
}
);

Breadcrumbs::register(
    'bills.edit', function (Generator $breadcrumbs, Bill $bill) {
    $breadcrumbs->parent('bills.show', $bill);
    $breadcrumbs->push(trans('breadcrumbs.edit_bill', ['name' => e($bill->name)]), route('bills.edit', [$bill->id]));
}
);
Breadcrumbs::register(
    'bills.delete', function (Generator $breadcrumbs, Bill $bill) {
    $breadcrumbs->parent('bills.show', $bill);
    $breadcrumbs->push(trans('breadcrumbs.delete_bill', ['name' => e($bill->name)]), route('bills.delete', [$bill->id]));
}
);

Breadcrumbs::register(
    'bills.show', function (Generator $breadcrumbs, Bill $bill) {
    $breadcrumbs->parent('bills.index');
    $breadcrumbs->push(e($bill->name), route('bills.show', [$bill->id]));

}
);

// reports
Breadcrumbs::register(
    'reports.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.reports'), route('reports.index'));
}
);

Breadcrumbs::register(
    'reports.year', function (Generator $breadcrumbs, Carbon $date, $shared) {
    $breadcrumbs->parent('reports.index');
    if ($shared) {
        $title = trans('breadcrumbs.yearly_report_shared', ['date' => $date->year]);
    } else {
        $title = trans('breadcrumbs.yearly_report', ['date' => $date->year]);
    }
    $breadcrumbs->push($title, route('reports.year', [$date->year]));
}
);

Breadcrumbs::register(
    'reports.month', function (Generator $breadcrumbs, Carbon $date, $shared) {
    $breadcrumbs->parent('reports.year', $date, $shared);
    $language = Preferences::get('language', 'en')->data;
    $format   = Config::get('firefly.month.' . $language);

    if ($shared) {
        $title = trans('breadcrumbs.monthly_report_shared', ['date' => $date->formatLocalized($format)]);
    } else {
        $title = trans('breadcrumbs.monthly_report', ['date' => $date->formatLocalized($format)]);
    }

    $breadcrumbs->push($title, route('reports.month', [$date->year, $date->month]));
}
);

// search
Breadcrumbs::register(
    'search', function (Generator $breadcrumbs, $query) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.searchResult', ['query' => e($query)]), route('search'));
}
);

// transactions
Breadcrumbs::register(
    'transactions.index', function (Generator $breadcrumbs, $what) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.' . $what . '_list'), route('transactions.index', [$what]));
}
);
Breadcrumbs::register(
    'transactions.create', function (Generator $breadcrumbs, $what) {
    $breadcrumbs->parent('transactions.index', $what);
    $breadcrumbs->push(trans('breadcrumbs.create_' . e($what)), route('transactions.create', [$what]));
}
);

Breadcrumbs::register(
    'transactions.edit', function (Generator $breadcrumbs, TransactionJournal $journal) {
    $breadcrumbs->parent('transactions.show', $journal);
    $breadcrumbs->push(trans('breadcrumbs.edit_journal', ['description' => $journal->description]), route('transactions.edit', [$journal->id]));
}
);
Breadcrumbs::register(
    'transactions.delete', function (Generator $breadcrumbs, TransactionJournal $journal) {
    $breadcrumbs->parent('transactions.show', $journal);
    $breadcrumbs->push(trans('breadcrumbs.delete_journal', ['description' => e($journal->description)]), route('transactions.delete', [$journal->id]));
}
);

Breadcrumbs::register(
    'transactions.show', function (Generator $breadcrumbs, TransactionJournal $journal) {

    $breadcrumbs->parent('transactions.index', strtolower($journal->transactionType->type));
    $breadcrumbs->push($journal->description, route('transactions.show', [$journal->id]));

}
);

// tags
Breadcrumbs::register(
    'tags.index', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('breadcrumbs.tags'), route('tags.index'));
}
);

Breadcrumbs::register(
    'tags.create', function (Generator $breadcrumbs) {
    $breadcrumbs->parent('tags.index');
    $breadcrumbs->push(trans('breadcrumbs.createTag'), route('tags.create'));
}
);

Breadcrumbs::register(
    'tags.edit', function (Generator $breadcrumbs, Tag $tag) {
    $breadcrumbs->parent('tags.show', $tag);
    $breadcrumbs->push(trans('breadcrumbs.edit_tag', ['tag' => e($tag->tag)]), route('tags.edit', [$tag->id]));
}
);

Breadcrumbs::register(
    'tags.delete', function (Generator $breadcrumbs, Tag $tag) {
    $breadcrumbs->parent('tags.show', $tag);
    $breadcrumbs->push(trans('breadcrumbs.delete_tag', ['tag' => e($tag->tag)]), route('tags.delete', [$tag->id]));
}
);


Breadcrumbs::register(
    'tags.show', function (Generator $breadcrumbs, Tag $tag) {
    $breadcrumbs->parent('tags.index');
    $breadcrumbs->push(e($tag->tag), route('tags.show', [$tag->id]));
}
);
