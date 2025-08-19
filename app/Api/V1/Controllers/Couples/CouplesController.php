<?php

declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers\Couples;

use FireflyIII\Api\V1\Controllers\Controller;
use FireflyIII\Enums\AccountTypeEnum;
use FireflyIII\Enums\TransactionTypeEnum;
use FireflyIII\Models\TransactionJournal;
use FireflyIII\Models\Tag;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\PiggyBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouplesController extends Controller
{
    public function state(): JsonResponse
    {
        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Income calculation - get all income for this month
        $income = 0;
        $incomeJournals = $user->transactionJournals()
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->whereHas('transactionType', function ($query) {
                $query->where('type', TransactionTypeEnum::DEPOSIT->value);
            })
            ->with('transactions')
            ->get();

        foreach ($incomeJournals as $journal) {
            foreach ($journal->transactions as $transaction) {
                if ($transaction->amount > 0) { // Positive amounts are income
                    $income += $transaction->amount;
                }
            }
        }

        // Helper function to get transactions by tag
        $getTransactionsByTag = function ($tagName) use ($user, $startOfMonth, $endOfMonth) {
            return $user->transactionJournals()
                ->where('date', '>=', $startOfMonth)
                ->where('date', '<=', $endOfMonth)
                ->whereHas('tags', function ($query) use ($tagName) {
                    $query->where('tag', $tagName);
                })
                ->with(['transactions'])
                ->get()
                ->flatMap(function ($journal) {
                    return $journal->transactions->where('amount', '<', 0)->map(function ($transaction) use ($journal) {
                        return [
                            'id' => $transaction->id,
                            'description' => $journal->description,
                            'amount' => abs($transaction->amount), // Convert to positive for UI
                        ];
                    });
                });
        };

        // Get transactions by couples tags
        $p1Transactions = $getTransactionsByTag('couple-p1');
        $p2Transactions = $getTransactionsByTag('couple-p2');
        $sharedTransactions = $getTransactionsByTag('couple-shared');

        // Get unassigned transactions (no couples tags)
        $unassignedTransactions = $user->transactionJournals()
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->whereDoesntHave('tags', function ($query) {
                $query->whereIn('tag', ['couple-p1', 'couple-p2', 'couple-shared']);
            })
            ->with(['transactions'])
            ->get()
            ->flatMap(function ($journal) {
                return $journal->transactions->where('amount', '<', 0)->map(function ($transaction) use ($journal) {
                    return [
                        'id' => $transaction->id,
                        'description' => $journal->description,
                        'amount' => abs($transaction->amount), // Convert to positive for UI
                    ];
                });
            });

        // Goals from PiggyBanks
        $goals = $user->piggyBanks()->get()->map(function ($piggyBank) {
            return [
                'id' => $piggyBank->id,
                'name' => $piggyBank->name,
                'amount' => $piggyBank->target_amount,
                'saved' => $piggyBank->current_amount ?? 0,
                'date' => $piggyBank->target_date ? $piggyBank->target_date->format('Y-m-d') : null,
            ];
        });


        $state = [
            'person1' => [
                'name' => $user->name,
                'income' => $income,
                'transactions' => $p1Transactions,
            ],
            'person2' => [
                'name' => 'Partner', // TODO: Make this configurable
                'income' => 4000, // TODO: Get income from Firefly III
                'transactions' => $p2Transactions,
            ],
            'shared' => [
                'name' => 'Shared Expenses',
                'transactions' => $sharedTransactions,
                'contributionType' => 'incomeProportion',
                'person1CustomPercent' => 50,
            ],
            'unassigned' => [
                'name' => 'Unassigned Expenses',
                'transactions' => $unassignedTransactions,
            ],
            'goals' => $goals,
            'settings' => [
                'currency' => 'USD',
                'period' => 'monthly',
            ],
        ];

        return new JsonResponse($state);
    }

    public function storeTransaction(Request $request): JsonResponse
    {
        $user = auth()->user();
        $description = $request->input('description');
        $amount = $request->input('amount');
        $column = $request->input('column'); // e.g., 'person1', 'person2', 'shared', 'unassigned'

        // Determine the tag based on the column
        $tag = null;
        if ($column === 'person1') {
            $tag = 'couple-p1';
        } elseif ($column === 'person2') {
            $tag = 'couple-p2';
        } elseif ($column === 'shared') {
            $tag = 'couple-shared';
        }

        // Get default accounts for the transaction
        $defaultAssetAccount = $user->accounts()->accountTypeIn([AccountTypeEnum::ASSET->value])->first();
        $defaultExpenseAccount = $user->accounts()->accountTypeIn([AccountTypeEnum::EXPENSE->value])->first();

        if (!$defaultAssetAccount || !$defaultExpenseAccount) {
            return new JsonResponse(['message' => 'Default accounts not found'], 400);
        }

        // Create a new transaction journal
        $journal = new TransactionJournal();
        $journal->user_id = $user->id;
        $journal->description = $description;
        $journal->date = Carbon::now();
        $journal->transaction_type_id = 1; // Withdrawal type ID
        $journal->save();

        // Create source transaction (negative amount from asset account)
        $sourceTransaction = new Transaction();
        $sourceTransaction->account_id = $defaultAssetAccount->id;
        $sourceTransaction->transaction_journal_id = $journal->id;
        $sourceTransaction->amount = -$amount; // Negative for withdrawal from asset
        $sourceTransaction->save();

        // Create destination transaction (positive amount to expense account)
        $destinationTransaction = new Transaction();
        $destinationTransaction->account_id = $defaultExpenseAccount->id;
        $destinationTransaction->transaction_journal_id = $journal->id;
        $destinationTransaction->amount = $amount; // Positive for expense
        $destinationTransaction->save();

        // Attach the tag if applicable
        if ($tag) {
            $tagModel = Tag::firstOrCreate(['tag' => $tag, 'user_id' => $user->id]);
            $journal->tags()->attach($tagModel->id);
        }

        return new JsonResponse(['message' => 'Transaction created successfully', 'journal_id' => $journal->id], 201);
    }

    public function updateTransaction(Request $request, Transaction $transaction): JsonResponse
    {
        $user = auth()->user();

        // Ensure the transaction belongs to the authenticated user
        if ($transaction->transactionJournal->user_id !== $user->id) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        $description = $request->input('description');
        $amount = $request->input('amount');

        $journal = $transaction->transactionJournal;
        $journal->description = $description;
        $journal->save();

        // Update both transactions in the journal (source and destination)
        $transactions = $journal->transactions;
        foreach ($transactions as $trans) {
            if ($trans->amount < 0) {
                // This is the source transaction (withdrawal from asset)
                $trans->amount = -$amount;
            } else {
                // This is the destination transaction (to expense)
                $trans->amount = $amount;
            }
            $trans->save();
        }

        return new JsonResponse(['message' => 'Transaction updated successfully']);
    }

    public function deleteTransaction(Transaction $transaction): JsonResponse
    {
        $user = auth()->user();

        // Ensure the transaction belongs to the authenticated user
        if ($transaction->transactionJournal->user_id !== $user->id) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        $journal = $transaction->transactionJournal;
        $journal->delete(); // Soft delete the journal, which will also soft delete the transaction

        return new JsonResponse(['message' => 'Transaction deleted successfully']);
    }

    public function updateTransactionTag(Request $request, Transaction $transaction): JsonResponse
    {
        $user = auth()->user();

        // Ensure the transaction belongs to the authenticated user
        if ($transaction->transactionJournal->user_id !== $user->id) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        $column = $request->input('column'); // e.g., 'person1', 'person2', 'couple-shared', 'unassigned'

        // Remove existing couple-related tags
        $journal = $transaction->transactionJournal;
        $existingCoupleTags = $journal->tags()->whereIn('tag', ['couple-p1', 'couple-p2', 'couple-shared'])->get();
        foreach ($existingCoupleTags as $tag) {
            $journal->tags()->detach($tag->id);
        }

        // Determine the new tag based on the column
        $newTag = null;
        if ($column === 'person1') {
            $newTag = 'couple-p1';
        } elseif ($column === 'person2') {
            $newTag = 'couple-p2';
        } elseif ($column === 'shared') {
            $newTag = 'couple-shared';
        }

        // Attach the new tag if applicable
        if ($newTag) {
            $tagModel = Tag::firstOrCreate(['tag' => $newTag]);
            $journal->tags()->attach($tagModel->id);
        }

        return new JsonResponse(['message' => 'Transaction tag updated successfully']);
    }

    public function storeGoal(Request $request): JsonResponse
    {
        $user = auth()->user();
        $name = $request->input('name');
        $amount = $request->input('amount');
        $date = $request->input('date');

        $piggyBank = new PiggyBank();
        $piggyBank->user_id = $user->id;
        $piggyBank->name = $name;
        $piggyBank->target_amount = $amount;
        $piggyBank->target_date = Carbon::parse($date);
        $piggyBank->save();

        return new JsonResponse(['message' => 'Goal created successfully', 'goal' => $piggyBank->toArray()], 201);
    }

    public function deleteGoal(PiggyBank $goal): JsonResponse
    {
        $user = auth()->user();

        // Ensure the goal belongs to the authenticated user
        if ($goal->user_id !== $user->id) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        $goal->delete();

        return new JsonResponse(['message' => 'Goal deleted successfully']);
    }
}