Directory structure:
└── hunnibear-pmoves-firefly-iii/
    ├── readme.md
    ├── artisan
    ├── composer.json
    ├── COPYING
    ├── crowdin.yml
    ├── index.php
    ├── LICENSE
    ├── nginx_app.conf
    ├── package.json
    ├── phpunit.xml
    ├── Procfile
    ├── releases.md
    ├── server.php
    ├── sonar-project.properties
    ├── temp-file.txt
    ├── THANKS.md
    ├── .editorconfig
    ├── .env.example
    ├── .env.testing
    ├── .htaccess
    ├── app/
    │   ├── User.php
    │   ├── Api/
    │   │   ├── V1/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Controller.php
    │   │   │   │   ├── Autocomplete/
    │   │   │   │   │   ├── AccountController.php
    │   │   │   │   │   ├── BillController.php
    │   │   │   │   │   ├── BudgetController.php
    │   │   │   │   │   ├── CategoryController.php
    │   │   │   │   │   ├── CurrencyController.php
    │   │   │   │   │   ├── ObjectGroupController.php
    │   │   │   │   │   ├── PiggyBankController.php
    │   │   │   │   │   ├── RecurrenceController.php
    │   │   │   │   │   ├── RuleController.php
    │   │   │   │   │   ├── RuleGroupController.php
    │   │   │   │   │   ├── TagController.php
    │   │   │   │   │   ├── TransactionController.php
    │   │   │   │   │   └── TransactionTypeController.php
    │   │   │   │   ├── Chart/
    │   │   │   │   │   ├── AccountController.php
    │   │   │   │   │   ├── BudgetController.php
    │   │   │   │   │   └── CategoryController.php
    │   │   │   │   ├── Data/
    │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   ├── PurgeController.php
    │   │   │   │   │   ├── Bulk/
    │   │   │   │   │   │   └── TransactionController.php
    │   │   │   │   │   └── Export/
    │   │   │   │   │       └── ExportController.php
    │   │   │   │   ├── Insight/
    │   │   │   │   │   ├── Expense/
    │   │   │   │   │   │   ├── AccountController.php
    │   │   │   │   │   │   ├── BillController.php
    │   │   │   │   │   │   ├── BudgetController.php
    │   │   │   │   │   │   ├── CategoryController.php
    │   │   │   │   │   │   ├── PeriodController.php
    │   │   │   │   │   │   └── TagController.php
    │   │   │   │   │   ├── Income/
    │   │   │   │   │   │   ├── AccountController.php
    │   │   │   │   │   │   ├── CategoryController.php
    │   │   │   │   │   │   ├── PeriodController.php
    │   │   │   │   │   │   └── TagController.php
    │   │   │   │   │   └── Transfer/
    │   │   │   │   │       ├── AccountController.php
    │   │   │   │   │       ├── CategoryController.php
    │   │   │   │   │       ├── PeriodController.php
    │   │   │   │   │       └── TagController.php
    │   │   │   │   ├── Models/
    │   │   │   │   │   ├── Account/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Attachment/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── AvailableBudget/
    │   │   │   │   │   │   └── ShowController.php
    │   │   │   │   │   ├── Bill/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Budget/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── BudgetLimit/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Category/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── CurrencyExchangeRate/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── IndexController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── ObjectGroup/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── PiggyBank/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Recurrence/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Rule/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ExpressionController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   ├── TriggerController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── RuleGroup/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   ├── TriggerController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Tag/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── Transaction/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── TransactionCurrency/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── TransactionLink/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   ├── TransactionLinkType/
    │   │   │   │   │   │   ├── DestroyController.php
    │   │   │   │   │   │   ├── ListController.php
    │   │   │   │   │   │   ├── ShowController.php
    │   │   │   │   │   │   ├── StoreController.php
    │   │   │   │   │   │   └── UpdateController.php
    │   │   │   │   │   └── UserGroup/
    │   │   │   │   │       ├── IndexController.php
    │   │   │   │   │       ├── ShowController.php
    │   │   │   │   │       └── UpdateController.php
    │   │   │   │   ├── Search/
    │   │   │   │   │   ├── AccountController.php
    │   │   │   │   │   └── TransactionController.php
    │   │   │   │   ├── Summary/
    │   │   │   │   │   └── BasicController.php
    │   │   │   │   ├── System/
    │   │   │   │   │   ├── AboutController.php
    │   │   │   │   │   ├── ConfigurationController.php
    │   │   │   │   │   ├── CronController.php
    │   │   │   │   │   └── UserController.php
    │   │   │   │   ├── User/
    │   │   │   │   │   └── PreferencesController.php
    │   │   │   │   └── Webhook/
    │   │   │   │       ├── AttemptController.php
    │   │   │   │       ├── DestroyController.php
    │   │   │   │       ├── MessageController.php
    │   │   │   │       ├── ShowController.php
    │   │   │   │       ├── StoreController.php
    │   │   │   │       ├── SubmitController.php
    │   │   │   │       └── UpdateController.php
    │   │   │   ├── Middleware/
    │   │   │   │   └── ApiDemoUser.php
    │   │   │   └── Requests/
    │   │   │       ├── Autocomplete/
    │   │   │       │   └── AutocompleteRequest.php
    │   │   │       ├── Chart/
    │   │   │       │   └── ChartRequest.php
    │   │   │       ├── Data/
    │   │   │       │   ├── DateRequest.php
    │   │   │       │   ├── DestroyRequest.php
    │   │   │       │   ├── SameDateRequest.php
    │   │   │       │   ├── Bulk/
    │   │   │       │   │   ├── MoveTransactionsRequest.php
    │   │   │       │   │   └── TransactionRequest.php
    │   │   │       │   └── Export/
    │   │   │       │       └── ExportRequest.php
    │   │   │       ├── Generic/
    │   │   │       │   ├── DateRequest.php
    │   │   │       │   └── SingleDateRequest.php
    │   │   │       ├── Insight/
    │   │   │       │   └── GenericRequest.php
    │   │   │       ├── Models/
    │   │   │       │   ├── Account/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Attachment/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── AvailableBudget/
    │   │   │       │   │   └── Request.php
    │   │   │       │   ├── Bill/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Budget/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── BudgetLimit/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Category/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── CurrencyExchangeRate/
    │   │   │       │   │   ├── DestroyRequest.php
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── ObjectGroup/
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── PiggyBank/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Recurrence/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Rule/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   ├── TestRequest.php
    │   │   │       │   │   ├── TriggerRequest.php
    │   │   │       │   │   ├── UpdateRequest.php
    │   │   │       │   │   └── ValidateExpressionRequest.php
    │   │   │       │   ├── RuleGroup/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   ├── TestRequest.php
    │   │   │       │   │   ├── TriggerRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Tag/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── Transaction/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── TransactionCurrency/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── TransactionLink/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── TransactionLinkType/
    │   │   │       │   │   ├── StoreRequest.php
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   ├── UserGroup/
    │   │   │       │   │   └── UpdateRequest.php
    │   │   │       │   └── Webhook/
    │   │   │       │       ├── CreateRequest.php
    │   │   │       │       └── UpdateRequest.php
    │   │   │       ├── System/
    │   │   │       │   ├── CronRequest.php
    │   │   │       │   ├── UpdateRequest.php
    │   │   │       │   ├── UserStoreRequest.php
    │   │   │       │   └── UserUpdateRequest.php
    │   │   │       └── User/
    │   │   │           ├── PreferenceStoreRequest.php
    │   │   │           └── PreferenceUpdateRequest.php
    │   │   └── V2/
    │   │       ├── Controllers/
    │   │       │   ├── Controller.php
    │   │       │   ├── Autocomplete/
    │   │       │   │   ├── AccountController.php
    │   │       │   │   ├── CategoryController.php
    │   │       │   │   ├── TagController.php
    │   │       │   │   └── TransactionController.php
    │   │       │   ├── Chart/
    │   │       │   │   ├── AccountController.php
    │   │       │   │   ├── BalanceController.php
    │   │       │   │   ├── BudgetController.php
    │   │       │   │   └── CategoryController.php
    │   │       │   ├── Data/
    │   │       │   │   ├── Bulk/
    │   │       │   │   │   └── AccountController.php
    │   │       │   │   ├── Export/
    │   │       │   │   │   └── AccountController.php
    │   │       │   │   └── MassDestroy/
    │   │       │   │       └── AccountController.php
    │   │       │   ├── Model/
    │   │       │   │   ├── Account/
    │   │       │   │   │   ├── IndexController.php
    │   │       │   │   │   ├── ShowController.php
    │   │       │   │   │   └── UpdateController.php
    │   │       │   │   ├── Bill/
    │   │       │   │   │   ├── IndexController.php
    │   │       │   │   │   ├── ShowController.php
    │   │       │   │   │   └── SumController.php
    │   │       │   │   ├── Budget/
    │   │       │   │   │   ├── IndexController.php
    │   │       │   │   │   ├── ShowController.php
    │   │       │   │   │   └── SumController.php
    │   │       │   │   ├── BudgetLimit/
    │   │       │   │   │   ├── IndexController.php
    │   │       │   │   │   └── ListController.php
    │   │       │   │   ├── Currency/
    │   │       │   │   │   └── IndexController.php
    │   │       │   │   ├── PiggyBank/
    │   │       │   │   │   └── IndexController.php
    │   │       │   │   ├── Transaction/
    │   │       │   │   │   ├── ShowController.php
    │   │       │   │   │   ├── StoreController.php
    │   │       │   │   │   └── UpdateController.php
    │   │       │   │   └── TransactionCurrency/
    │   │       │   │       ├── IndexController.php
    │   │       │   │       └── ShowController.php
    │   │       │   ├── Search/
    │   │       │   │   └── AccountController.php
    │   │       │   ├── Summary/
    │   │       │   │   ├── BasicController.php
    │   │       │   │   └── NetWorthController.php
    │   │       │   ├── System/
    │   │       │   │   ├── ConfigurationController.php
    │   │       │   │   ├── DebugController.php
    │   │       │   │   ├── PreferencesController.php
    │   │       │   │   └── VersionUpdateController.php
    │   │       │   ├── Transaction/
    │   │       │   │   ├── List/
    │   │       │   │   │   ├── AccountController.php
    │   │       │   │   │   └── TransactionController.php
    │   │       │   │   └── Sum/
    │   │       │   │       └── BillController.php
    │   │       │   └── UserGroup/
    │   │       │       ├── DestroyController.php
    │   │       │       ├── IndexController.php
    │   │       │       ├── ShowController.php
    │   │       │       ├── StoreController.php
    │   │       │       └── UpdateController.php
    │   │       ├── Request/
    │   │       │   ├── Autocomplete/
    │   │       │   │   └── AutocompleteRequest.php
    │   │       │   ├── Chart/
    │   │       │   │   ├── BalanceChartRequest.php
    │   │       │   │   ├── ChartRequest.php
    │   │       │   │   └── DashboardChartRequest.php
    │   │       │   ├── Generic/
    │   │       │   │   ├── DateRequest.php
    │   │       │   │   └── SingleDateRequest.php
    │   │       │   ├── Model/
    │   │       │   │   ├── Account/
    │   │       │   │   │   ├── IndexRequest.php
    │   │       │   │   │   └── UpdateRequest.php
    │   │       │   │   ├── Transaction/
    │   │       │   │   │   ├── InfiniteListRequest.php
    │   │       │   │   │   ├── ListRequest.php
    │   │       │   │   │   ├── StoreRequest.php
    │   │       │   │   │   └── UpdateRequest.php
    │   │       │   │   └── TransactionCurrency/
    │   │       │   │       └── IndexRequest.php
    │   │       │   └── UserGroup/
    │   │       │       ├── StoreRequest.php
    │   │       │       ├── UpdateMembershipRequest.php
    │   │       │       └── UpdateRequest.php
    │   │       └── Response/
    │   │           └── Sum/
    │   │               └── AutoSum.php
    │   ├── Casts/
    │   │   └── SeparateTimezoneCaster.php
    │   ├── Console/
    │   │   ├── Kernel.php
    │   │   └── Commands/
    │   │       ├── ShowsFriendlyMessages.php
    │   │       ├── VerifiesAccessToken.php
    │   │       ├── Correction/
    │   │       │   ├── ConvertsDatesToUTC.php
    │   │       │   ├── CorrectionSkeleton.php.stub
    │   │       │   ├── CorrectsAccountOrder.php
    │   │       │   ├── CorrectsAccountTypes.php
    │   │       │   ├── CorrectsAmounts.php
    │   │       │   ├── CorrectsCurrencies.php
    │   │       │   ├── CorrectsDatabase.php
    │   │       │   ├── CorrectsFrontpageAccounts.php
    │   │       │   ├── CorrectsGroupAccounts.php
    │   │       │   ├── CorrectsGroupInformation.php
    │   │       │   ├── CorrectsIbans.php
    │   │       │   ├── CorrectsLongDescriptions.php
    │   │       │   ├── CorrectsMetaDataFields.php
    │   │       │   ├── CorrectsNativeAmounts.php
    │   │       │   ├── CorrectsOpeningBalanceCurrencies.php
    │   │       │   ├── CorrectsPiggyBanks.php
    │   │       │   ├── CorrectsPreferences.php
    │   │       │   ├── CorrectsRecurringTransactions.php
    │   │       │   ├── CorrectsTimezoneInformation.php
    │   │       │   ├── CorrectsTransactionTypes.php
    │   │       │   ├── CorrectsTransferBudgets.php
    │   │       │   ├── CorrectsUnevenAmount.php
    │   │       │   ├── CreatesAccessTokens.php
    │   │       │   ├── CreatesGroupMemberships.php
    │   │       │   ├── CreatesLinkTypes.php
    │   │       │   ├── RemovesBills.php
    │   │       │   ├── RemovesEmptyGroups.php
    │   │       │   ├── RemovesEmptyJournals.php
    │   │       │   ├── RemovesOrphanedTransactions.php
    │   │       │   ├── RemovesZeroAmount.php
    │   │       │   ├── RestoresOAuthKeys.php
    │   │       │   └── TriggersCreditCalculation.php
    │   │       ├── Export/
    │   │       │   └── ExportsData.php
    │   │       ├── Integrity/
    │   │       │   ├── ReportsEmptyObjects.php
    │   │       │   ├── ReportsIntegrity.php
    │   │       │   ├── ReportSkeleton.php.stub
    │   │       │   ├── ReportsSums.php
    │   │       │   └── ValidatesEnvironmentVariables.php
    │   │       ├── System/
    │   │       │   ├── CallsLaravelPassportKeys.php
    │   │       │   ├── CreatesDatabase.php
    │   │       │   ├── CreatesFirstUser.php
    │   │       │   ├── ForcesDecimalSize.php
    │   │       │   ├── ForcesMigrations.php
    │   │       │   ├── OutputsInstructions.php
    │   │       │   ├── OutputsVersion.php
    │   │       │   ├── RecalculatesRunningBalance.php
    │   │       │   ├── ScansAttachments.php
    │   │       │   ├── SetsLatestVersion.php
    │   │       │   └── VerifySecurityAlerts.php
    │   │       ├── Tools/
    │   │       │   ├── ApplyRules.php
    │   │       │   └── Cron.php
    │   │       └── Upgrade/
    │   │           ├── AddsTransactionIdentifiers.php
    │   │           ├── RemovesDatabaseDecryption.php
    │   │           ├── RepairsAccountBalances.php
    │   │           ├── RepairsPostgresSequences.php
    │   │           ├── UpgradesAccountCurrencies.php
    │   │           ├── UpgradesAccountMetaData.php
    │   │           ├── UpgradesAttachments.php
    │   │           ├── UpgradesBillsToRules.php
    │   │           ├── UpgradesBudgetLimitPeriods.php
    │   │           ├── UpgradesBudgetLimits.php
    │   │           ├── UpgradesCreditCardLiabilities.php
    │   │           ├── UpgradesCurrencyPreferences.php
    │   │           ├── UpgradesDatabase.php
    │   │           ├── UpgradesJournalMetaData.php
    │   │           ├── UpgradesJournalNotes.php
    │   │           ├── UpgradeSkeleton.php.stub
    │   │           ├── UpgradesLiabilities.php
    │   │           ├── UpgradesLiabilitiesEight.php
    │   │           ├── UpgradesMultiPiggyBanks.php
    │   │           ├── UpgradesNativeAmounts.php
    │   │           ├── UpgradesRecurrenceMetaData.php
    │   │           ├── UpgradesRuleActions.php
    │   │           ├── UpgradesTagLocations.php
    │   │           ├── UpgradesToGroups.php
    │   │           ├── UpgradesTransferCurrencies.php
    │   │           └── UpgradesVariousCurrencyInformation.php
    │   ├── Entities/
    │   │   └── AccountBalance.php
    │   ├── Enums/
    │   │   ├── AccountTypeEnum.php
    │   │   ├── AutoBudgetType.php
    │   │   ├── ClauseType.php
    │   │   ├── RecurrenceRepetitionWeekend.php
    │   │   ├── SearchDirection.php
    │   │   ├── StringPosition.php
    │   │   ├── TransactionTypeEnum.php
    │   │   ├── UserRoleEnum.php
    │   │   ├── WebhookDelivery.php
    │   │   ├── WebhookResponse.php
    │   │   └── WebhookTrigger.php
    │   ├── Events/
    │   │   ├── ActuallyLoggedIn.php
    │   │   ├── DestroyedTransactionGroup.php
    │   │   ├── DestroyedTransactionLink.php
    │   │   ├── DetectedNewIPAddress.php
    │   │   ├── Event.php
    │   │   ├── NewVersionAvailable.php
    │   │   ├── RegisteredUser.php
    │   │   ├── RequestedNewPassword.php
    │   │   ├── RequestedReportOnJournals.php
    │   │   ├── RequestedSendWebhookMessages.php
    │   │   ├── RequestedVersionCheckStatus.php
    │   │   ├── StoredAccount.php
    │   │   ├── StoredTransactionGroup.php
    │   │   ├── TriggeredAuditLog.php
    │   │   ├── UpdatedAccount.php
    │   │   ├── UpdatedTransactionGroup.php
    │   │   ├── UserChangedEmail.php
    │   │   ├── WarnUserAboutBill.php
    │   │   ├── Admin/
    │   │   │   └── InvitationCreated.php
    │   │   ├── Model/
    │   │   │   ├── Account/
    │   │   │   │   └── Updated.php
    │   │   │   ├── BudgetLimit/
    │   │   │   │   ├── Created.php
    │   │   │   │   ├── Deleted.php
    │   │   │   │   └── Updated.php
    │   │   │   ├── PiggyBank/
    │   │   │   │   ├── ChangedAmount.php
    │   │   │   │   └── ChangedName.php
    │   │   │   └── Rule/
    │   │   │       ├── RuleActionFailedOnArray.php
    │   │   │       └── RuleActionFailedOnObject.php
    │   │   ├── Preferences/
    │   │   │   └── UserGroupChangedDefaultCurrency.php
    │   │   ├── Security/
    │   │   │   ├── DisabledMFA.php
    │   │   │   ├── EnabledMFA.php
    │   │   │   ├── MFABackupFewLeft.php
    │   │   │   ├── MFABackupNoLeft.php
    │   │   │   ├── MFAManyFailedAttempts.php
    │   │   │   ├── MFANewBackupCodes.php
    │   │   │   ├── MFAUsedBackupCode.php
    │   │   │   ├── UnknownUserAttemptedLogin.php
    │   │   │   └── UserAttemptedLogin.php
    │   │   └── Test/
    │   │       ├── OwnerTestNotificationChannel.php
    │   │       └── UserTestNotificationChannel.php
    │   ├── Exceptions/
    │   │   ├── BadHttpHeaderException.php
    │   │   ├── DuplicateTransactionException.php
    │   │   ├── FireflyException.php
    │   │   ├── GracefulNotFoundHandler.php
    │   │   ├── Handler.php
    │   │   ├── IntervalException.php
    │   │   ├── NotImplementedException.php
    │   │   └── ValidationException.php
    │   ├── Factory/
    │   │   ├── AccountFactory.php
    │   │   ├── AccountMetaFactory.php
    │   │   ├── AttachmentFactory.php
    │   │   ├── BillFactory.php
    │   │   ├── BudgetFactory.php
    │   │   ├── CategoryFactory.php
    │   │   ├── PiggyBankEventFactory.php
    │   │   ├── PiggyBankFactory.php
    │   │   ├── RecurrenceFactory.php
    │   │   ├── TagFactory.php
    │   │   ├── TransactionCurrencyFactory.php
    │   │   ├── TransactionFactory.php
    │   │   ├── TransactionGroupFactory.php
    │   │   ├── TransactionJournalFactory.php
    │   │   ├── TransactionJournalMetaFactory.php
    │   │   ├── TransactionTypeFactory.php
    │   │   └── UserGroupFactory.php
    │   ├── Generator/
    │   │   ├── Chart/
    │   │   │   └── Basic/
    │   │   │       ├── ChartJsGenerator.php
    │   │   │       └── GeneratorInterface.php
    │   │   ├── Report/
    │   │   │   ├── ReportGeneratorFactory.php
    │   │   │   ├── ReportGeneratorInterface.php
    │   │   │   ├── Account/
    │   │   │   │   ├── MonthReportGenerator.php
    │   │   │   │   ├── MultiYearReportGenerator.php
    │   │   │   │   └── YearReportGenerator.php
    │   │   │   ├── Audit/
    │   │   │   │   ├── MonthReportGenerator.php
    │   │   │   │   ├── MultiYearReportGenerator.php
    │   │   │   │   └── YearReportGenerator.php
    │   │   │   ├── Budget/
    │   │   │   │   ├── MonthReportGenerator.php
    │   │   │   │   ├── MultiYearReportGenerator.php
    │   │   │   │   └── YearReportGenerator.php
    │   │   │   ├── Category/
    │   │   │   │   ├── MonthReportGenerator.php
    │   │   │   │   ├── MultiYearReportGenerator.php
    │   │   │   │   └── YearReportGenerator.php
    │   │   │   ├── Standard/
    │   │   │   │   ├── MonthReportGenerator.php
    │   │   │   │   ├── MultiYearReportGenerator.php
    │   │   │   │   └── YearReportGenerator.php
    │   │   │   └── Tag/
    │   │   │       ├── MonthReportGenerator.php
    │   │   │       ├── MultiYearReportGenerator.php
    │   │   │       └── YearReportGenerator.php
    │   │   └── Webhook/
    │   │       ├── MessageGeneratorInterface.php
    │   │       └── StandardMessageGenerator.php
    │   ├── Handlers/
    │   │   ├── Events/
    │   │   │   ├── AdminEventHandler.php
    │   │   │   ├── APIEventHandler.php
    │   │   │   ├── AuditEventHandler.php
    │   │   │   ├── AutomationHandler.php
    │   │   │   ├── BillEventHandler.php
    │   │   │   ├── DestroyedGroupEventHandler.php
    │   │   │   ├── PreferencesEventHandler.php
    │   │   │   ├── StoredAccountEventHandler.php
    │   │   │   ├── StoredGroupEventHandler.php
    │   │   │   ├── UpdatedAccountEventHandler.php
    │   │   │   ├── UpdatedGroupEventHandler.php
    │   │   │   ├── UserEventHandler.php
    │   │   │   ├── VersionCheckEventHandler.php
    │   │   │   ├── WebhookEventHandler.php
    │   │   │   ├── Model/
    │   │   │   │   ├── BudgetLimitHandler.php
    │   │   │   │   ├── PiggyBankEventHandler.php
    │   │   │   │   └── RuleHandler.php
    │   │   │   └── Security/
    │   │   │       └── MFAHandler.php
    │   │   └── Observer/
    │   │       ├── AccountObserver.php
    │   │       ├── AttachmentObserver.php
    │   │       ├── AutoBudgetObserver.php
    │   │       ├── AvailableBudgetObserver.php
    │   │       ├── BillObserver.php
    │   │       ├── BudgetLimitObserver.php
    │   │       ├── BudgetObserver.php
    │   │       ├── CategoryObserver.php
    │   │       ├── PiggyBankEventObserver.php
    │   │       ├── PiggyBankObserver.php
    │   │       ├── RecurrenceObserver.php
    │   │       ├── RecurrenceTransactionObserver.php
    │   │       ├── RuleGroupObserver.php
    │   │       ├── RuleObserver.php
    │   │       ├── TagObserver.php
    │   │       ├── TransactionGroupObserver.php
    │   │       ├── TransactionJournalObserver.php
    │   │       ├── TransactionObserver.php
    │   │       ├── WebhookMessageObserver.php
    │   │       └── WebhookObserver.php
    │   ├── Helpers/
    │   │   ├── Attachments/
    │   │   │   ├── AttachmentHelper.php
    │   │   │   └── AttachmentHelperInterface.php
    │   │   ├── Collector/
    │   │   │   ├── GroupCollector.php
    │   │   │   ├── GroupCollectorInterface.php
    │   │   │   └── Extensions/
    │   │   │       ├── AccountCollection.php
    │   │   │       ├── AmountCollection.php
    │   │   │       ├── AttachmentCollection.php
    │   │   │       ├── CollectorProperties.php
    │   │   │       ├── MetaCollection.php
    │   │   │       └── TimeCollection.php
    │   │   ├── Fiscal/
    │   │   │   ├── FiscalHelper.php
    │   │   │   └── FiscalHelperInterface.php
    │   │   ├── Report/
    │   │   │   ├── NetWorth.php
    │   │   │   ├── NetWorthInterface.php
    │   │   │   ├── PopupReport.php
    │   │   │   ├── PopupReportInterface.php
    │   │   │   ├── ReportHelper.php
    │   │   │   └── ReportHelperInterface.php
    │   │   ├── Update/
    │   │   │   └── UpdateTrait.php
    │   │   └── Webhook/
    │   │       ├── Sha3SignatureGenerator.php
    │   │       └── SignatureGeneratorInterface.php
    │   ├── Http/
    │   │   ├── Kernel.php
    │   │   ├── Controllers/
    │   │   │   ├── AttachmentController.php
    │   │   │   ├── Controller.php
    │   │   │   ├── DebugController.php
    │   │   │   ├── HomeController.php
    │   │   │   ├── JavascriptController.php
    │   │   │   ├── NewUserController.php
    │   │   │   ├── PreferencesController.php
    │   │   │   ├── ProfileController.php
    │   │   │   ├── ReportController.php
    │   │   │   ├── SearchController.php
    │   │   │   ├── TagController.php
    │   │   │   ├── Account/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   ├── ReconcileController.php
    │   │   │   │   └── ShowController.php
    │   │   │   ├── Admin/
    │   │   │   │   ├── ConfigurationController.php
    │   │   │   │   ├── HomeController.php
    │   │   │   │   ├── LinkController.php
    │   │   │   │   ├── NotificationController.php
    │   │   │   │   ├── UpdateController.php
    │   │   │   │   └── UserController.php
    │   │   │   ├── Auth/
    │   │   │   │   ├── ConfirmPasswordController.php
    │   │   │   │   ├── ForgotPasswordController.php
    │   │   │   │   ├── LoginController.php
    │   │   │   │   ├── RegisterController.php
    │   │   │   │   ├── ResetPasswordController.php
    │   │   │   │   └── TwoFactorController.php
    │   │   │   ├── Bill/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   └── ShowController.php
    │   │   │   ├── Budget/
    │   │   │   │   ├── BudgetLimitController.php
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   └── ShowController.php
    │   │   │   ├── Category/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   ├── NoCategoryController.php
    │   │   │   │   └── ShowController.php
    │   │   │   ├── Chart/
    │   │   │   │   ├── AccountController.php
    │   │   │   │   ├── BillController.php
    │   │   │   │   ├── BudgetController.php
    │   │   │   │   ├── BudgetReportController.php
    │   │   │   │   ├── CategoryController.php
    │   │   │   │   ├── CategoryReportController.php
    │   │   │   │   ├── DoubleReportController.php
    │   │   │   │   ├── ExpenseReportController.php
    │   │   │   │   ├── PiggyBankController.php
    │   │   │   │   ├── ReportController.php
    │   │   │   │   ├── TagReportController.php
    │   │   │   │   └── TransactionController.php
    │   │   │   ├── ExchangeRates/
    │   │   │   │   └── IndexController.php
    │   │   │   ├── Export/
    │   │   │   │   └── IndexController.php
    │   │   │   ├── Json/
    │   │   │   │   ├── AutoCompleteController.php
    │   │   │   │   ├── BoxController.php
    │   │   │   │   ├── BudgetController.php
    │   │   │   │   ├── FrontpageController.php
    │   │   │   │   ├── IntroController.php
    │   │   │   │   ├── ReconcileController.php
    │   │   │   │   ├── RecurrenceController.php
    │   │   │   │   └── RuleController.php
    │   │   │   ├── ObjectGroup/
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   └── IndexController.php
    │   │   │   ├── PiggyBank/
    │   │   │   │   ├── AmountController.php
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   └── ShowController.php
    │   │   │   ├── Popup/
    │   │   │   │   └── ReportController.php
    │   │   │   ├── Preferences/
    │   │   │   │   └── NotificationsController.php
    │   │   │   ├── Profile/
    │   │   │   │   └── MfaController.php
    │   │   │   ├── Recurring/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   ├── ShowController.php
    │   │   │   │   └── TriggerController.php
    │   │   │   ├── Report/
    │   │   │   │   ├── AccountController.php
    │   │   │   │   ├── BalanceController.php
    │   │   │   │   ├── BillController.php
    │   │   │   │   ├── BudgetController.php
    │   │   │   │   ├── CategoryController.php
    │   │   │   │   ├── DoubleController.php
    │   │   │   │   ├── OperationsController.php
    │   │   │   │   └── TagController.php
    │   │   │   ├── Rule/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   └── SelectController.php
    │   │   │   ├── RuleGroup/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   └── ExecutionController.php
    │   │   │   ├── System/
    │   │   │   │   ├── CronController.php
    │   │   │   │   ├── HealthcheckController.php
    │   │   │   │   └── InstallController.php
    │   │   │   ├── Transaction/
    │   │   │   │   ├── BulkController.php
    │   │   │   │   ├── ConvertController.php
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   ├── IndexController.php
    │   │   │   │   ├── LinkController.php
    │   │   │   │   ├── MassController.php
    │   │   │   │   └── ShowController.php
    │   │   │   ├── TransactionCurrency/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── DeleteController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   └── IndexController.php
    │   │   │   ├── UserGroup/
    │   │   │   │   ├── CreateController.php
    │   │   │   │   ├── EditController.php
    │   │   │   │   └── IndexController.php
    │   │   │   └── Webhooks/
    │   │   │       ├── CreateController.php
    │   │   │       ├── DeleteController.php
    │   │   │       ├── EditController.php
    │   │   │       ├── IndexController.php
    │   │   │       └── ShowController.php
    │   │   ├── Middleware/
    │   │   │   ├── AcceptHeaders.php
    │   │   │   ├── Authenticate.php
    │   │   │   ├── Binder.php
    │   │   │   ├── EncryptCookies.php
    │   │   │   ├── InstallationId.php
    │   │   │   ├── Installer.php
    │   │   │   ├── InterestingMessage.php
    │   │   │   ├── IsAdmin.php
    │   │   │   ├── IsDemoUser.php
    │   │   │   ├── Range.php
    │   │   │   ├── RedirectIfAuthenticated.php
    │   │   │   ├── SecureHeaders.php
    │   │   │   ├── StartFireflySession.php
    │   │   │   ├── TrimStrings.php
    │   │   │   ├── TrustHosts.php
    │   │   │   ├── TrustProxies.php
    │   │   │   └── VerifyCsrfToken.php
    │   │   └── Requests/
    │   │       ├── AccountFormRequest.php
    │   │       ├── AttachmentFormRequest.php
    │   │       ├── BillStoreRequest.php
    │   │       ├── BillUpdateRequest.php
    │   │       ├── BudgetFormStoreRequest.php
    │   │       ├── BudgetFormUpdateRequest.php
    │   │       ├── BudgetIncomeRequest.php
    │   │       ├── BulkEditJournalRequest.php
    │   │       ├── CategoryFormRequest.php
    │   │       ├── ConfigurationRequest.php
    │   │       ├── CurrencyFormRequest.php
    │   │       ├── DeleteAccountFormRequest.php
    │   │       ├── EmailFormRequest.php
    │   │       ├── ExistingTokenFormRequest.php
    │   │       ├── InviteUserFormRequest.php
    │   │       ├── JournalLinkRequest.php
    │   │       ├── LinkTypeFormRequest.php
    │   │       ├── MassDeleteJournalRequest.php
    │   │       ├── MassEditJournalRequest.php
    │   │       ├── NewUserFormRequest.php
    │   │       ├── NotificationRequest.php
    │   │       ├── ObjectGroupFormRequest.php
    │   │       ├── PiggyBankStoreRequest.php
    │   │       ├── PiggyBankUpdateRequest.php
    │   │       ├── PreferencesRequest.php
    │   │       ├── ProfileFormRequest.php
    │   │       ├── ReconciliationStoreRequest.php
    │   │       ├── RecurrenceFormRequest.php
    │   │       ├── ReportFormRequest.php
    │   │       ├── RuleFormRequest.php
    │   │       ├── RuleGroupFormRequest.php
    │   │       ├── SelectTransactionsRequest.php
    │   │       ├── TagFormRequest.php
    │   │       ├── TestRuleFormRequest.php
    │   │       ├── TokenFormRequest.php
    │   │       ├── TriggerRecurrenceRequest.php
    │   │       ├── UserFormRequest.php
    │   │       └── UserRegistrationRequest.php
    │   ├── Jobs/
    │   │   ├── CreateAutoBudgetLimits.php
    │   │   ├── CreateRecurringTransactions.php
    │   │   ├── DownloadExchangeRates.php
    │   │   ├── Job.php
    │   │   ├── MailError.php
    │   │   ├── SendWebhookMessage.php
    │   │   └── WarnAboutBills.php
    │   ├── Listeners/
    │   │   └── .gitkeep
    │   ├── Mail/
    │   │   ├── AccessTokenCreatedMail.php
    │   │   ├── AdminTestMail.php
    │   │   ├── BillWarningMail.php
    │   │   ├── ConfirmEmailChangeMail.php
    │   │   ├── InvitationMail.php
    │   │   ├── NewIPAddressWarningMail.php
    │   │   ├── OAuthTokenCreatedMail.php
    │   │   ├── RegisteredUser.php
    │   │   ├── ReportNewJournalsMail.php
    │   │   ├── RequestedNewPassword.php
    │   │   └── UndoEmailChangeMail.php
    │   ├── Models/
    │   │   ├── Account.php
    │   │   ├── AccountBalance.php
    │   │   ├── AccountMeta.php
    │   │   ├── AccountType.php
    │   │   ├── Attachment.php
    │   │   ├── AuditLogEntry.php
    │   │   ├── AutoBudget.php
    │   │   ├── AvailableBudget.php
    │   │   ├── Bill.php
    │   │   ├── Budget.php
    │   │   ├── BudgetLimit.php
    │   │   ├── Category.php
    │   │   ├── Configuration.php
    │   │   ├── CurrencyExchangeRate.php
    │   │   ├── GroupMembership.php
    │   │   ├── InvitedUser.php
    │   │   ├── LinkType.php
    │   │   ├── Location.php
    │   │   ├── Note.php
    │   │   ├── ObjectGroup.php
    │   │   ├── PiggyBank.php
    │   │   ├── PiggyBankEvent.php
    │   │   ├── PiggyBankRepetition.php
    │   │   ├── Preference.php
    │   │   ├── Recurrence.php
    │   │   ├── RecurrenceMeta.php
    │   │   ├── RecurrenceRepetition.php
    │   │   ├── RecurrenceTransaction.php
    │   │   ├── RecurrenceTransactionMeta.php
    │   │   ├── Role.php
    │   │   ├── Rule.php
    │   │   ├── RuleAction.php
    │   │   ├── RuleGroup.php
    │   │   ├── RuleTrigger.php
    │   │   ├── Tag.php
    │   │   ├── Transaction.php
    │   │   ├── TransactionCurrency.php
    │   │   ├── TransactionGroup.php
    │   │   ├── TransactionJournal.php
    │   │   ├── TransactionJournalLink.php
    │   │   ├── TransactionJournalMeta.php
    │   │   ├── TransactionType.php
    │   │   ├── UserGroup.php
    │   │   ├── UserRole.php
    │   │   ├── Webhook.php
    │   │   ├── WebhookAttempt.php
    │   │   └── WebhookMessage.php
    │   ├── Notifications/
    │   │   ├── ReturnsAvailableChannels.php
    │   │   ├── ReturnsSettings.php
    │   │   ├── Admin/
    │   │   │   ├── UnknownUserLoginAttempt.php
    │   │   │   ├── UserInvitation.php
    │   │   │   ├── UserRegistration.php
    │   │   │   └── VersionCheckResult.php
    │   │   ├── Notifiables/
    │   │   │   └── OwnerNotifiable.php
    │   │   ├── Security/
    │   │   │   ├── DisabledMFANotification.php
    │   │   │   ├── EnabledMFANotification.php
    │   │   │   ├── MFABackupFewLeftNotification.php
    │   │   │   ├── MFABackupNoLeftNotification.php
    │   │   │   ├── MFAManyFailedAttemptsNotification.php
    │   │   │   ├── MFAUsedBackupCodeNotification.php
    │   │   │   ├── NewBackupCodesNotification.php
    │   │   │   └── UserFailedLoginAttempt.php
    │   │   ├── Test/
    │   │   │   ├── OwnerTestNotificationEmail.php
    │   │   │   ├── OwnerTestNotificationNtfy.php
    │   │   │   ├── OwnerTestNotificationPushover.php
    │   │   │   ├── OwnerTestNotificationSlack.php
    │   │   │   ├── UserTestNotificationEmail.php
    │   │   │   ├── UserTestNotificationNtfy.php
    │   │   │   ├── UserTestNotificationPushover.php
    │   │   │   └── UserTestNotificationSlack.php
    │   │   └── User/
    │   │       ├── BillReminder.php
    │   │       ├── NewAccessToken.php
    │   │       ├── RuleActionFailed.php
    │   │       ├── TransactionCreation.php
    │   │       ├── UserLogin.php
    │   │       ├── UserNewPassword.php
    │   │       └── UserRegistration.php
    │   ├── Policies/
    │   │   ├── AccountBalancePolicy.php
    │   │   ├── AccountPolicy.php
    │   │   ├── BalancePolicy.php
    │   │   ├── UserPolicy.php
    │   │   └── .gitkeep
    │   ├── Providers/
    │   │   ├── AccountServiceProvider.php
    │   │   ├── AdminServiceProvider.php
    │   │   ├── AppServiceProvider.php
    │   │   ├── AttachmentServiceProvider.php
    │   │   ├── AuthServiceProvider.php
    │   │   ├── BillServiceProvider.php
    │   │   ├── BroadcastServiceProvider.php
    │   │   ├── BudgetServiceProvider.php
    │   │   ├── CategoryServiceProvider.php
    │   │   ├── CurrencyServiceProvider.php
    │   │   ├── EventServiceProvider.php
    │   │   ├── FireflyServiceProvider.php
    │   │   ├── FireflySessionProvider.php
    │   │   ├── JournalServiceProvider.php
    │   │   ├── PiggyBankServiceProvider.php
    │   │   ├── RecurringServiceProvider.php
    │   │   ├── RouteServiceProvider.php
    │   │   ├── RuleGroupServiceProvider.php
    │   │   ├── RuleServiceProvider.php
    │   │   ├── SearchServiceProvider.php
    │   │   ├── SessionServiceProvider.php
    │   │   └── TagServiceProvider.php
    │   ├── Repositories/
    │   │   ├── Account/
    │   │   │   ├── AccountRepository.php
    │   │   │   ├── AccountRepositoryInterface.php
    │   │   │   ├── AccountTasker.php
    │   │   │   ├── AccountTaskerInterface.php
    │   │   │   ├── OperationsRepository.php
    │   │   │   └── OperationsRepositoryInterface.php
    │   │   ├── Attachment/
    │   │   │   ├── AttachmentRepository.php
    │   │   │   └── AttachmentRepositoryInterface.php
    │   │   ├── AuditLogEntry/
    │   │   │   ├── ALERepository.php
    │   │   │   └── ALERepositoryInterface.php
    │   │   ├── Bill/
    │   │   │   ├── BillRepository.php
    │   │   │   └── BillRepositoryInterface.php
    │   │   ├── Budget/
    │   │   │   ├── AvailableBudgetRepository.php
    │   │   │   ├── AvailableBudgetRepositoryInterface.php
    │   │   │   ├── BudgetLimitRepository.php
    │   │   │   ├── BudgetLimitRepositoryInterface.php
    │   │   │   ├── BudgetRepository.php
    │   │   │   ├── BudgetRepositoryInterface.php
    │   │   │   ├── NoBudgetRepository.php
    │   │   │   ├── NoBudgetRepositoryInterface.php
    │   │   │   ├── OperationsRepository.php
    │   │   │   └── OperationsRepositoryInterface.php
    │   │   ├── Category/
    │   │   │   ├── CategoryRepository.php
    │   │   │   ├── CategoryRepositoryInterface.php
    │   │   │   ├── NoCategoryRepository.php
    │   │   │   ├── NoCategoryRepositoryInterface.php
    │   │   │   ├── OperationsRepository.php
    │   │   │   └── OperationsRepositoryInterface.php
    │   │   ├── Currency/
    │   │   │   ├── CurrencyRepository.php
    │   │   │   └── CurrencyRepositoryInterface.php
    │   │   ├── ExchangeRate/
    │   │   │   ├── ExchangeRateRepository.php
    │   │   │   └── ExchangeRateRepositoryInterface.php
    │   │   ├── Journal/
    │   │   │   ├── JournalAPIRepository.php
    │   │   │   ├── JournalAPIRepositoryInterface.php
    │   │   │   ├── JournalCLIRepository.php
    │   │   │   ├── JournalCLIRepositoryInterface.php
    │   │   │   ├── JournalRepository.php
    │   │   │   └── JournalRepositoryInterface.php
    │   │   ├── LinkType/
    │   │   │   ├── LinkTypeRepository.php
    │   │   │   └── LinkTypeRepositoryInterface.php
    │   │   ├── ObjectGroup/
    │   │   │   ├── CreatesObjectGroups.php
    │   │   │   ├── ObjectGroupRepository.php
    │   │   │   ├── ObjectGroupRepositoryInterface.php
    │   │   │   └── OrganisesObjectGroups.php
    │   │   ├── PiggyBank/
    │   │   │   ├── ModifiesPiggyBanks.php
    │   │   │   ├── PiggyBankRepository.php
    │   │   │   └── PiggyBankRepositoryInterface.php
    │   │   ├── Recurring/
    │   │   │   ├── RecurringRepository.php
    │   │   │   └── RecurringRepositoryInterface.php
    │   │   ├── Rule/
    │   │   │   ├── RuleRepository.php
    │   │   │   └── RuleRepositoryInterface.php
    │   │   ├── RuleGroup/
    │   │   │   ├── RuleGroupRepository.php
    │   │   │   └── RuleGroupRepositoryInterface.php
    │   │   ├── Tag/
    │   │   │   ├── OperationsRepository.php
    │   │   │   ├── OperationsRepositoryInterface.php
    │   │   │   ├── TagRepository.php
    │   │   │   └── TagRepositoryInterface.php
    │   │   ├── TransactionGroup/
    │   │   │   ├── TransactionGroupRepository.php
    │   │   │   └── TransactionGroupRepositoryInterface.php
    │   │   ├── TransactionType/
    │   │   │   ├── TransactionTypeRepository.php
    │   │   │   └── TransactionTypeRepositoryInterface.php
    │   │   ├── User/
    │   │   │   ├── UserRepository.php
    │   │   │   └── UserRepositoryInterface.php
    │   │   ├── UserGroup/
    │   │   │   ├── UserGroupRepository.php
    │   │   │   └── UserGroupRepositoryInterface.php
    │   │   ├── UserGroups/
    │   │   │   ├── Account/
    │   │   │   │   ├── AccountRepository.php
    │   │   │   │   └── AccountRepositoryInterface.php
    │   │   │   ├── Bill/
    │   │   │   │   ├── BillRepository.php
    │   │   │   │   └── BillRepositoryInterface.php
    │   │   │   ├── Budget/
    │   │   │   │   ├── AvailableBudgetRepository.php
    │   │   │   │   ├── AvailableBudgetRepositoryInterface.php
    │   │   │   │   ├── BudgetRepository.php
    │   │   │   │   ├── BudgetRepositoryInterface.php
    │   │   │   │   ├── OperationsRepository.php
    │   │   │   │   └── OperationsRepositoryInterface.php
    │   │   │   ├── Category/
    │   │   │   │   ├── CategoryRepository.php
    │   │   │   │   └── CategoryRepositoryInterface.php
    │   │   │   ├── Currency/
    │   │   │   │   ├── CurrencyRepository.php
    │   │   │   │   └── CurrencyRepositoryInterface.php
    │   │   │   ├── ExchangeRate/
    │   │   │   │   ├── ExchangeRateRepository.php
    │   │   │   │   └── ExchangeRateRepositoryInterface.php
    │   │   │   ├── Journal/
    │   │   │   │   ├── JournalRepository.php
    │   │   │   │   └── JournalRepositoryInterface.php
    │   │   │   ├── PiggyBank/
    │   │   │   │   ├── PiggyBankRepository.php
    │   │   │   │   └── PiggyBankRepositoryInterface.php
    │   │   │   └── Tag/
    │   │   │       ├── TagRepository.php
    │   │   │       └── TagRepositoryInterface.php
    │   │   └── Webhook/
    │   │       ├── WebhookRepository.php
    │   │       └── WebhookRepositoryInterface.php
    │   ├── Rules/
    │   │   ├── BelongsUser.php
    │   │   ├── BelongsUserGroup.php
    │   │   ├── IsAllowedGroupAction.php
    │   │   ├── IsAssetAccountId.php
    │   │   ├── IsBoolean.php
    │   │   ├── IsDateOrTime.php
    │   │   ├── IsDefaultUserGroupName.php
    │   │   ├── IsDuplicateTransaction.php
    │   │   ├── IsFilterValueIn.php
    │   │   ├── IsTransferAccount.php
    │   │   ├── IsValidActionExpression.php
    │   │   ├── IsValidAmount.php
    │   │   ├── IsValidAttachmentModel.php
    │   │   ├── IsValidBulkClause.php
    │   │   ├── IsValidDateRange.php
    │   │   ├── IsValidPositiveAmount.php
    │   │   ├── IsValidZeroOrMoreAmount.php
    │   │   ├── LessThanPiggyTarget.php
    │   │   ├── UniqueAccountNumber.php
    │   │   ├── UniqueIban.php
    │   │   ├── ValidJournals.php
    │   │   ├── ValidRecurrenceRepetitionType.php
    │   │   ├── ValidRecurrenceRepetitionValue.php
    │   │   ├── Account/
    │   │   │   └── IsValidAccountType.php
    │   │   └── Admin/
    │   │       ├── IsValidDiscordUrl.php
    │   │       ├── IsValidSlackOrDiscordUrl.php
    │   │       └── IsValidSlackUrl.php
    │   ├── Services/
    │   │   ├── FireflyIIIOrg/
    │   │   │   └── Update/
    │   │   │       ├── UpdateRequest.php
    │   │   │       └── UpdateRequestInterface.php
    │   │   ├── Internal/
    │   │   │   ├── Destroy/
    │   │   │   │   ├── AccountDestroyService.php
    │   │   │   │   ├── BillDestroyService.php
    │   │   │   │   ├── BudgetDestroyService.php
    │   │   │   │   ├── CategoryDestroyService.php
    │   │   │   │   ├── CurrencyDestroyService.php
    │   │   │   │   ├── JournalDestroyService.php
    │   │   │   │   ├── RecurrenceDestroyService.php
    │   │   │   │   └── TransactionGroupDestroyService.php
    │   │   │   ├── Support/
    │   │   │   │   ├── AccountServiceTrait.php
    │   │   │   │   ├── BillServiceTrait.php
    │   │   │   │   ├── CreditRecalculateService.php
    │   │   │   │   ├── JournalServiceTrait.php
    │   │   │   │   ├── LocationServiceTrait.php
    │   │   │   │   ├── RecurringTransactionTrait.php
    │   │   │   │   └── TransactionTypeTrait.php
    │   │   │   └── Update/
    │   │   │       ├── AccountUpdateService.php
    │   │   │       ├── BillUpdateService.php
    │   │   │       ├── CategoryUpdateService.php
    │   │   │       ├── CurrencyUpdateService.php
    │   │   │       ├── GroupCloneService.php
    │   │   │       ├── GroupUpdateService.php
    │   │   │       ├── JournalUpdateService.php
    │   │   │       └── RecurrenceUpdateService.php
    │   │   ├── Password/
    │   │   │   ├── PwndVerifierV2.php
    │   │   │   └── Verifier.php
    │   │   └── Webhook/
    │   │       ├── StandardWebhookSender.php
    │   │       └── WebhookSenderInterface.php
    │   ├── Support/
    │   │   ├── Amount.php
    │   │   ├── Balance.php
    │   │   ├── CacheProperties.php
    │   │   ├── ChartColour.php
    │   │   ├── Domain.php
    │   │   ├── ExpandedForm.php
    │   │   ├── FireflyConfig.php
    │   │   ├── Navigation.php
    │   │   ├── NullArrayObject.php
    │   │   ├── ParseDateString.php
    │   │   ├── Preferences.php
    │   │   ├── Steam.php
    │   │   ├── Authentication/
    │   │   │   ├── RemoteUserGuard.php
    │   │   │   └── RemoteUserProvider.php
    │   │   ├── Binder/
    │   │   │   ├── AccountList.php
    │   │   │   ├── BinderInterface.php
    │   │   │   ├── BudgetList.php
    │   │   │   ├── CategoryList.php
    │   │   │   ├── CLIToken.php
    │   │   │   ├── CurrencyCode.php
    │   │   │   ├── Date.php
    │   │   │   ├── DynamicConfigKey.php
    │   │   │   ├── EitherConfigKey.php
    │   │   │   ├── JournalList.php
    │   │   │   ├── TagList.php
    │   │   │   ├── TagOrId.php
    │   │   │   ├── UserGroupAccount.php
    │   │   │   ├── UserGroupBill.php
    │   │   │   ├── UserGroupExchangeRate.php
    │   │   │   └── UserGroupTransaction.php
    │   │   ├── Calendar/
    │   │   │   ├── Calculator.php
    │   │   │   ├── Periodicity.php
    │   │   │   └── Periodicity/
    │   │   │       ├── Bimonthly.php
    │   │   │       ├── Daily.php
    │   │   │       ├── Fortnightly.php
    │   │   │       ├── HalfYearly.php
    │   │   │       ├── Interspacable.php
    │   │   │       ├── Interval.php
    │   │   │       ├── Monthly.php
    │   │   │       ├── Quarterly.php
    │   │   │       ├── Weekly.php
    │   │   │       └── Yearly.php
    │   │   ├── Chart/
    │   │   │   ├── ChartData.php
    │   │   │   ├── Budget/
    │   │   │   │   └── FrontpageChartGenerator.php
    │   │   │   └── Category/
    │   │   │       ├── FrontpageChartGenerator.php
    │   │   │       └── WholePeriodChartGenerator.php
    │   │   ├── Cronjobs/
    │   │   │   ├── AbstractCronjob.php
    │   │   │   ├── AutoBudgetCronjob.php
    │   │   │   ├── BillWarningCronjob.php
    │   │   │   ├── ExchangeRatesCronjob.php
    │   │   │   ├── RecurringCronjob.php
    │   │   │   └── UpdateCheckCronjob.php
    │   │   ├── Debug/
    │   │   │   └── Timer.php
    │   │   ├── Export/
    │   │   │   └── ExportDataGenerator.php
    │   │   ├── Facades/
    │   │   │   ├── AccountForm.php
    │   │   │   ├── Amount.php
    │   │   │   ├── Balance.php
    │   │   │   ├── CurrencyForm.php
    │   │   │   ├── ExpandedForm.php
    │   │   │   ├── FireflyConfig.php
    │   │   │   ├── Navigation.php
    │   │   │   ├── PiggyBankForm.php
    │   │   │   ├── Preferences.php
    │   │   │   ├── RuleForm.php
    │   │   │   └── Steam.php
    │   │   ├── Form/
    │   │   │   ├── AccountForm.php
    │   │   │   ├── CurrencyForm.php
    │   │   │   ├── FormSupport.php
    │   │   │   ├── PiggyBankForm.php
    │   │   │   └── RuleForm.php
    │   │   ├── Http/
    │   │   │   ├── Api/
    │   │   │   │   ├── AccountBalanceGrouped.php
    │   │   │   │   ├── AccountFilter.php
    │   │   │   │   ├── ApiSupport.php
    │   │   │   │   ├── CleansChartData.php
    │   │   │   │   ├── CollectsAccountsFromFilter.php
    │   │   │   │   ├── ExchangeRateConverter.php
    │   │   │   │   ├── SummaryBalanceGrouped.php
    │   │   │   │   ├── TransactionFilter.php
    │   │   │   │   └── ValidatesUserGroupTrait.php
    │   │   │   └── Controllers/
    │   │   │       ├── AugumentData.php
    │   │   │       ├── BasicDataSupport.php
    │   │   │       ├── ChartGeneration.php
    │   │   │       ├── CreateStuff.php
    │   │   │       ├── CronRunner.php
    │   │   │       ├── DateCalculation.php
    │   │   │       ├── GetConfigurationData.php
    │   │   │       ├── ModelInformation.php
    │   │   │       ├── PeriodOverview.php
    │   │   │       ├── RenderPartialViews.php
    │   │   │       ├── RequestInformation.php
    │   │   │       ├── RuleManagement.php
    │   │   │       ├── TransactionCalculation.php
    │   │   │       └── UserNavigation.php
    │   │   ├── JsonApi/
    │   │   │   └── Enrichments/
    │   │   │       ├── AccountEnrichment.php
    │   │   │       ├── EnrichmentInterface.php
    │   │   │       └── TransactionGroupEnrichment.php
    │   │   ├── Logging/
    │   │   │   ├── AuditLogger.php
    │   │   │   └── AuditProcessor.php
    │   │   ├── Models/
    │   │   │   ├── AccountBalanceCalculator.php
    │   │   │   ├── BillDateCalculator.php
    │   │   │   ├── ReturnsIntegerIdTrait.php
    │   │   │   └── ReturnsIntegerUserIdTrait.php
    │   │   ├── Notifications/
    │   │   │   └── UrlValidator.php
    │   │   ├── Report/
    │   │   │   ├── Budget/
    │   │   │   │   └── BudgetReportGenerator.php
    │   │   │   ├── Category/
    │   │   │   │   └── CategoryReportGenerator.php
    │   │   │   └── Summarizer/
    │   │   │       └── TransactionSummarizer.php
    │   │   ├── Repositories/
    │   │   │   ├── Recurring/
    │   │   │   │   ├── CalculateRangeOccurrences.php
    │   │   │   │   ├── CalculateXOccurrences.php
    │   │   │   │   ├── CalculateXOccurrencesSince.php
    │   │   │   │   └── FiltersWeekends.php
    │   │   │   └── UserGroup/
    │   │   │       ├── UserGroupInterface.php
    │   │   │       └── UserGroupTrait.php
    │   │   ├── Request/
    │   │   │   ├── AppendsLocationData.php
    │   │   │   ├── ChecksLogin.php
    │   │   │   ├── ConvertsDataTypes.php
    │   │   │   ├── GetFilterInstructions.php
    │   │   │   ├── GetRecurrenceData.php
    │   │   │   ├── GetRuleConfiguration.php
    │   │   │   └── GetSortInstructions.php
    │   │   ├── Search/
    │   │   │   ├── AccountSearch.php
    │   │   │   ├── GenericSearchInterface.php
    │   │   │   ├── SearchInterface.php
    │   │   │   └── QueryParser/
    │   │   │       ├── FieldNode.php
    │   │   │       ├── GdbotsQueryParser.php
    │   │   │       ├── Node.php
    │   │   │       ├── NodeGroup.php
    │   │   │       ├── NodeResult.php
    │   │   │       ├── QueryParser.php
    │   │   │       ├── QueryParserInterface.php
    │   │   │       └── StringNode.php
    │   │   ├── System/
    │   │   │   ├── GeneratesInstallationId.php
    │   │   │   └── OAuthKeys.php
    │   │   ├── Twig/
    │   │   │   ├── AmountFormat.php
    │   │   │   ├── General.php
    │   │   │   ├── Rule.php
    │   │   │   ├── TransactionGroupTwig.php
    │   │   │   └── Translation.php
    │   │   └── Validation/
    │   │       └── ValidatesAmountsTrait.php
    │   ├── TransactionRules/
    │   │   ├── Actions/
    │   │   │   ├── ActionInterface.php
    │   │   │   ├── AddTag.php
    │   │   │   ├── AppendDescription.php
    │   │   │   ├── AppendDescriptionToNotes.php
    │   │   │   ├── AppendNotes.php
    │   │   │   ├── AppendNotesToDescription.php
    │   │   │   ├── ClearBudget.php
    │   │   │   ├── ClearCategory.php
    │   │   │   ├── ClearNotes.php
    │   │   │   ├── ConvertToDeposit.php
    │   │   │   ├── ConvertToTransfer.php
    │   │   │   ├── ConvertToWithdrawal.php
    │   │   │   ├── DeleteTransaction.php
    │   │   │   ├── LinkToBill.php
    │   │   │   ├── MoveDescriptionToNotes.php
    │   │   │   ├── MoveNotesToDescription.php
    │   │   │   ├── PrependDescription.php
    │   │   │   ├── PrependNotes.php
    │   │   │   ├── RemoveAllTags.php
    │   │   │   ├── RemoveTag.php
    │   │   │   ├── SetAmount.php
    │   │   │   ├── SetBudget.php
    │   │   │   ├── SetCategory.php
    │   │   │   ├── SetDescription.php
    │   │   │   ├── SetDestinationAccount.php
    │   │   │   ├── SetDestinationToCashAccount.php
    │   │   │   ├── SetNotes.php
    │   │   │   ├── SetSourceAccount.php
    │   │   │   ├── SetSourceToCashAccount.php
    │   │   │   ├── SwitchAccounts.php
    │   │   │   └── UpdatePiggyBank.php
    │   │   ├── Engine/
    │   │   │   ├── RuleEngineInterface.php
    │   │   │   └── SearchRuleEngine.php
    │   │   ├── Expressions/
    │   │   │   ├── ActionExpression.php
    │   │   │   └── ActionExpressionLanguageProvider.php
    │   │   ├── Factory/
    │   │   │   └── ActionFactory.php
    │   │   └── Traits/
    │   │       └── RefreshNotesTrait.php
    │   ├── Transformers/
    │   │   ├── AbstractTransformer.php
    │   │   ├── AccountTransformer.php
    │   │   ├── AttachmentTransformer.php
    │   │   ├── AvailableBudgetTransformer.php
    │   │   ├── BillTransformer.php
    │   │   ├── BudgetLimitTransformer.php
    │   │   ├── BudgetTransformer.php
    │   │   ├── CategoryTransformer.php
    │   │   ├── CurrencyTransformer.php
    │   │   ├── ExchangeRateTransformer.php
    │   │   ├── LinkTypeTransformer.php
    │   │   ├── ObjectGroupTransformer.php
    │   │   ├── PiggyBankEventTransformer.php
    │   │   ├── PiggyBankTransformer.php
    │   │   ├── PreferenceTransformer.php
    │   │   ├── RecurrenceTransformer.php
    │   │   ├── RuleGroupTransformer.php
    │   │   ├── RuleTransformer.php
    │   │   ├── TagTransformer.php
    │   │   ├── TransactionGroupTransformer.php
    │   │   ├── TransactionLinkTransformer.php
    │   │   ├── UserGroupTransformer.php
    │   │   ├── UserTransformer.php
    │   │   ├── WebhookAttemptTransformer.php
    │   │   ├── WebhookMessageTransformer.php
    │   │   ├── WebhookTransformer.php
    │   │   └── V2/
    │   │       ├── AbstractTransformer.php
    │   │       ├── AccountTransformer.php
    │   │       ├── BillTransformer.php
    │   │       ├── BudgetLimitTransformer.php
    │   │       ├── BudgetTransformer.php
    │   │       ├── CurrencyTransformer.php
    │   │       ├── ExchangeRateTransformer.php
    │   │       ├── PiggyBankTransformer.php
    │   │       ├── PreferenceTransformer.php
    │   │       ├── TransactionGroupTransformer.php
    │   │       └── UserGroupTransformer.php
    │   └── Validation/
    │       ├── AccountValidator.php
    │       ├── CurrencyValidation.php
    │       ├── FireflyValidator.php
    │       ├── GroupValidation.php
    │       ├── RecurrenceValidation.php
    │       ├── TransactionValidation.php
    │       ├── Account/
    │       │   ├── DepositValidation.php
    │       │   ├── LiabilityValidation.php
    │       │   ├── OBValidation.php
    │       │   ├── ReconciliationValidation.php
    │       │   ├── TransferValidation.php
    │       │   └── WithdrawalValidation.php
    │       ├── Api/
    │       │   └── Data/
    │       │       └── Bulk/
    │       │           └── ValidatesBulkTransactionQuery.php
    │       └── AutoBudget/
    │           └── ValidatesAutoBudgetRequest.php
    ├── bootstrap/
    │   └── app.php
    ├── config/
    │   ├── api.php
    │   ├── app.php
    │   ├── auth.php
    │   ├── bindables.php
    │   ├── breadcrumbs.php
    │   ├── broadcasting.php
    │   ├── bulk.php
    │   ├── cache.php
    │   ├── cer.php
    │   ├── cors.php
    │   ├── database.php
    │   ├── debugbar.php
    │   ├── filesystems.php
    │   ├── firefly.php
    │   ├── google2fa.php
    │   ├── hashing.php
    │   ├── ide-helper.php
    │   ├── intro.php
    │   ├── laravel-model-caching.php
    │   ├── logging.php
    │   ├── mail.php
    │   ├── notifications.php
    │   ├── ntfy-notification-channel.php
    │   ├── passport.php
    │   ├── queue.php
    │   ├── sanctum.php
    │   ├── search.php
    │   ├── services.php
    │   ├── session.php
    │   ├── translations.php
    │   ├── twigbridge.php
    │   ├── upgrade.php
    │   ├── user_roles.php
    │   └── view.php
    ├── database/
    │   ├── migrations/
    │   │   ├── 2016_06_16_000000_create_support_tables.php
    │   │   ├── 2016_06_16_000001_create_users_table.php
    │   │   ├── 2016_06_16_000002_create_main_tables.php
    │   │   ├── 2016_08_25_091522_changes_for_3101.php
    │   │   ├── 2016_09_12_121359_fix_nullables.php
    │   │   ├── 2016_10_09_150037_expand_transactions_table.php
    │   │   ├── 2016_10_22_075804_changes_for_v410.php
    │   │   ├── 2016_11_24_210552_changes_for_v420.php
    │   │   ├── 2016_12_22_150431_changes_for_v430.php
    │   │   ├── 2016_12_28_203205_changes_for_v431.php
    │   │   ├── 2017_04_13_163623_changes_for_v440.php
    │   │   ├── 2017_06_02_105232_changes_for_v450.php
    │   │   ├── 2017_08_20_062014_changes_for_v470.php
    │   │   ├── 2017_11_04_170844_changes_for_v470a.php
    │   │   ├── 2018_01_01_000001_create_oauth_auth_codes_table.php
    │   │   ├── 2018_01_01_000002_create_oauth_access_tokens_table.php
    │   │   ├── 2018_01_01_000003_create_oauth_refresh_tokens_table.php
    │   │   ├── 2018_01_01_000004_create_oauth_clients_table.php
    │   │   ├── 2018_01_01_000005_create_oauth_personal_access_clients_table.php
    │   │   ├── 2018_03_19_141348_changes_for_v472.php
    │   │   ├── 2018_04_07_210913_changes_for_v473.php
    │   │   ├── 2018_04_29_174524_changes_for_v474.php
    │   │   ├── 2018_06_08_200526_changes_for_v475.php
    │   │   ├── 2018_09_05_195147_changes_for_v477.php
    │   │   ├── 2018_11_06_172532_changes_for_v479.php
    │   │   ├── 2019_01_28_193833_changes_for_v4710.php
    │   │   ├── 2019_02_05_055516_changes_for_v4711.php
    │   │   ├── 2019_02_11_170529_changes_for_v4712.php
    │   │   ├── 2019_03_11_223700_fix_ldap_configuration.php
    │   │   ├── 2019_03_22_183214_changes_for_v480.php
    │   │   ├── 2019_12_28_191351_make_locations_table.php
    │   │   ├── 2020_03_13_201950_changes_for_v520.php
    │   │   ├── 2020_06_07_063612_changes_for_v530.php
    │   │   ├── 2020_06_30_202620_changes_for_v530a.php
    │   │   ├── 2020_07_24_162820_changes_for_v540.php
    │   │   ├── 2020_11_12_070604_changes_for_v550.php
    │   │   ├── 2021_03_12_061213_changes_for_v550b2.php
    │   │   ├── 2021_05_09_064644_add_ldap_columns_to_users_table.php
    │   │   ├── 2021_05_13_053836_extend_currency_info.php
    │   │   ├── 2021_07_05_193044_drop_tele_table.php
    │   │   ├── 2021_08_28_073733_user_groups.php
    │   │   ├── 2021_12_27_000001_create_local_personal_access_tokens_table.php
    │   │   ├── 2022_08_21_104626_add_user_groups.php
    │   │   ├── 2022_09_18_123911_create_notifications_table.php
    │   │   ├── 2022_10_01_074908_invited_users.php
    │   │   ├── 2022_10_01_210238_audit_log_entries.php
    │   │   ├── 2023_08_11_192521_upgrade_og_table.php
    │   │   ├── 2023_10_21_113213_add_currency_pivot_tables.php
    │   │   ├── 2024_03_03_174645_add_indices.php
    │   │   ├── 2024_04_01_174351_expand_preferences_table.php
    │   │   ├── 2024_05_12_060551_create_account_balance_table.php
    │   │   ├── 2024_07_28_145631_add_running_balance.php
    │   │   ├── 2024_11_05_062108_add_date_tz_columns.php
    │   │   ├── 2024_11_30_075826_multi_piggy.php
    │   │   ├── 2024_12_19_061003_add_native_amount_column.php
    │   │   ├── 2025_07_10_065736_rename_tag_mode.php
    │   │   └── .gitkeep
    │   └── seeders/
    │       ├── AccountTypeSeeder.php
    │       ├── ConfigSeeder.php
    │       ├── DatabaseSeeder.php
    │       ├── ExchangeRateSeeder.php
    │       ├── LinkTypeSeeder.php
    │       ├── PermissionSeeder.php
    │       ├── TransactionCurrencySeeder.php
    │       ├── TransactionTypeSeeder.php
    │       ├── UserRoleSeeder.php
    │       └── .gitkeep
    ├── patches/
    │   └── admin-lte+4.0.0-rc4.patch
    ├── public/
    │   ├── browserconfig.xml
    │   ├── index.php
    │   ├── manifest.webmanifest
    │   ├── mix-manifest.json
    │   ├── robots.txt
    │   ├── web.config
    │   ├── .htaccess
    │   ├── v1/
    │   │   ├── css/
    │   │   │   ├── app.css
    │   │   │   ├── bootstrap-multiselect.css
    │   │   │   ├── bootstrap-sortable.css
    │   │   │   ├── bootstrap-tagsinput.css
    │   │   │   ├── daterangepicker-dark.css
    │   │   │   ├── daterangepicker-default.css
    │   │   │   ├── daterangepicker-light.css
    │   │   │   ├── firefly.css
    │   │   │   ├── gf-roboto.css
    │   │   │   ├── gf-source.css
    │   │   │   ├── .htaccess
    │   │   │   └── jquery-ui/
    │   │   │       ├── .htaccess
    │   │   │       └── images/
    │   │   │           └── .htaccess
    │   │   ├── fonts/
    │   │   │   ├── lato-100.woff
    │   │   │   ├── lato-100.woff2
    │   │   │   ├── roboto-light-300.woff
    │   │   │   ├── roboto-light-300.woff2
    │   │   │   ├── Roboto-Regular-cyrillic-ext.woff2
    │   │   │   ├── Roboto-Regular-cyrillic.woff2
    │   │   │   ├── Roboto-Regular-greek-ext.woff2
    │   │   │   ├── Roboto-Regular-greek.woff2
    │   │   │   ├── Roboto-Regular-latin-ext.woff2
    │   │   │   ├── Roboto-Regular-latin.woff2
    │   │   │   ├── Roboto-Regular-vietnamese.woff2
    │   │   │   ├── source-sans-pro-v13-greek_cyrillic-ext_vietnamese_greek-ext_latin-ext_cyrillic_latin-600italic.woff
    │   │   │   ├── source-sans-pro-v13-greek_cyrillic-ext_vietnamese_greek-ext_latin-ext_cyrillic_latin-600italic.woff2
    │   │   │   ├── source-sans-pro-v13-greek_cyrillic-ext_vietnamese_greek-ext_latin-ext_cyrillic_latin-italic.woff
    │   │   │   ├── source-sans-pro-v13-greek_cyrillic-ext_vietnamese_greek-ext_latin-ext_cyrillic_latin-italic.woff2
    │   │   │   └── .htaccess
    │   │   ├── images/
    │   │   │   ├── .htaccess
    │   │   │   ├── flags/
    │   │   │   │   └── .htaccess
    │   │   │   └── logos/
    │   │   │       └── .htaccess
    │   │   ├── js/
    │   │   │   ├── .gitkeep
    │   │   │   ├── .htaccess
    │   │   │   ├── ff/
    │   │   │   │   ├── charts.defaults.js
    │   │   │   │   ├── charts.js
    │   │   │   │   ├── firefly.js
    │   │   │   │   ├── guest.js
    │   │   │   │   ├── help.js
    │   │   │   │   ├── index.js
    │   │   │   │   ├── .htaccess
    │   │   │   │   ├── accounts/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   ├── edit-reconciliation.js
    │   │   │   │   │   ├── edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── reconcile.js
    │   │   │   │   │   ├── show.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── admin/
    │   │   │   │   │   ├── users.js
    │   │   │   │   │   ├── .htaccess
    │   │   │   │   │   └── update/
    │   │   │   │   │       ├── index.js
    │   │   │   │   │       └── .htaccess
    │   │   │   │   ├── bills/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   ├── edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── show.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── budgets/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   ├── edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── show.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── categories/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   ├── edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── show-by-date.js
    │   │   │   │   │   ├── show.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── common/
    │   │   │   │   │   └── autocomplete.js
    │   │   │   │   ├── currencies/
    │   │   │   │   │   └── index.js
    │   │   │   │   ├── export/
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── install/
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── intro/
    │   │   │   │   │   ├── intro.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── list/
    │   │   │   │   │   └── groups.js
    │   │   │   │   ├── object-groups/
    │   │   │   │   │   ├── create-edit.js
    │   │   │   │   │   └── index.js
    │   │   │   │   ├── piggy-banks/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   ├── edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── show.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── preferences/
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── recurring/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   ├── edit.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── reports/
    │   │   │   │   │   ├── all.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── .htaccess
    │   │   │   │   │   ├── audit/
    │   │   │   │   │   │   ├── all.js
    │   │   │   │   │   │   └── .htaccess
    │   │   │   │   │   ├── budget/
    │   │   │   │   │   │   ├── month.js
    │   │   │   │   │   │   └── .htaccess
    │   │   │   │   │   ├── category/
    │   │   │   │   │   │   ├── month.js
    │   │   │   │   │   │   └── .htaccess
    │   │   │   │   │   ├── default/
    │   │   │   │   │   │   ├── all.js
    │   │   │   │   │   │   ├── month.js
    │   │   │   │   │   │   ├── multi-year.js
    │   │   │   │   │   │   ├── year.js
    │   │   │   │   │   │   └── .htaccess
    │   │   │   │   │   ├── double/
    │   │   │   │   │   │   ├── month.js
    │   │   │   │   │   │   └── .htaccess
    │   │   │   │   │   └── tag/
    │   │   │   │   │       ├── month.js
    │   │   │   │   │       └── .htaccess
    │   │   │   │   ├── rule-groups/
    │   │   │   │   │   ├── create.js
    │   │   │   │   │   └── edit.js
    │   │   │   │   ├── rules/
    │   │   │   │   │   ├── create-edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── select-transactions.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── search/
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   ├── tags/
    │   │   │   │   │   ├── create-edit.js
    │   │   │   │   │   ├── index.js
    │   │   │   │   │   ├── show.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   └── transactions/
    │   │   │   │       ├── convert.js
    │   │   │   │       ├── index.js
    │   │   │   │       ├── list.js
    │   │   │   │       ├── show.js
    │   │   │   │       ├── .htaccess
    │   │   │   │       └── mass/
    │   │   │   │           ├── edit-bulk.js
    │   │   │   │           ├── edit.js
    │   │   │   │           └── .htaccess
    │   │   │   ├── lib/
    │   │   │   │   ├── bootstrap-sortable.js
    │   │   │   │   ├── modernizr-custom.js
    │   │   │   │   ├── .htaccess
    │   │   │   │   ├── moment/
    │   │   │   │   │   ├── bg_BG.js
    │   │   │   │   │   ├── ca_ES.js
    │   │   │   │   │   ├── cs_CZ.js
    │   │   │   │   │   ├── da_DK.js
    │   │   │   │   │   ├── de_DE.js
    │   │   │   │   │   ├── el_GR.js
    │   │   │   │   │   ├── en_GB.js
    │   │   │   │   │   ├── en_US.js
    │   │   │   │   │   ├── es_ES.js
    │   │   │   │   │   ├── es_MX.js
    │   │   │   │   │   ├── fi_FI.js
    │   │   │   │   │   ├── fr_FR.js
    │   │   │   │   │   ├── hu_HU.js
    │   │   │   │   │   ├── id_ID.js
    │   │   │   │   │   ├── it_IT.js
    │   │   │   │   │   ├── ja_JP.js
    │   │   │   │   │   ├── ko_KR.js
    │   │   │   │   │   ├── nb_NO.js
    │   │   │   │   │   ├── nl_NL.js
    │   │   │   │   │   ├── nn_NO.js
    │   │   │   │   │   ├── pl_PL.js
    │   │   │   │   │   ├── pt_BR.js
    │   │   │   │   │   ├── pt_PT.js
    │   │   │   │   │   ├── ro_RO.js
    │   │   │   │   │   ├── ru_RU.js
    │   │   │   │   │   ├── sk_SK.js
    │   │   │   │   │   ├── sl_SI.js
    │   │   │   │   │   ├── sv_SE.js
    │   │   │   │   │   ├── tr_TR.js
    │   │   │   │   │   ├── uk_UA.js
    │   │   │   │   │   ├── vi_VN.js
    │   │   │   │   │   ├── zh_CN.js
    │   │   │   │   │   ├── zh_TW.js
    │   │   │   │   │   └── .htaccess
    │   │   │   │   └── typeahead/
    │   │   │   │       ├── bloodhound.js
    │   │   │   │       └── .htaccess
    │   │   │   └── webhooks/
    │   │   │       └── .gitkeep
    │   │   └── lib/
    │   │       ├── .htaccess
    │   │       ├── adminlte/
    │   │       │   ├── .htaccess
    │   │       │   ├── css/
    │   │       │   │   ├── .htaccess
    │   │       │   │   └── skins/
    │   │       │   │       ├── skin-dark.css
    │   │       │   │       ├── skin-light.css
    │   │       │   │       └── .htaccess
    │   │       │   ├── img/
    │   │       │   │   └── .htaccess
    │   │       │   └── js/
    │   │       │       ├── adminlte.js
    │   │       │       ├── app.js
    │   │       │       └── .htaccess
    │   │       ├── bs/
    │   │       │   ├── css/
    │   │       │   │   └── bootstrap-theme.css
    │   │       │   ├── fonts/
    │   │       │   │   ├── glyphicons-halflings-regular.eot
    │   │       │   │   ├── glyphicons-halflings-regular.ttf
    │   │       │   │   ├── glyphicons-halflings-regular.woff
    │   │       │   │   └── glyphicons-halflings-regular.woff2
    │   │       │   └── js/
    │   │       │       └── npm.js
    │   │       ├── fa/
    │   │       │   └── css/
    │   │       │       └── font-awesome.css
    │   │       ├── fc/
    │   │       │   ├── fullcalendar.css
    │   │       │   ├── fullcalendar.print.css
    │   │       │   └── .htaccess
    │   │       ├── intro/
    │   │       │   └── .htaccess
    │   │       └── leaflet/
    │   │           ├── leaflet.css
    │   │           ├── .htaccess
    │   │           └── images/
    │   │               └── .htaccess
    │   └── .well-known/
    │       ├── security.txt
    │       └── security.txt.sig
    ├── resources/
    │   ├── assets/
    │   │   ├── v1/
    │   │   │   ├── mix-manifest.json
    │   │   │   ├── package.json
    │   │   │   ├── webpack.mix.js
    │   │   │   └── src/
    │   │   │       ├── app.js
    │   │   │       ├── app_vue.js
    │   │   │       ├── bootstrap.js
    │   │   │       ├── create_transaction.js
    │   │   │       ├── edit_transaction.js
    │   │   │       ├── i18n.js
    │   │   │       ├── profile.js
    │   │   │       ├── administrations/
    │   │   │       │   ├── edit.js
    │   │   │       │   └── index.js
    │   │   │       ├── components/
    │   │   │       │   ├── ExampleComponent.vue
    │   │   │       │   ├── SomeTestComponent.vue
    │   │   │       │   ├── administrations/
    │   │   │       │   │   ├── Edit.vue
    │   │   │       │   │   └── Index.vue
    │   │   │       │   ├── exchange-rates/
    │   │   │       │   │   ├── Index.vue
    │   │   │       │   │   └── Rates.vue
    │   │   │       │   ├── form/
    │   │   │       │   │   ├── Checkbox.vue
    │   │   │       │   │   ├── Title.vue
    │   │   │       │   │   ├── URL.vue
    │   │   │       │   │   ├── UserGroupCurrency.vue
    │   │   │       │   │   ├── WebhookDelivery.vue
    │   │   │       │   │   ├── WebhookResponse.vue
    │   │   │       │   │   └── WebhookTrigger.vue
    │   │   │       │   ├── passport/
    │   │   │       │   │   ├── AuthorizedClients.vue
    │   │   │       │   │   ├── Clients.vue
    │   │   │       │   │   └── PersonalAccessTokens.vue
    │   │   │       │   ├── profile/
    │   │   │       │   │   └── ProfileOptions.vue
    │   │   │       │   ├── transactions/
    │   │   │       │   │   ├── AccountSelect.vue
    │   │   │       │   │   ├── Amount.vue
    │   │   │       │   │   ├── Bill.vue
    │   │   │       │   │   ├── Budget.vue
    │   │   │       │   │   ├── Category.vue
    │   │   │       │   │   ├── CreateTransaction.vue
    │   │   │       │   │   ├── CustomAttachments.vue
    │   │   │       │   │   ├── CustomDate.vue
    │   │   │       │   │   ├── CustomString.vue
    │   │   │       │   │   ├── CustomTextarea.vue
    │   │   │       │   │   ├── CustomTransactionFields.vue
    │   │   │       │   │   ├── CustomUri.vue
    │   │   │       │   │   ├── ForeignAmountSelect.vue
    │   │   │       │   │   ├── GroupDescription.vue
    │   │   │       │   │   ├── PiggyBank.vue
    │   │   │       │   │   ├── Reconciled.vue
    │   │   │       │   │   ├── StandardDate.vue
    │   │   │       │   │   ├── Tags.vue
    │   │   │       │   │   ├── TransactionDescription.vue
    │   │   │       │   │   └── TransactionType.vue
    │   │   │       │   └── webhooks/
    │   │   │       │       ├── Create.vue
    │   │   │       │       ├── Edit.vue
    │   │   │       │       ├── Index.vue
    │   │   │       │       └── Show.vue
    │   │   │       ├── exchange-rates/
    │   │   │       │   ├── index.js
    │   │   │       │   └── rates.js
    │   │   │       ├── locales/
    │   │   │       │   ├── af.json
    │   │   │       │   ├── bg.json
    │   │   │       │   ├── ca.json
    │   │   │       │   ├── cs.json
    │   │   │       │   ├── da.json
    │   │   │       │   ├── de.json
    │   │   │       │   ├── el.json
    │   │   │       │   ├── en-gb.json
    │   │   │       │   ├── en.json
    │   │   │       │   ├── es.json
    │   │   │       │   ├── fa.json
    │   │   │       │   ├── fi.json
    │   │   │       │   ├── fr.json
    │   │   │       │   ├── hu.json
    │   │   │       │   ├── id.json
    │   │   │       │   ├── it.json
    │   │   │       │   ├── ja.json
    │   │   │       │   ├── ko.json
    │   │   │       │   ├── nb.json
    │   │   │       │   ├── nl.json
    │   │   │       │   ├── nn.json
    │   │   │       │   ├── pl.json
    │   │   │       │   ├── pt-br.json
    │   │   │       │   ├── pt.json
    │   │   │       │   ├── ro.json
    │   │   │       │   ├── ru.json
    │   │   │       │   ├── sk.json
    │   │   │       │   ├── sl.json
    │   │   │       │   ├── sv.json
    │   │   │       │   ├── tr.json
    │   │   │       │   ├── uk.json
    │   │   │       │   ├── vi.json
    │   │   │       │   ├── zh-cn.json
    │   │   │       │   ├── zh-tw.json
    │   │   │       │   └── .json
    │   │   │       └── webhooks/
    │   │   │           ├── create.js
    │   │   │           ├── edit.js
    │   │   │           ├── index.js
    │   │   │           └── show.js
    │   │   └── v2/
    │   │       ├── package.json
    │   │       ├── vite.config.js
    │   │       └── src/
    │   │           ├── api/
    │   │           │   ├── v1/
    │   │           │   │   ├── chart/
    │   │           │   │   │   ├── account/
    │   │           │   │   │   │   ├── dashboard.js
    │   │           │   │   │   │   └── overview.js
    │   │           │   │   │   ├── budget/
    │   │           │   │   │   │   └── dashboard.js
    │   │           │   │   │   └── category/
    │   │           │   │   │       └── dashboard.js
    │   │           │   │   ├── configuration/
    │   │           │   │   │   └── get.js
    │   │           │   │   ├── model/
    │   │           │   │   │   ├── account/
    │   │           │   │   │   │   ├── get.js
    │   │           │   │   │   │   └── put.js
    │   │           │   │   │   ├── attachment/
    │   │           │   │   │   │   └── post.js
    │   │           │   │   │   ├── budget/
    │   │           │   │   │   │   └── get.js
    │   │           │   │   │   ├── currency/
    │   │           │   │   │   │   └── get.js
    │   │           │   │   │   ├── piggy-bank/
    │   │           │   │   │   │   └── get.js
    │   │           │   │   │   ├── subscription/
    │   │           │   │   │   │   └── get.js
    │   │           │   │   │   ├── transaction/
    │   │           │   │   │   │   ├── get.js
    │   │           │   │   │   │   ├── post.js
    │   │           │   │   │   │   └── put.js
    │   │           │   │   │   └── user-group/
    │   │           │   │   │       ├── get.js
    │   │           │   │   │       ├── post.js
    │   │           │   │   │       └── put.js
    │   │           │   │   ├── preferences/
    │   │           │   │   │   ├── index.js
    │   │           │   │   │   ├── post.js
    │   │           │   │   │   └── put.js
    │   │           │   │   └── summary/
    │   │           │   │       └── index.js
    │   │           │   └── v2/
    │   │           │       ├── chart/
    │   │           │       │   ├── account/
    │   │           │       │   │   └── dashboard.js
    │   │           │       │   ├── budget/
    │   │           │       │   │   └── dashboard.js
    │   │           │       │   └── category/
    │   │           │       │       └── dashboard.js
    │   │           │       ├── model/
    │   │           │       │   ├── account/
    │   │           │       │   │   ├── get.js
    │   │           │       │   │   └── put.js
    │   │           │       │   ├── budget/
    │   │           │       │   │   └── get.js
    │   │           │       │   ├── currency/
    │   │           │       │   │   └── get.js
    │   │           │       │   ├── piggy-bank/
    │   │           │       │   │   └── get.js
    │   │           │       │   ├── subscription/
    │   │           │       │   │   └── get.js
    │   │           │       │   ├── transaction/
    │   │           │       │   │   ├── get.js
    │   │           │       │   │   ├── post.js
    │   │           │       │   │   └── put.js
    │   │           │       │   └── user-group/
    │   │           │       │       ├── get.js
    │   │           │       │       ├── post.js
    │   │           │       │       └── put.js
    │   │           │       └── summary/
    │   │           │           └── index.js
    │   │           ├── boot/
    │   │           │   ├── axios.js
    │   │           │   └── bootstrap.js
    │   │           ├── css/
    │   │           │   └── grid-ff3-theme.css
    │   │           ├── libraries/
    │   │           │   └── dark-editable/
    │   │           │       ├── dark-editable.css
    │   │           │       ├── dark-editable.js
    │   │           │       ├── Modes/
    │   │           │       │   ├── BaseMode.js
    │   │           │       │   ├── InlineMode.js
    │   │           │       │   └── PopupMode.js
    │   │           │       └── Types/
    │   │           │           ├── BaseType.js
    │   │           │           ├── DateTimeType.js
    │   │           │           ├── DateType.js
    │   │           │           ├── InputType.js
    │   │           │           ├── SelectType.js
    │   │           │           └── TextAreaType.js
    │   │           ├── pages/
    │   │           │   ├── template.js
    │   │           │   ├── accounts/
    │   │           │   │   └── index.js
    │   │           │   ├── administrations/
    │   │           │   │   ├── create.js
    │   │           │   │   ├── edit.js
    │   │           │   │   └── index.js
    │   │           │   ├── dashboard/
    │   │           │   │   ├── accounts.js
    │   │           │   │   ├── boxes.js
    │   │           │   │   ├── budgets.js
    │   │           │   │   ├── categories.js
    │   │           │   │   ├── dashboard.js
    │   │           │   │   ├── piggies.js
    │   │           │   │   ├── sankey.js
    │   │           │   │   └── subscriptions.js
    │   │           │   ├── shared/
    │   │           │   │   └── dates.js
    │   │           │   └── transactions/
    │   │           │       ├── create.js
    │   │           │       ├── edit.js
    │   │           │       ├── index.js
    │   │           │       ├── show.js
    │   │           │       └── shared/
    │   │           │           ├── add-autocomplete.js
    │   │           │           ├── autocomplete-functions.js
    │   │           │           ├── create-empty-split.js
    │   │           │           ├── load-budgets.js
    │   │           │           ├── load-currencies.js
    │   │           │           ├── load-piggy-banks.js
    │   │           │           ├── load-subscriptions.js
    │   │           │           ├── manage-locations.js
    │   │           │           ├── parse-downloaded-splits.js
    │   │           │           ├── parse-from-entries.js
    │   │           │           ├── process-attachments.js
    │   │           │           └── splice-errors-into-transactions.js
    │   │           ├── sass/
    │   │           │   ├── adminlte-filtered.scss
    │   │           │   └── app.scss
    │   │           ├── store/
    │   │           │   ├── get-configuration.js
    │   │           │   ├── get-fresh-variable.js
    │   │           │   ├── get-variable.js
    │   │           │   ├── get-variables.js
    │   │           │   └── set-variable.js
    │   │           ├── support/
    │   │           │   ├── cleanup-cache.js
    │   │           │   ├── default-chart-settings.js
    │   │           │   ├── get-cache-key.js
    │   │           │   ├── get-colors.js
    │   │           │   ├── get-viewrange.js
    │   │           │   ├── inline-edit.js
    │   │           │   ├── load-translations.js
    │   │           │   ├── page-navigation.js
    │   │           │   ├── ag-grid/
    │   │           │   │   ├── AmountEditor.js
    │   │           │   │   ├── DateTimeEditor.js
    │   │           │   │   └── TransactionDataSource.js
    │   │           │   ├── editable/
    │   │           │   │   └── GenericEditor.js
    │   │           │   ├── page-settings/
    │   │           │   │   ├── show-internals-button.js
    │   │           │   │   └── show-wizard-button.js
    │   │           │   └── renderers/
    │   │           │       ├── AccountRenderer.js
    │   │           │       └── GenericObjectRenderer.js
    │   │           └── util/
    │   │               ├── format-money.js
    │   │               └── format.js
    │   ├── lang/
    │   │   └── en_US/
    │   │       ├── api.php
    │   │       ├── auth.php
    │   │       ├── breadcrumbs.php
    │   │       ├── components.php
    │   │       ├── config.php
    │   │       ├── demo.php
    │   │       ├── email.php
    │   │       ├── errors.php
    │   │       ├── form.php
    │   │       ├── intro.php
    │   │       ├── list.php
    │   │       ├── locales.json
    │   │       ├── pagination.php
    │   │       ├── passwords.php
    │   │       ├── rules.php
    │   │       └── validation.php
    │   ├── locales/
    │   │   ├── af_ZA/
    │   │   │   └── locales.json
    │   │   ├── ca_ES/
    │   │   │   └── locales.json
    │   │   ├── cs_CZ/
    │   │   │   └── locales.json
    │   │   ├── da_DK/
    │   │   │   └── locales.json
    │   │   ├── de_DE/
    │   │   │   └── locales.json
    │   │   ├── en_GB/
    │   │   │   └── locales.json
    │   │   ├── en_US/
    │   │   │   └── locales.json
    │   │   ├── es_ES/
    │   │   │   └── locales.json
    │   │   ├── fi_FI/
    │   │   │   └── locales.json
    │   │   ├── fr_FR/
    │   │   │   └── locales.json
    │   │   ├── hu_HU/
    │   │   │   └── locales.json
    │   │   ├── id_ID/
    │   │   │   └── locales.json
    │   │   ├── it_IT/
    │   │   │   └── locales.json
    │   │   ├── ja_JP/
    │   │   │   └── locales.json
    │   │   ├── ko_KR/
    │   │   │   └── locales.json
    │   │   ├── nb_NO/
    │   │   │   └── locales.json
    │   │   ├── nl_NL/
    │   │   │   └── locales.json
    │   │   ├── nn_NO/
    │   │   │   └── locales.json
    │   │   ├── pl_PL/
    │   │   │   └── locales.json
    │   │   ├── pt_BR/
    │   │   │   └── locales.json
    │   │   ├── pt_PT/
    │   │   │   └── locales.json
    │   │   ├── ro_RO/
    │   │   │   └── locales.json
    │   │   ├── sk_SK/
    │   │   │   └── locales.json
    │   │   ├── sl_SI/
    │   │   │   └── locales.json
    │   │   ├── sv_SE/
    │   │   │   └── locales.json
    │   │   ├── tr_TR/
    │   │   │   └── locales.json
    │   │   ├── vi_VN/
    │   │   │   └── locales.json
    │   │   ├── zh_CN/
    │   │   │   └── locales.json
    │   │   └── zh_TW/
    │   │       └── locales.json
    │   ├── stubs/
    │   │   ├── csv.csv
    │   │   ├── demo-configuration.json
    │   │   └── demo-import.csv
    │   └── views/
    │       ├── debug.twig
    │       ├── error.twig
    │       ├── index.twig
    │       ├── pwa.twig
    │       ├── accounts/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   ├── show.twig
    │       │   └── reconcile/
    │       │       ├── edit.twig
    │       │       ├── index.twig
    │       │       ├── overview.twig
    │       │       ├── show.twig
    │       │       └── transactions.twig
    │       ├── administrations/
    │       │   ├── edit.twig
    │       │   └── index.twig
    │       ├── attachments/
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   └── index.twig
    │       ├── auth/
    │       │   ├── login.blade.php
    │       │   ├── lost-two-factor.blade.php
    │       │   ├── mfa.blade.php
    │       │   ├── register.blade.php
    │       │   └── passwords/
    │       │       ├── email.blade.php
    │       │       └── reset.blade.php
    │       ├── bills/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   └── show.twig
    │       ├── budgets/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   ├── no-budget.twig
    │       │   ├── show.twig
    │       │   └── budget-limits/
    │       │       ├── create.twig
    │       │       ├── edit.twig
    │       │       └── show.twig
    │       ├── categories/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   ├── no-category.twig
    │       │   └── show.twig
    │       ├── components/
    │       │   ├── messages.blade.php
    │       │   ├── transaction-split.blade.php
    │       │   └── transaction-tab-list.blade.php
    │       ├── currencies/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   └── index.twig
    │       ├── demo/
    │       │   ├── home.twig
    │       │   ├── index.twig
    │       │   ├── no-demo-text.twig
    │       │   ├── accounts/
    │       │   │   └── index.twig
    │       │   ├── budgets/
    │       │   │   └── index.twig
    │       │   ├── currencies/
    │       │   │   └── index.twig
    │       │   ├── piggy-banks/
    │       │   │   └── index.twig
    │       │   ├── profile/
    │       │   │   └── index.twig
    │       │   ├── recurring/
    │       │   │   ├── index.twig
    │       │   │   └── recurring-create.twig
    │       │   ├── reports/
    │       │   │   └── index.twig
    │       │   └── transactions/
    │       │       └── index.twig
    │       ├── emails/
    │       │   ├── admin-test.blade.php
    │       │   ├── bill-warning.blade.php
    │       │   ├── confirm-email-change.blade.php
    │       │   ├── error-html.twig
    │       │   ├── error-text.twig
    │       │   ├── footer-html.twig
    │       │   ├── footer-text.twig
    │       │   ├── header-html.twig
    │       │   ├── header-text.twig
    │       │   ├── invitation-created.blade.php
    │       │   ├── invitation.blade.php
    │       │   ├── new-ip.blade.php
    │       │   ├── new-version.blade.php
    │       │   ├── oauth-client-created.blade.php
    │       │   ├── password.blade.php
    │       │   ├── registered-admin.blade.php
    │       │   ├── registered.blade.php
    │       │   ├── report-new-journals.blade.php
    │       │   ├── token-created.blade.php
    │       │   ├── undo-email-change.blade.php
    │       │   ├── owner/
    │       │   │   └── unknown-user.blade.php
    │       │   └── security/
    │       │       ├── disabled-mfa.blade.php
    │       │       ├── enabled-mfa.blade.php
    │       │       ├── failed-login.blade.php
    │       │       ├── few-backup-codes.blade.php
    │       │       ├── many-failed-attempts.blade.php
    │       │       ├── new-backup-codes.blade.php
    │       │       ├── no-backup-codes.blade.php
    │       │       └── used-backup-code.blade.php
    │       ├── errors/
    │       │   ├── 404.blade.php
    │       │   ├── 500.blade.php
    │       │   ├── 503.blade.php
    │       │   ├── DatabaseException.blade.php
    │       │   └── FireflyException.blade.php
    │       ├── exchange-rates/
    │       │   ├── index.twig
    │       │   └── rates.twig
    │       ├── export/
    │       │   └── index.twig
    │       ├── form/
    │       │   ├── amount-no-currency.twig
    │       │   ├── amount-small.twig
    │       │   ├── amount.twig
    │       │   ├── assetAccountCheckList.twig
    │       │   ├── balance.twig
    │       │   ├── checkbox.twig
    │       │   ├── date.twig
    │       │   ├── feedback.twig
    │       │   ├── file.twig
    │       │   ├── help.twig
    │       │   ├── integer.twig
    │       │   ├── location.twig
    │       │   ├── multi-select.twig
    │       │   ├── non-selectable-amount.twig
    │       │   ├── number.twig
    │       │   ├── object_group.twig
    │       │   ├── options.twig
    │       │   ├── password.twig
    │       │   ├── percentage.twig
    │       │   ├── select.twig
    │       │   ├── static.twig
    │       │   ├── tags.twig
    │       │   ├── text.twig
    │       │   └── textarea.twig
    │       ├── install/
    │       │   ├── index-old.twig
    │       │   └── index.blade.php
    │       ├── javascript/
    │       │   ├── accounts.twig
    │       │   ├── currencies.twig
    │       │   └── variables.twig
    │       ├── json/
    │       │   └── piggy-banks.twig
    │       ├── layout/
    │       │   ├── default.twig
    │       │   ├── empty.twig
    │       │   ├── guest.twig
    │       │   ├── install.twig
    │       │   ├── v2/
    │       │   │   ├── error.blade.php
    │       │   │   └── session.blade.php
    │       │   └── v3/
    │       │       └── session.twig
    │       ├── list/
    │       │   ├── accounts.twig
    │       │   ├── ale.twig
    │       │   ├── attachments.twig
    │       │   ├── bills.twig
    │       │   ├── categories.twig
    │       │   ├── groups-tiny.twig
    │       │   ├── groups.twig
    │       │   ├── journals-array-tiny.twig
    │       │   ├── periods.twig
    │       │   ├── piggy-bank-events.twig
    │       │   └── piggy-banks.twig
    │       ├── new-user/
    │       │   └── index.twig
    │       ├── object-groups/
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   └── index.twig
    │       ├── pagination/
    │       │   └── bootstrap-4.twig
    │       ├── partials/
    │       │   ├── boxes.twig
    │       │   ├── control-bar.twig
    │       │   ├── debug-table.twig
    │       │   ├── empty.twig
    │       │   ├── favicons.twig
    │       │   ├── flashes.twig
    │       │   ├── journal-row.twig
    │       │   ├── menu-sidebar.twig
    │       │   ├── old-password-modal.twig
    │       │   ├── page-header.twig
    │       │   ├── password-modal-twig.twig
    │       │   ├── password-modal.blade.php
    │       │   ├── transaction-row.twig
    │       │   └── layout/
    │       │       └── breadcrumbs.twig
    │       ├── piggy-banks/
    │       │   ├── add-mobile.twig
    │       │   ├── add.twig
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   ├── remove-mobile.twig
    │       │   ├── remove.twig
    │       │   └── show.twig
    │       ├── popup/
    │       │   ├── list/
    │       │   │   └── journals.twig
    │       │   └── report/
    │       │       ├── balance-amount.twig
    │       │       ├── budget-spent-amount.twig
    │       │       ├── category-entry.twig
    │       │       ├── expense-entry.twig
    │       │       └── income-entry.twig
    │       ├── preferences/
    │       │   └── index.twig
    │       ├── profile/
    │       │   ├── change-email.twig
    │       │   ├── change-password.twig
    │       │   ├── delete-account.twig
    │       │   ├── index.twig
    │       │   ├── logout-other-sessions.twig
    │       │   ├── new-backup-codes.twig
    │       │   └── mfa/
    │       │       ├── backup-codes-intro.twig
    │       │       ├── backup-codes-post.twig
    │       │       ├── disable-mfa.twig
    │       │       ├── enable-mfa.twig
    │       │       └── index.twig
    │       ├── recurring/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   └── show.twig
    │       ├── reports/
    │       │   ├── index.twig
    │       │   ├── audit/
    │       │   │   └── report.twig
    │       │   ├── budget/
    │       │   │   ├── month.twig
    │       │   │   └── partials/
    │       │   │       ├── account-per-budget.twig
    │       │   │       ├── accounts.twig
    │       │   │       ├── avg-expenses.twig
    │       │   │       ├── budgets.twig
    │       │   │       └── top-expenses.twig
    │       │   ├── category/
    │       │   │   ├── month.twig
    │       │   │   └── partials/
    │       │   │       ├── account-per-category.twig
    │       │   │       ├── accounts.twig
    │       │   │       ├── avg-expenses.twig
    │       │   │       ├── avg-income.twig
    │       │   │       ├── categories.twig
    │       │   │       ├── top-expenses.twig
    │       │   │       └── top-income.twig
    │       │   ├── default/
    │       │   │   ├── month.twig
    │       │   │   ├── multi-year.twig
    │       │   │   └── year.twig
    │       │   ├── double/
    │       │   │   ├── report.twig
    │       │   │   └── partials/
    │       │   │       ├── accounts-per-asset.twig
    │       │   │       ├── accounts.twig
    │       │   │       ├── avg-expenses.twig
    │       │   │       ├── avg-income.twig
    │       │   │       ├── top-expenses.twig
    │       │   │       └── top-income.twig
    │       │   ├── options/
    │       │   │   ├── budget.twig
    │       │   │   ├── category.twig
    │       │   │   ├── double.twig
    │       │   │   ├── no-options.twig
    │       │   │   └── tag.twig
    │       │   ├── partials/
    │       │   │   ├── accounts.twig
    │       │   │   ├── balance.twig
    │       │   │   ├── bills.twig
    │       │   │   ├── budget-period.twig
    │       │   │   ├── budgets.twig
    │       │   │   ├── categories.twig
    │       │   │   ├── category-period.twig
    │       │   │   ├── exp-budgets.twig
    │       │   │   ├── exp-categories.twig
    │       │   │   ├── exp-not-grouped.twig
    │       │   │   ├── income-expenses.twig
    │       │   │   ├── journals-audit.twig
    │       │   │   ├── operations.twig
    │       │   │   ├── tags.twig
    │       │   │   └── top-transactions.twig
    │       │   └── tag/
    │       │       ├── month.twig
    │       │       └── partials/
    │       │           ├── account-per-tag.twig
    │       │           ├── accounts.twig
    │       │           ├── avg-expenses.twig
    │       │           ├── avg-income.twig
    │       │           ├── tags.twig
    │       │           ├── top-expenses.twig
    │       │           └── top-income.twig
    │       ├── rules/
    │       │   ├── index.twig
    │       │   ├── partials/
    │       │   │   ├── action.twig
    │       │   │   ├── test-trigger-modal.twig
    │       │   │   └── trigger.twig
    │       │   ├── rule/
    │       │   │   ├── create.twig
    │       │   │   ├── delete.twig
    │       │   │   ├── edit.twig
    │       │   │   └── select-transactions.twig
    │       │   └── rule-group/
    │       │       ├── create.twig
    │       │       ├── delete.twig
    │       │       ├── edit.twig
    │       │       └── select-transactions.twig
    │       ├── search/
    │       │   ├── index.twig
    │       │   └── search.twig
    │       ├── settings/
    │       │   ├── index.twig
    │       │   ├── configuration/
    │       │   │   └── index.twig
    │       │   ├── link/
    │       │   │   ├── create.twig
    │       │   │   ├── delete.twig
    │       │   │   ├── edit.twig
    │       │   │   ├── index.twig
    │       │   │   └── show.twig
    │       │   ├── notifications/
    │       │   │   └── index.twig
    │       │   ├── update/
    │       │   │   └── index.twig
    │       │   └── users/
    │       │       ├── delete.twig
    │       │       ├── edit.twig
    │       │       ├── index.twig
    │       │       └── show.twig
    │       ├── tags/
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   └── show.twig
    │       ├── test/
    │       │   └── test.twig
    │       ├── transactions/
    │       │   ├── convert.twig
    │       │   ├── create.twig
    │       │   ├── delete.twig
    │       │   ├── edit.twig
    │       │   ├── index.twig
    │       │   ├── show.twig
    │       │   ├── bulk/
    │       │   │   └── edit.twig
    │       │   ├── links/
    │       │   │   ├── delete.twig
    │       │   │   └── modal.twig
    │       │   ├── mass/
    │       │   │   ├── delete.twig
    │       │   │   └── edit.twig
    │       │   └── single/
    │       │       ├── delete.twig
    │       │       └── edit.twig
    │       ├── v2/
    │       │   ├── index.blade.php
    │       │   ├── accounts/
    │       │   │   └── index.blade.php
    │       │   ├── administrations/
    │       │   │   ├── create.blade.php
    │       │   │   ├── edit.blade.php
    │       │   │   └── index.blade.php
    │       │   ├── components/
    │       │   │   ├── messages.blade.php
    │       │   │   ├── transaction-split.blade.php
    │       │   │   └── transaction-tab-list.blade.php
    │       │   ├── errors/
    │       │   │   └── FireflyException.blade.php
    │       │   ├── layout/
    │       │   │   └── v2.blade.php
    │       │   ├── partials/
    │       │   │   ├── favicons.twig
    │       │   │   ├── dashboard/
    │       │   │   │   ├── account-chart.blade.php
    │       │   │   │   ├── account-list.blade.php
    │       │   │   │   ├── boxes.blade.php
    │       │   │   │   ├── budget-chart.blade.php
    │       │   │   │   ├── category-chart.blade.php
    │       │   │   │   ├── piggy-banks.blade.php
    │       │   │   │   ├── sankey.blade.php
    │       │   │   │   └── subscriptions.blade.php
    │       │   │   ├── elements/
    │       │   │   │   └── amount.blade.php
    │       │   │   ├── form/
    │       │   │   │   ├── submission-options.blade.php
    │       │   │   │   ├── title.blade.php
    │       │   │   │   └── transaction/
    │       │   │   │       ├── amount.blade.php
    │       │   │   │       ├── attachments.blade.php
    │       │   │   │       ├── budget.blade.php
    │       │   │   │       ├── category.blade.php
    │       │   │   │       ├── date-fields.blade.php
    │       │   │   │       ├── date-time.blade.php
    │       │   │   │       ├── description.blade.php
    │       │   │   │       ├── destination-account.blade.php
    │       │   │   │       ├── external-url.blade.php
    │       │   │   │       ├── foreign-amount.blade.php
    │       │   │   │       ├── internal-reference.blade.php
    │       │   │   │       ├── location.blade.php
    │       │   │   │       ├── notes.blade.php
    │       │   │   │       ├── piggy-bank.blade.php
    │       │   │   │       ├── source-account.blade.php
    │       │   │   │       ├── submission-options.blade.php
    │       │   │   │       ├── subscription.blade.php
    │       │   │   │       └── tags.blade.php
    │       │   │   └── layout/
    │       │   │       ├── breadcrumbs.blade.php
    │       │   │       ├── footer.blade.php
    │       │   │       ├── head.blade.php
    │       │   │       ├── scripts.blade.php
    │       │   │       ├── sidebar.blade.php
    │       │   │       └── topbar.blade.php
    │       │   └── transactions/
    │       │       ├── create.blade.php
    │       │       ├── edit.blade.php
    │       │       ├── index.blade.php
    │       │       └── show.blade.php
    │       └── webhooks/
    │           ├── create.twig
    │           ├── delete.twig
    │           ├── edit.twig
    │           ├── index.twig
    │           └── show.twig
    ├── routes/
    │   ├── api-noauth.php
    │   ├── api.php
    │   ├── breadcrumbs.php
    │   ├── channels.php
    │   └── console.php
    ├── storage/
    │   └── .htaccess
    ├── tests/
    │   ├── feature/
    │   │   ├── TestCase.php
    │   │   └── Http/
    │   │       └── Home/
    │   │           └── IndexControllerTest.php.disabled
    │   ├── integration/
    │   │   ├── CreatesApplication.php
    │   │   ├── TestCase.php
    │   │   ├── Api/
    │   │   │   ├── About/
    │   │   │   │   └── AboutControllerTest.php
    │   │   │   └── Autocomplete/
    │   │   │       ├── AccountControllerTest.php
    │   │   │       ├── BillControllerTest.php
    │   │   │       ├── BudgetControllerTest.php
    │   │   │       ├── CategoryControllerTest.php
    │   │   │       ├── CurrencyControllerTest.php
    │   │   │       └── ObjectGroupControllerTest.php
    │   │   ├── Support/
    │   │   │   ├── NavigationCustomEndOfPeriodTest.php
    │   │   │   └── Models/
    │   │   │       └── BillDateCalculatorTest.php
    │   │   └── Traits/
    │   │       └── CollectsValues.php
    │   └── unit/
    │       └── Support/
    │           ├── NavigationAddPeriodTest.php
    │           ├── NavigationEndOfPeriodTest.php
    │           ├── NavigationPreferredCarbonFormatByPeriodTest.php
    │           ├── NavigationPreferredCarbonFormatTest.php
    │           ├── NavigationPreferredEndOfPeriodTest.php
    │           ├── NavigationPreferredRangeFormatTest.php
    │           ├── NavigationPreferredSqlFormatTest.php
    │           ├── NavigationStartOfPeriodTest.php
    │           ├── Calendar/
    │           │   ├── CalculatorProvider.php
    │           │   ├── CalculatorTest.php
    │           │   └── Periodicity/
    │           │       ├── BimonthlyTest.php
    │           │       ├── DailyTest.php
    │           │       ├── FortnightlyTest.php
    │           │       ├── HalfYearlyTest.php
    │           │       ├── IntervalProvider.php
    │           │       ├── IntervalTestCase.php
    │           │       ├── MonthlyTest.php
    │           │       ├── QuarterlyTest.php
    │           │       ├── WeeklyTest.php
    │           │       └── YearlyTest.php
    │           └── Search/
    │               └── QueryParser/
    │                   ├── AbstractQueryParserInterfaceParseQueryTester.php
    │                   ├── GdbotsQueryParserParseQueryTest.php.disabled
    │                   └── QueryParserParseQueryTest.php
    ├── .ci/
    │   ├── all.sh
    │   ├── firefly-iii-standard.yml
    │   ├── phpcs.sh
    │   ├── phpmd.sh
    │   ├── phpstan.neon
    │   ├── phpstan.sh
    │   ├── rector.php
    │   ├── rector.sh
    │   ├── .env.ci
    │   ├── php-cs-fixer/
    │   │   ├── composer.json
    │   │   └── .php-cs-fixer.php
    │   └── phpmd/
    │       ├── composer.json
    │       ├── composer.lock
    │       └── phpmd.xml
    └── .github/
        ├── code_of_conduct.md
        ├── CODEOWNERS
        ├── contributing.md
        ├── dependabot.yml
        ├── funding.yml
        ├── its_you_not_me.md
        ├── label-actions.yml
        ├── mergify.yml
        ├── pull_request_template.md
        ├── security.md
        ├── support.md
        ├── ISSUE_TEMPLATE/
        │   ├── bug.yml
        │   ├── config.yml
        │   └── fr.yml
        └── workflows/
            ├── cleanup.yml
            ├── close-duplicates.yml
            ├── closed-issues.yml
            ├── debug-info-actions.yml
            ├── depsreview.yml
            ├── label-actions.yml
            ├── lock.yml
            ├── release.yml
            └── stale.yml
