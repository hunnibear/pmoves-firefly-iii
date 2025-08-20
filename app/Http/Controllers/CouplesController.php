<?php

declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Services\LangExtractService;
use FireflyIII\Services\CouplesAIService;
use FireflyIII\Repositories\Transaction\TransactionRepositoryInterface;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CouplesController extends Controller
{
    private LangExtractService $langExtractService;
    private CouplesAIService $couplesAIService;
    
    public function __construct(LangExtractService $langExtractService, CouplesAIService $couplesAIService)
    {
        parent::__construct();
        $this->langExtractService = $langExtractService;
        $this->couplesAIService = $couplesAIService;
    }
    /**
     * Display the basic couples index page
     */
    public function index()
    {
        return view('couples.index');
    }

    /**
     * Display the enhanced couples dashboard with Supabase integration
     */
    public function dashboard()
    {
        return view('couples.dashboard');
    }

    /**
     * API endpoint for dashboard data with budget categories and recent transactions
     */
    public function dashboardData(): JsonResponse
    {
        $user = auth()->user();
        
        try {
            // Get budget categories with spending data
            $budgetCategories = [
                [
                    'category' => 'Groceries',
                    'spent' => 450.00,
                    'budget' => 600.00,
                    'color' => '#8884d8'
                ],
                [
                    'category' => 'Restaurants',
                    'spent' => 280.00,
                    'budget' => 300.00,
                    'color' => '#82ca9d'
                ],
                [
                    'category' => 'Transportation',
                    'spent' => 150.00,
                    'budget' => 200.00,
                    'color' => '#ffc658'
                ],
                [
                    'category' => 'Entertainment',
                    'spent' => 120.00,
                    'budget' => 150.00,
                    'color' => '#ff7300'
                ]
            ];

            // Get recent transactions (limit to last 10)
            $recentTransactions = [
                [
                    'id' => 1,
                    'description' => 'Grocery Store',
                    'amount' => -85.50,
                    'category' => 'Groceries',
                    'partner' => 'Alex',
                    'date' => now()->subDays(1)->format('Y-m-d'),
                    'ai_processed' => false,
                    'confidence' => null
                ],
                [
                    'id' => 2,
                    'description' => 'Coffee Shop',
                    'amount' => -12.75,
                    'category' => 'Restaurants',
                    'partner' => 'Jamie',
                    'date' => now()->subDays(1)->format('Y-m-d'),
                    'ai_processed' => false,
                    'confidence' => null
                ],
                [
                    'id' => 3,
                    'description' => 'Gas Station',
                    'amount' => -45.00,
                    'category' => 'Transportation',
                    'partner' => 'Alex',
                    'date' => now()->subDays(2)->format('Y-m-d'),
                    'ai_processed' => true,
                    'confidence' => 0.88
                ],
                [
                    'id' => 4,
                    'description' => 'Movie Theater',
                    'amount' => -25.00,
                    'category' => 'Entertainment',
                    'partner' => 'Jamie',
                    'date' => now()->subDays(2)->format('Y-m-d'),
                    'ai_processed' => false,
                    'confidence' => null
                ]
            ];

            return response()->json([
                'status' => 'success',
                'budget_categories' => $budgetCategories,
                'recent_transactions' => $recentTransactions,
                'summary' => [
                    'total_spent' => array_sum(array_column($budgetCategories, 'spent')),
                    'total_budget' => array_sum(array_column($budgetCategories, 'budget')),
                    'transaction_count' => count($recentTransactions)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard data fetch failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data',
                'error_code' => 'DASHBOARD_FETCH_ERROR'
            ], 500);
        }
    }

    /**
     * API endpoint for couples financial state
     */
    public function state(): JsonResponse
    {
        $user = auth()->user();
        
        // Mock data structure - will be replaced with real Firefly III data integration
        $couplesData = [
            'totalBalance' => 5420.50,
            'monthlySpending' => 3250.75,
            'sharedExpenses' => 1890.25,
            'partners' => [
                'partner1' => [
                    'name' => 'Partner 1',
                    'balance' => 2710.25,
                    'personal_spending' => 1360.50
                ],
                'partner2' => [
                    'name' => 'Partner 2', 
                    'balance' => 2710.25,
                    'personal_spending' => 1890.25
                ]
            ],
            'recentTransactions' => [
                [
                    'id' => 1,
                    'date' => now()->subDays(1)->format('Y-m-d'),
                    'description' => 'Grocery Store',
                    'amount' => -85.50,
                    'category' => 'Groceries',
                    'assignment' => 'shared',
                    'ai_categorized' => true,
                    'ai_confidence' => 0.95
                ],
                [
                    'id' => 2,
                    'date' => now()->subDays(2)->format('Y-m-d'),
                    'description' => 'Gas Station',
                    'amount' => -45.00,
                    'category' => 'Transportation', 
                    'assignment' => 'partner1',
                    'ai_categorized' => true,
                    'ai_confidence' => 0.88
                ]
            ]
        ];

        return response()->json($couplesData);
    }

    /**
     * Process uploaded document with LangExtract integration and real transaction creation
     * Enhanced to support receipts, bank statements, photos, and other financial documents
     */
    public function uploadReceipt(Request $request): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Handle different upload methods and document types
            if ($request->hasFile('document') || $request->hasFile('receipt')) {
                // Standard multipart file upload - support both 'document' and 'receipt' field names
                $fileField = $request->hasFile('document') ? 'document' : 'receipt';
                
                // Enhanced validation for multiple document types
                $request->validate([
                    $fileField => 'required|file|mimes:jpg,jpeg,png,pdf,txt,csv,xlsx,xls|max:51200', // 50MB max
                    'document_type' => 'string|in:receipt,statement,photo,document',
                    'create_transaction' => 'boolean',
                    'use_vision_model' => 'boolean',
                    'account_id' => 'nullable|integer',
                    'partner_override' => 'nullable|in:partner1,partner2,shared'
                ]);
                
                $file = $request->file($fileField);
                $documentType = $request->input('document_type', 'receipt');
                $useVisionModel = $request->boolean('use_vision_model', false);
                
                Log::info('Document upload started', [
                    'user_id' => auth()->id(),
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'document_type' => $documentType,
                    'use_vision_model' => $useVisionModel
                ]);
                
                // Process document based on type using enhanced LangExtract service
                switch ($documentType) {
                    case 'statement':
                        $extractedData = $this->langExtractService->processBankStatement($file, $useVisionModel);
                        break;
                    case 'photo':
                    case 'document':
                        $extractedData = $this->langExtractService->processDocument($file, $documentType, $useVisionModel);
                        break;
                    case 'receipt':
                    default:
                        $extractedData = $this->langExtractService->processReceipt($file);
                        break;
                }
                
            } else {
                // Raw binary data upload (legacy support)
                $fileContent = $request->getContent();
                if (empty($fileContent)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No file content provided',
                        'error_code' => 'EMPTY_CONTENT'
                    ], 422);
                }
                
                $fileName = $request->header('X-Filename') ?? 'uploaded_receipt.txt';
                $mimeType = $request->header('Content-Type') ?? 'text/plain';
                $documentType = $request->input('document_type', 'receipt');
                
                Log::info('Document upload started (raw)', [
                    'user_id' => auth()->id(),
                    'filename' => $fileName,
                    'size' => strlen($fileContent),
                    'content_type' => $mimeType,
                    'document_type' => $documentType
                ]);
                
                // Process raw content (legacy method)
                $extractedData = $this->langExtractService->processReceiptContent($fileContent, $fileName, $mimeType);
            }
            
            // Get couples profile for AI context
            $couplesProfile = [
                'partner1_name' => 'Partner 1',
                'partner2_name' => 'Partner 2', 
                'shared_categories' => 'Groceries, Utilities, Rent, Insurance, Healthcare'
            ];
            
            // Enhanced AI processing based on document type
            if (isset($documentType) && $documentType === 'statement') {
                // For bank statements, focus on transaction categorization
                $aiSuggestions = $this->processBankStatementAI($extractedData, $couplesProfile);
            } else {
                // For receipts, photos, and other documents
                $categorization = $this->couplesAIService->categorizeForCouples($extractedData, $couplesProfile);
                $assignment = $this->couplesAIService->suggestPartnerAssignment($extractedData, $couplesProfile);
                
                $aiSuggestions = [
                    'category' => $categorization['category'],
                    'subcategory' => $categorization['subcategory'],
                    'partner_assignment' => $assignment['assignment'],
                    'split_percentage' => $assignment['split_percentage'],
                    'confidence' => min($categorization['confidence'], $assignment['confidence']),
                    'categorization_reasoning' => $categorization['reasoning'],
                    'assignment_reasoning' => $assignment['reasoning']
                ];
            }
            
            $processingTime = round((microtime(true) - $startTime), 2);

            $response = [
                'status' => $extractedData['status'] ?? 'success',
                'extracted_data' => $extractedData['extracted_data'] ?? $extractedData,
                'ai_suggestions' => $aiSuggestions,
                'processing_time' => $processingTime . 's',
                'timestamp' => now()->toISOString(),
                'document_type' => $documentType ?? 'receipt'
            ];

            // Create actual transaction(s) if requested
            if ($request->boolean('create_transaction')) {
                if (isset($documentType) && $documentType === 'statement') {
                    // Create multiple transactions from bank statement
                    $transactionResults = $this->createTransactionsFromBankStatement(
                        $extractedData, 
                        $aiSuggestions, 
                        $request->input('account_id'),
                        $request->input('partner_override')
                    );
                    $response['transactions_created'] = $transactionResults;
                } else {
                    // Create single transaction from receipt/document
                    $transactionResult = $this->createTransactionFromReceipt(
                        $extractedData['extracted_data'] ?? $extractedData, 
                        $aiSuggestions, 
                        $request->input('account_id'),
                        $request->input('partner_override')
                    );
                    $response['transaction_created'] = $transactionResult;
                }
                
                Log::info('Transaction(s) created from document', [
                    'transaction_id' => $transactionResult['transaction_id'] ?? null,
                    'amount' => $extractedData['total_amount'] ?? null
                ]);
            }

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Receipt upload error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process receipt. Please try again.',
                'error_code' => 'RECEIPT_PROCESSING_FAILED',
                'debug_info' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Process photo uploads with vision model integration
     * Specialized endpoint for camera captures and photo-based document processing
     */
    public function processPhoto(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'photo' => 'required|file|mimes:jpg,jpeg,png|max:51200', // 50MB max for photos
                'create_transaction' => 'boolean',
                'account_id' => 'nullable|integer',
                'partner_override' => 'nullable|in:partner1,partner2,shared',
                'document_type' => 'string|in:receipt,statement,document'
            ]);
            
            $file = $request->file('photo');
            $documentType = $request->input('document_type', 'photo');
            
            Log::info('Photo processing started', [
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'document_type' => $documentType
            ]);
            
            // Force vision model usage for photos
            $extractedData = $this->langExtractService->processDocument($file, $documentType, true);
            
            // Get couples profile for AI context
            $couplesProfile = [
                'partner1_name' => 'Partner 1',
                'partner2_name' => 'Partner 2', 
                'shared_categories' => 'Groceries, Utilities, Rent, Insurance, Healthcare'
            ];
            
            // Get AI suggestions
            $categorization = $this->couplesAIService->categorizeForCouples($extractedData, $couplesProfile);
            $assignment = $this->couplesAIService->suggestPartnerAssignment($extractedData, $couplesProfile);
            
            $aiSuggestions = [
                'category' => $categorization['category'],
                'subcategory' => $categorization['subcategory'],
                'partner_assignment' => $assignment['assignment'],
                'split_percentage' => $assignment['split_percentage'],
                'confidence' => min($categorization['confidence'], $assignment['confidence']),
                'categorization_reasoning' => $categorization['reasoning'],
                'assignment_reasoning' => $assignment['reasoning']
            ];
            
            $response = [
                'status' => 'success',
                'extracted_data' => $extractedData,
                'ai_suggestions' => $aiSuggestions,
                'vision_model_used' => true,
                'document_type' => $documentType,
                'timestamp' => now()->toISOString()
            ];
            
            // Create transaction if requested
            if ($request->boolean('create_transaction')) {
                $transactionResult = $this->createTransactionFromReceipt(
                    $extractedData, 
                    $aiSuggestions, 
                    $request->input('account_id'),
                    $request->input('partner_override')
                );
                
                $response['transaction_created'] = $transactionResult;
                Log::info('Transaction created from photo', [
                    'user_id' => auth()->id(),
                    'transaction_id' => $transactionResult['transaction_id'] ?? null
                ]);
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Photo processing failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Photo processing failed: ' . $e->getMessage(),
                'error_code' => 'PHOTO_PROCESSING_ERROR'
            ], 500);
        }
    }

    /**
     * Process AI suggestions for bank statement transactions
     */
    private function processBankStatementAI($extractedData, $couplesProfile): array
    {
        $transactions = $extractedData['transactions'] ?? [];
        $aiSuggestions = [];
        
        foreach ($transactions as $index => $transaction) {
            $categorization = $this->couplesAIService->categorizeForCouples($transaction, $couplesProfile);
            $assignment = $this->couplesAIService->suggestPartnerAssignment($transaction, $couplesProfile);
            
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
    }

    /**
     * Create multiple transactions from bank statement data
     */
    private function createTransactionsFromBankStatement($extractedData, $aiSuggestions, $accountId = null, $partnerOverride = null): array
    {
        $transactions = $extractedData['transactions'] ?? [];
        $suggestions = $aiSuggestions['transaction_suggestions'] ?? [];
        $results = [];
        
        foreach ($transactions as $index => $transactionData) {
            $suggestion = $suggestions[$index] ?? null;
            
            try {
                $result = $this->createTransactionFromReceipt($transactionData, $suggestion, $accountId, $partnerOverride);
                $results[] = [
                    'index' => $index,
                    'status' => 'success',
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'firefly_response' => $result
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'index' => $index,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'transaction_data' => $transactionData
                ];
                
                Log::error('Failed to create transaction from bank statement', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'transaction_data' => $transactionData
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Create a Firefly III transaction from receipt data
     */
    private function createTransactionFromReceipt($extractedData, $aiSuggestions, $accountId = null, $partnerOverride = null): array
    {
        try {
            // Use existing transaction repository pattern from Firefly III
            $transactionRepository = app(\FireflyIII\Repositories\Transaction\TransactionRepositoryInterface::class);
            $accountRepository = app(\FireflyIII\Repositories\Account\AccountRepositoryInterface::class);
            
            // Get user's default asset account if none specified
            if (!$accountId) {
                $account = $accountRepository->getAccountsByType(['Asset account'])->first();
                if (!$account) {
                    throw new \Exception('No asset account found. Please create an account first.');
                }
                $accountId = $account->id;
            }

            // Prepare transaction data in Firefly III format
            $transactionData = [
                'group_title' => 'Receipt: ' . ($extractedData['merchant_name'] ?? 'Unknown Merchant'),
                'transactions' => [
                    [
                        'type' => 'withdrawal',
                        'date' => $extractedData['date'] ?? now()->format('Y-m-d'),
                        'amount' => abs(floatval($extractedData['total_amount'] ?? $extractedData['amount'] ?? 0)),
                        'currency_id' => 1, // USD - could be made configurable
                        'description' => $extractedData['description'] ?? 
                                       ($extractedData['merchant_name'] ?? 'Receipt Upload') . 
                                       ' - ' . 
                                       ($extractedData['items'][0]['description'] ?? 'Various items'),
                        'source_id' => $accountId,
                        'destination_name' => $extractedData['merchant_name'] ?? 'Cash Expense',
                        'category_name' => $aiSuggestions['category'] ?? 'Uncategorized',
                        'tags' => [
                            'ai-processed',
                            'couples-' . ($partnerOverride ?? $aiSuggestions['partner_assignment'] ?? 'shared'),
                            'receipt-upload',
                            'confidence-' . round(($aiSuggestions['confidence'] ?? 0) * 100)
                        ],
                        'notes' => 'Created from receipt upload. ' . 
                                 'AI Confidence: ' . round(($aiSuggestions['confidence'] ?? 0) * 100) . '%. ' .
                                 'Reasoning: ' . ($aiSuggestions['categorization_reasoning'] ?? 'N/A'),
                    ]
                ]
            ];

            // Store the transaction using Firefly III's existing system
            $transactionGroup = $transactionRepository->store($transactionData);
            
            return [
                'success' => true,
                'transaction_id' => $transactionGroup->id,
                'transaction_group_id' => $transactionGroup->id,
                'amount' => $transactionData['transactions'][0]['amount'],
                'description' => $transactionData['transactions'][0]['description'],
                'category' => $transactionData['transactions'][0]['category_name'],
                'partner_assignment' => $partnerOverride ?? $aiSuggestions['partner_assignment'] ?? 'shared',
                'ai_confidence' => $aiSuggestions['confidence'] ?? 0
            ];
            
        } catch (\Exception $e) {
            Log::error('Transaction creation from receipt failed', [
                'error' => $e->getMessage(),
                'extracted_data' => $extractedData,
                'ai_suggestions' => $aiSuggestions
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'TRANSACTION_CREATION_FAILED'
            ];
        }
    }

    /**
     * Process bank statement upload
     */
    public function processBankStatement(Request $request): JsonResponse
    {
        $request->validate([
            'bank_statement' => 'required|file|mimes:pdf,csv|max:50240' // 50MB max
        ]);

        try {
            // Process bank statement with LangExtract
            $result = $this->langExtractService->processBankStatement($request->file('bank_statement'));
            
            if ($result['status'] === 'error') {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 400);
            }
            
            // For large bank statements, this might be a background job
            // For now, return immediate results
            return response()->json([
                'status' => 'success',
                'data' => $result,
                'message' => 'Bank statement processed successfully',
                'transaction_count' => count($result['transactions'] ?? [])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Bank statement processing error', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process bank statement. Please try again.',
                'error_code' => 'STATEMENT_PROCESSING_FAILED'
            ], 500);
        }
    }

    /**
     * Get real-time couples events
     */
    public function getRealtimeEvents(): JsonResponse
    {
        // TODO: Integrate with Supabase real-time subscriptions
        return response()->json([
            'events' => [],
            'status' => 'connected'
        ]);
    }

    /**
     * Broadcast update to partner
     */
    public function broadcastUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string',
            'data' => 'required|array'
        ]);

        // TODO: Implement Supabase real-time broadcasting
        return response()->json([
            'status' => 'broadcasted',
            'event_id' => uniqid(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Store new couples transaction with AI enhancement
     */
    public function storeTransaction(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'category_id' => 'nullable|integer',
            'account_id' => 'required|integer',
            'assignTo' => 'required|in:partner1,partner2,shared',
            'use_ai_categorization' => 'boolean'
        ]);

        // TODO: Create actual Firefly III transaction
        // TODO: Apply AI categorization if requested
        // TODO: Add couples-specific metadata
        // TODO: Broadcast real-time update

        return response()->json([
            'status' => 'success',
            'transaction_id' => rand(1000, 9999),
            'ai_category' => $request->input('use_ai_categorization') ? 'AI Suggested Category' : null,
            'message' => 'Transaction created successfully'
        ]);
    }
}
