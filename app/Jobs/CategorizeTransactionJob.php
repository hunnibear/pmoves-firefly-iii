<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\AIService;
use FireflyIII\Models\TransactionGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class CategorizeTransactionJob
 * 
 * Background job to categorize transactions using AI
 */
class CategorizeTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public TransactionGroup $transactionGroup;

    /**
     * Create a new job instance.
     */
    public function __construct(TransactionGroup $transactionGroup)
    {
        $this->transactionGroup = $transactionGroup;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('CategorizeTransactionJob: Starting categorization', [
            'group_id' => $this->transactionGroup->id,
            'user_id' => $this->transactionGroup->user_id
        ]);

        try {
            // Check if AI service is available
            $aiService = app(AIService::class);
            if (!$aiService->isAvailable()) {
                Log::warning('CategorizeTransactionJob: AI service not available');
                return;
            }

            // Get the first transaction journal from the group
            $journal = $this->transactionGroup->transactionJournals()->first();
            if (!$journal) {
                Log::warning('CategorizeTransactionJob: No transaction journal found', [
                    'group_id' => $this->transactionGroup->id
                ]);
                return;
            }

            // Prepare transaction data for AI
            $transactionData = [
                'description' => $journal->description,
                'amount' => $journal->transactions()->first()?->amount ?? 0,
                'date' => $journal->date->format('Y-m-d'),
                'source_account' => $journal->transactions()->where('amount', '<', 0)->first()?->account?->name,
                'destination_account' => $journal->transactions()->where('amount', '>', 0)->first()?->account?->name,
            ];

            // Get AI categorization
            $categorization = $aiService->categorizeTransaction($transactionData);
            
            if (!empty($categorization['category'])) {
                Log::info('CategorizeTransactionJob: AI suggested category', [
                    'group_id' => $this->transactionGroup->id,
                    'suggested_category' => $categorization['category'],
                    'confidence' => $categorization['confidence'] ?? 'unknown'
                ]);

                // TODO: Apply the categorization to the transaction
                // This would involve finding or creating the appropriate category
                // and updating the transaction journal
                
                // For now, just log the suggestion
                Log::info('CategorizeTransactionJob: Categorization complete', [
                    'group_id' => $this->transactionGroup->id,
                    'category' => $categorization['category']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('CategorizeTransactionJob: Categorization failed', [
                'group_id' => $this->transactionGroup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw the exception - just log it
            // We don't want to fail the entire import process due to AI issues
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CategorizeTransactionJob: Job failed', [
            'group_id' => $this->transactionGroup->id,
            'error' => $exception->getMessage()
        ]);
    }
}