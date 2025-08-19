<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CategorizeTransactionJob;
use FireflyIII\Events\StoredTransactionGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Class AutoCategorizeTransactionListener
 * 
 * Automatically triggers AI categorization when transactions are created
 */
class AutoCategorizeTransactionListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(StoredTransactionGroup $event): void
    {
        Log::debug('AutoCategorizeTransactionListener: Processing transaction group', [
            'group_id' => $event->transactionGroup->id,
            'user_id' => $event->transactionGroup->user_id
        ]);

        try {
            // Check if AI categorization is enabled
            if (!config('ai.auto_categorize_enabled', false)) {
                Log::debug('AutoCategorizeTransactionListener: Auto-categorization disabled');
                return;
            }

            // Dispatch the AI categorization job
            CategorizeTransactionJob::dispatch($event->transactionGroup);
            
            Log::info('AutoCategorizeTransactionListener: Dispatched categorization job', [
                'group_id' => $event->transactionGroup->id
            ]);

        } catch (\Exception $e) {
            Log::error('AutoCategorizeTransactionListener: Failed to dispatch categorization job', [
                'group_id' => $event->transactionGroup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't fail the transaction creation if AI categorization fails
            // Just log the error and continue
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(StoredTransactionGroup $event, $exception): void
    {
        Log::error('AutoCategorizeTransactionListener: Job failed', [
            'group_id' => $event->transactionGroup->id ?? 'unknown',
            'error' => $exception->getMessage()
        ]);
    }
}