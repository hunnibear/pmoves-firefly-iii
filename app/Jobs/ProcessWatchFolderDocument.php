<?php

namespace FireflyIII\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use FireflyIII\Services\LangExtractService;
use FireflyIII\Services\CouplesAIService;
use FireflyIII\Http\Controllers\CouplesController;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Queue job for processing documents from watch folders
 */
class ProcessWatchFolderDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;
    protected $documentType;
    protected $settings;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $userId, string $documentType, array $settings)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->documentType = $documentType;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing watch folder document', [
                'file' => $this->filePath,
                'user_id' => $this->userId,
                'document_type' => $this->documentType
            ]);

            // Check if file still exists
            if (!file_exists($this->filePath)) {
                Log::warning('Watch folder file no longer exists', ['file' => $this->filePath]);
                return;
            }

            // Create a temporary uploaded file object for processing
            $file = new File($this->filePath);
            $uploadedFile = new UploadedFile(
                $this->filePath,
                basename($this->filePath),
                $file->getMimeType(),
                null,
                true // Test mode - don't validate file upload
            );

            // Initialize services
            $langExtractService = app(LangExtractService::class);
            $couplesAIService = app(CouplesAIService::class);

            // Process document based on type
            $extractedData = $this->processDocumentByType($uploadedFile, $langExtractService);

            if (!$extractedData || ($extractedData['status'] ?? '') === 'error') {
                Log::error('Failed to extract data from watch folder document', [
                    'file' => $this->filePath,
                    'error' => $extractedData['error'] ?? 'Unknown extraction error'
                ]);
                return;
            }

            // Get couples profile for AI context
            $couplesProfile = $this->getCouplesProfile();

            // Process AI suggestions
            $aiSuggestions = $this->processAISuggestions($extractedData, $couplesAIService, $couplesProfile);

            // Create transactions if configured
            if ($this->settings['auto_create_transactions'] ?? false) {
                $this->createTransactions($extractedData, $aiSuggestions);
            }

            Log::info('Successfully processed watch folder document', [
                'file' => $this->filePath,
                'user_id' => $this->userId,
                'document_type' => $this->documentType,
                'transactions_created' => $this->settings['auto_create_transactions'] ?? false
            ]);

            // Move file to processed folder if configured
            if ($this->settings['move_after_processing'] ?? false) {
                $this->moveToProcessedFolder();
            }

        } catch (\Exception $e) {
            Log::error('Watch folder document processing failed', [
                'file' => $this->filePath,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark file as failed by moving to failed directory
            $this->markFileAsFailed($e);

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Process document based on its type
     */
    private function processDocumentByType(UploadedFile $file, LangExtractService $langExtractService): array
    {
        $useVisionModel = $this->settings['use_vision_model'] ?? true;

        switch ($this->documentType) {
            case 'statement':
                return $langExtractService->processBankStatement($file, $useVisionModel);

            case 'photo':
            case 'document':
                return $langExtractService->processDocument($file, $this->documentType, $useVisionModel);

            case 'receipt':
            default:
                return $langExtractService->processReceipt($file);
        }
    }

    /**
     * Process AI suggestions for the extracted data
     */
    private function processAISuggestions(array $extractedData, CouplesAIService $couplesAIService, array $couplesProfile): array
    {
        if ($this->documentType === 'statement' && isset($extractedData['transactions'])) {
            // Process multiple transactions for bank statements
            $transactions = $extractedData['transactions'];
            $aiSuggestions = [];

            foreach ($transactions as $index => $transaction) {
                $categorization = $couplesAIService->categorizeForCouples($transaction, $couplesProfile);
                $assignment = $couplesAIService->suggestPartnerAssignment($transaction, $couplesProfile);

                $aiSuggestions[] = [
                    'transaction_index' => $index,
                    'category' => $categorization['category'],
                    'subcategory' => $categorization['subcategory'],
                    'partner_assignment' => $assignment['assignment'],
                    'split_percentage' => $assignment['split_percentage'],
                    'confidence' => min($categorization['confidence'], $assignment['confidence']),
                    'categorization_reasoning' => $categorization['reasoning'],
                    'assignment_reasoning' => $assignment['reasoning']
                ];
            }

            return [
                'transaction_suggestions' => $aiSuggestions,
                'statement_summary' => [
                    'total_transactions' => count($transactions),
                    'total_amount' => collect($transactions)->sum('amount'),
                    'date_range' => [
                        'start' => collect($transactions)->min('date'),
                        'end' => collect($transactions)->max('date')
                    ]
                ]
            ];
        } else {
            // Process single document
            $categorization = $couplesAIService->categorizeForCouples($extractedData, $couplesProfile);
            $assignment = $couplesAIService->suggestPartnerAssignment($extractedData, $couplesProfile);

            return [
                'category' => $categorization['category'],
                'subcategory' => $categorization['subcategory'],
                'partner_assignment' => $assignment['assignment'],
                'split_percentage' => $assignment['split_percentage'],
                'confidence' => min($categorization['confidence'], $assignment['confidence']),
                'categorization_reasoning' => $categorization['reasoning'],
                'assignment_reasoning' => $assignment['reasoning']
            ];
        }
    }

    /**
     * Create transactions in Firefly III
     */
    private function createTransactions(array $extractedData, array $aiSuggestions): void
    {
        // Use the existing transaction creation logic from CouplesController
        $controller = app(CouplesController::class);

        if ($this->documentType === 'statement' && isset($extractedData['transactions'])) {
            // Create multiple transactions from bank statement
            $this->createTransactionsFromBankStatement($extractedData, $aiSuggestions, $controller);
        } else {
            // Create single transaction
            $this->createSingleTransaction($extractedData, $aiSuggestions, $controller);
        }
    }

    /**
     * Create multiple transactions from bank statement
     */
    private function createTransactionsFromBankStatement(array $extractedData, array $aiSuggestions, $controller): void
    {
        $transactions = $extractedData['transactions'] ?? [];
        $suggestions = $aiSuggestions['transaction_suggestions'] ?? [];

        foreach ($transactions as $index => $transactionData) {
            $suggestion = $suggestions[$index] ?? null;

            try {
                // Use reflection to call private method (not ideal, but works for now)
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('createTransactionFromReceipt');
                $method->setAccessible(true);

                $result = $method->invoke($controller, $transactionData, $suggestion);

                Log::info('Watch folder transaction created', [
                    'file' => $this->filePath,
                    'transaction_index' => $index,
                    'transaction_id' => $result['transaction_id'] ?? null
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create transaction from watch folder bank statement', [
                    'file' => $this->filePath,
                    'transaction_index' => $index,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Create single transaction
     */
    private function createSingleTransaction(array $extractedData, array $aiSuggestions, $controller): void
    {
        try {
            // Use reflection to call private method
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('createTransactionFromReceipt');
            $method->setAccessible(true);

            $result = $method->invoke($controller, $extractedData, $aiSuggestions);

            Log::info('Watch folder transaction created', [
                'file' => $this->filePath,
                'transaction_id' => $result['transaction_id'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create transaction from watch folder document', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get couples profile for AI context
     */
    private function getCouplesProfile(): array
    {
        // This could be loaded from database based on user_id
        // For now, return default profile
        return [
            'partner1_name' => 'Partner 1',
            'partner2_name' => 'Partner 2',
            'shared_categories' => 'Groceries, Utilities, Rent, Insurance, Healthcare'
        ];
    }

    /**
     * Move file to processed folder
     */
    private function moveToProcessedFolder(): void
    {
        try {
            $watchBasePath = dirname($this->filePath);
            $processedDir = $this->settings['processed_folder'] ?? $watchBasePath . '/processed';
            
            // Create processed directory if it doesn't exist
            if (!is_dir($processedDir)) {
                mkdir($processedDir, 0755, true);
            }
            
            $fileName = basename($this->filePath);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $processedPath = $processedDir . '/' . $timestamp . '_' . $fileName;
            
            // Handle filename conflicts
            $counter = 1;
            while (file_exists($processedPath)) {
                $pathInfo = pathinfo($processedPath);
                $processedPath = $pathInfo['dirname'] . '/' . $timestamp . '_' . $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
                $counter++;
            }
            
            if (file_exists($this->filePath)) {
                rename($this->filePath, $processedPath);
                
                Log::info('Moved processed file to processed directory', [
                    'original' => $this->filePath,
                    'processed_path' => $processedPath
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to move file to processed directory', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark file as failed and move to failed directory
     */
    private function markFileAsFailed(\Exception $exception = null): void
    {
        try {
            $watchBasePath = dirname($this->filePath);
            $failedDir = $watchBasePath . '/failed';
            
            // Create failed directory if it doesn't exist
            if (!is_dir($failedDir)) {
                mkdir($failedDir, 0755, true);
            }
            
            $fileName = basename($this->filePath);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $failedPath = $failedDir . '/' . $timestamp . '_' . $fileName;
            
            // Handle filename conflicts
            $counter = 1;
            while (file_exists($failedPath)) {
                $pathInfo = pathinfo($failedPath);
                $failedPath = $pathInfo['dirname'] . '/' . $timestamp . '_' . $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
                $counter++;
            }
            
            if (file_exists($this->filePath)) {
                rename($this->filePath, $failedPath);
                
                // Create error log file
                $logPath = $failedPath . '.error.log';
                $errorDetails = [
                    'file' => $this->filePath,
                    'user_id' => $this->userId,
                    'document_type' => $this->documentType,
                    'timestamp' => now()->toISOString(),
                    'error' => $exception ? $exception->getMessage() : 'Unknown error',
                    'trace' => $exception ? $exception->getTraceAsString() : 'No trace available'
                ];
                
                file_put_contents($logPath, json_encode($errorDetails, JSON_PRETTY_PRINT));
                
                Log::info('Moved failed file to failed directory', [
                    'original' => $this->filePath,
                    'failed_path' => $failedPath,
                    'error_log' => $logPath
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to move file to failed directory', [
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Watch folder document processing job failed permanently', [
            'file' => $this->filePath,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}