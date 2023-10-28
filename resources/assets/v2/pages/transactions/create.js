/*
 * create.js
 * Copyright (c) 2023 james@firefly-iii.org
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import '../../boot/bootstrap.js';
import dates from '../../pages/shared/dates.js';
import {createEmptySplit} from "./shared/create-empty-split.js";
import {parseFromEntries} from "./shared/parse-from-entries.js";
import formatMoney from "../../util/format-money.js";
import Autocomplete from "bootstrap5-autocomplete";
import Post from "../../api/v2/model/transaction/post.js";
import Get from "../../api/v2/model/currency/get.js";
import {getVariable} from "../../store/get-variable.js";
import {I18n} from "i18n-js";
import {loadTranslations} from "../../support/load-translations.js";

let i18n;

const urls = {
    description: '/api/v2/autocomplete/transaction-descriptions',
    account: '/api/v2/autocomplete/accounts',
};

let transactions = function () {
    return {
        count: 0,
        totalAmount: 0,
        transactionType: 'unknown',
        showSuccessMessage: false,
        showErrorMessage: false,
        entries: [],
        loadingCurrencies: true,
        defaultCurrency: {},
        enabledCurrencies: [],
        nativeCurrencies: [],
        foreignCurrencies: [],
        filters: {
            source: [],
            destination: [],
        },
        errorMessageText: '',
        detectTransactionType() {
            const sourceType = this.entries[0].source_account.type ?? 'unknown';
            const destType = this.entries[0].destination_account.type ?? 'unknown';
            if ('unknown' === sourceType && 'unknown' === destType) {
                this.transactionType = 'unknown';
                console.warn('Cannot infer transaction type from two unknown accounts.');
                return;
            }
            // transfer: both are the same and in strict set of account types
            if (sourceType === destType && ['Asset account', 'Loan', 'Debt', 'Mortgage'].includes(sourceType)) {
                this.transactionType = 'transfer';
                console.log('Transaction type is detected to be "' + this.transactionType + '".');
                return;
            }
            // withdrawals:
            if ('Asset account' === sourceType && ['Expense account', 'Debt', 'Loan', 'Mortgage'].includes(destType)) {
                this.transactionType = 'withdrawal';
                console.log('Transaction type is detected to be "' + this.transactionType + '".');
                return;
            }
            if ('Asset account' === sourceType && 'unknown' === destType) {
                this.transactionType = 'withdrawal';
                console.log('Transaction type is detected to be "' + this.transactionType + '".');
                return;
            }
            if (['Debt', 'Loan', 'Mortgage'].includes(sourceType) && 'Expense account' === destType) {
                this.transactionType = 'withdrawal';
                console.log('Transaction type is detected to be "' + this.transactionType + '".');
                return;
            }

            // deposits:
            if ('Revenue account' === sourceType && ['Asset account', 'Debt', 'Loan', 'Mortgage'].includes(destType)) {
                this.transactionType = 'deposit';
                console.log('Transaction type is detected to be "' + this.transactionType + '".');
                return;
            }
            if (['Debt', 'Loan', 'Mortgage'].includes(sourceType) && 'Asset account' === destType) {
                this.transactionType = 'deposit';
                console.log('Transaction type is detected to be "' + this.transactionType + '".');
                return;
            }
            console.warn('Unknown account combination between "' + sourceType + '" and "' + destType + '".');
        },
        selectSourceAccount(item, ac) {
            const index = parseInt(ac._searchInput.attributes['data-index'].value);
            document.querySelector('#form')._x_dataStack[0].$data.entries[index].source_account =
                {
                    id: item.id,
                    name: item.name,
                    type: item.type,
                };
            console.log('Changed source account into a known ' + item.type.toLowerCase());
        },
        changedAmount(e) {
            const index = parseInt(e.target.dataset.index);
            this.entries[index].amount = parseFloat(e.target.value);
            this.totalAmount = 0;
            for (let i in this.entries) {
                if (this.entries.hasOwnProperty(i)) {
                    this.totalAmount = this.totalAmount + parseFloat(this.entries[i].amount);
                }
            }
            console.log('Changed amount to ' + this.totalAmount);
        },
        selectDestAccount(item, ac) {
            const index = parseInt(ac._searchInput.attributes['data-index'].value);
            document.querySelector('#form')._x_dataStack[0].$data.entries[index].destination_account =
                {
                    id: item.id,
                    name: item.name,
                    type: item.type,
                };
            console.log('Changed destination account into a known ' + item.type.toLowerCase());
        },
        loadCurrencies() {
            console.log('Loading user currencies.');
            let params = {
                page: 1,
                limit: 1337
            };
            let getter = new Get();
            getter.list({}).then((response) => {
                for(let i in response.data.data) {
                    if(response.data.data.hasOwnProperty(i)) {
                        let current = response.data.data[i];
                        if(current.attributes.enabled) {
                            let obj =

                                {
                                    id: current.id,
                                    name: current.attributes.name,
                                    code: current.attributes.code,
                                    default: current.attributes.default,
                                    symbol: current.attributes.symbol,
                                    decimal_places: current.attributes.decimal_places,

                                };
                            if(obj.default) {
                                this.defaultCurrency = obj;
                            }
                            this.enabledCurrencies.push(obj);
                        }
                    }
                }
                this.loadingCurrencies = false;
                console.log(this.enabledCurrencies);
            });
        },
        changeSourceAccount(item, ac) {
            if (typeof item === 'undefined') {
                const index = parseInt(ac._searchInput.attributes['data-index'].value);
                let source = document.querySelector('#form')._x_dataStack[0].$data.entries[index].source_account;
                if (source.name === ac._searchInput.value) {
                    console.warn('Ignore hallucinated source account name change to "' + ac._searchInput.value + '"');
                    return;
                }
                document.querySelector('#form')._x_dataStack[0].$data.entries[index].source_account =
                    {
                        name: ac._searchInput.value,
                    };
                console.log('Changed source account into a unknown account called "' + ac._searchInput.value + '"');
            }
        },
        changeDestAccount(item, ac) {
            if (typeof item === 'undefined') {
                const index = parseInt(ac._searchInput.attributes['data-index'].value);
                let destination = document.querySelector('#form')._x_dataStack[0].$data.entries[index].destination_account;
                if (destination.name === ac._searchInput.value) {
                    console.warn('Ignore hallucinated destination account name change to "' + ac._searchInput.value + '"');
                    return;
                }
                document.querySelector('#form')._x_dataStack[0].$data.entries[index].destination_account =
                    {
                        name: ac._searchInput.value,
                    };
                console.log('Changed destination account into a unknown account called "' + ac._searchInput.value + '"');
            }
        },


        // error and success messages:
        showError: false,
        showSuccess: false,

        addedSplit() {
            console.log('addedSplit');
            // TODO improve code location
            Autocomplete.init("input.ac-source", {
                server: urls.account,
                serverParams: {
                    types: this.filters.source,
                },
                fetchOptions: {
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                    }
                },
                hiddenInput: true,
                preventBrowserAutocomplete: true,
                highlightTyped: true,
                liveServer: true,
                onChange: this.changeSourceAccount,
                onSelectItem: this.selectSourceAccount,
                onRenderItem: function (item, b, c) {
                    return item.name_with_balance + '<br><small class="text-muted">' + i18n.t('firefly.account_type_' + item.type) + '</small>';
                }
            });

            Autocomplete.init("input.ac-dest", {
                server: urls.account,
                serverParams: {
                    types: this.filters.destination,
                },
                fetchOptions: {
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                    }
                },
                hiddenInput: true,
                preventBrowserAutocomplete: true,
                liveServer: true,
                highlightTyped: true,
                onSelectItem: this.selectDestAccount,
                onChange: this.changeDestAccount,
                onRenderItem: function (item, b, c) {
                    return item.name_with_balance + '<br><small class="text-muted">' + i18n.t('firefly.account_type_' + item.type) + '</small>';
                }
            });
            this.filters.destination = [];
            Autocomplete.init('input.ac-description', {
                server: urls.description,
                fetchOptions: {
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                    }
                },
                valueField: "id",
                labelField: "description",
                highlightTyped: true,
                onSelectItem: console.log,
            });


        },

        init() {
            Promise.all([getVariable('language', 'en_US')]).then((values) => {
                i18n = new I18n();
                const locale = values[0].replace('-', '_');
                i18n.locale = locale;
                loadTranslations(i18n, locale).then(() => {
                    this.addSplit();
                });

            });
            this.loadCurrencies();

            // source can never be expense account
            this.filters.source = ['Asset account', 'Loan', 'Debt', 'Mortgage', 'Revenue account'];
            // destination can never be revenue account
            this.filters.destination = ['Expense account', 'Loan', 'Debt', 'Mortgage', 'Asset account'];
        },
        submitTransaction() {
            this.detectTransactionType();
            // todo disable buttons

            let transactions = parseFromEntries(this.entries, this.transactionType);
            let submission = {
                // todo process all options
                group_title: null,
                fire_webhooks: false,
                apply_rules: false,
                transactions: transactions
            };
            if (transactions.length > 1) {
                // todo improve me
                submission.group_title = transactions[0].description;
            }
            let poster = new Post();
            console.log(submission);
            poster.post(submission).then((response) => {
                // todo create success banner
                this.showSuccessMessage = true;
                // todo release form
                console.log(response);

                // todo or redirect to transaction.
                window.location = 'transactions/show/' + response.data.data.id + '?transaction_group_id=' + response.data.data.id + '&message=created';

            }).catch((error) => {
                this.showErrorMessage = true;
                // todo create error banner.
                // todo release form
                this.errorMessageText = error.response.data.message;
            });
        },
        addSplit() {
            this.entries.push(createEmptySplit());
        },
        removeSplit(index) {
            this.entries.splice(index, 1);
            // fall back to index 0
            const triggerFirstTabEl = document.querySelector('#split-0-tab')
            triggerFirstTabEl.click();
        },
        formattedTotalAmount() {
            return formatMoney(this.totalAmount, 'EUR');
        }
    }
}

let comps = {transactions, dates};

function loadPage() {
    Object.keys(comps).forEach(comp => {
        console.log(`Loading page component "${comp}"`);
        let data = comps[comp]();
        Alpine.data(comp, () => data);
    });
    Alpine.start();
}

// wait for load until bootstrapped event is received.
document.addEventListener('firefly-iii-bootstrapped', () => {
    console.log('Loaded through event listener.');
    loadPage();
});
// or is bootstrapped before event is triggered.
if (window.bootstrapped) {
    console.log('Loaded through window variable.');
    loadPage();
}
