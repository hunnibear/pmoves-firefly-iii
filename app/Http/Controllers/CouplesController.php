<?php

declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use FireflyIII\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CouplesController extends Controller
{
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
     * Process uploaded receipt with LangExtract integration
     */
    public function uploadReceipt(Request $request): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240' // 10MB max
        ]);

        // TODO: Integrate with LangExtract service
        // For now, return mock extracted data
        $mockExtractedData = [
            'merchant' => 'Example Store',
            'amount' => 42.50,
            'date' => now()->format('Y-m-d'),
            'category' => 'Groceries',
            'items' => [
                'Bread - $3.50',
                'Milk - $4.25', 
                'Eggs - $3.75'
            ],
            'tax_amount' => 3.40,
            'payment_method' => 'Credit Card'
        ];

        $aiSuggestions = [
            'category' => 'Groceries',
            'partner_assignment' => 'shared',
            'confidence' => 92
        ];

        return response()->json([
            'status' => 'success',
            'extracted_data' => $mockExtractedData,
            'ai_suggestions' => $aiSuggestions,
            'processing_time' => '2.3s'
        ]);
    }

    /**
     * Process bank statement upload
     */
    public function processBankStatement(Request $request): JsonResponse
    {
        $request->validate([
            'bank_statement' => 'required|file|mimes:pdf,csv|max:50240' // 50MB max
        ]);

        // TODO: Integrate with LangExtract for bank statement processing
        // Mock response for now
        return response()->json([
            'status' => 'processing',
            'job_id' => 'bs_' . uniqid(),
            'estimated_completion' => '30 seconds',
            'message' => 'Bank statement processing started. You will be notified when complete.'
        ]);
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
