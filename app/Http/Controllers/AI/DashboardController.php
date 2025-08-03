<?php

/**
 * DashboardController.php
 * Copyright (c) 2025 AI Integration
 *
 * This file is part of Firefly III AI Integration.
 */

declare(strict_types=1);

namespace FireflyIII\Http\Controllers\AI;

use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Services\Internal\AIService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
        
        $this->middleware(function ($request, $next) {
            app('view')->share('title', 'AI Dashboard');
            app('view')->share('mainTitleIcon', 'fa-robot');
            return $next($request);
        });
    }

    /**
     * Show the AI dashboard
     */
    public function index(): View
    {
        return view('ai.dashboard');
    }

    /**
     * Test AI connectivity
     */
    public function testConnectivity(): JsonResponse
    {
        try {
            $status = $this->aiService->testConnectivity();
            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI insights for the user
     */
    public function getInsights(): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            // For now, return sample insights until we implement transaction fetching
            $insights = [
                "Your spending this month is 15% higher than last month.",
                "Consider setting up a budget for the 'Dining Out' category.",
                "You've saved $250 more than your target this month - great job!"
            ];
            
            return response()->json([
                'success' => true,
                'insights' => $insights
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Chat with AI assistant
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            $message = $request->input('message');
            $userId = auth()->id();
            
            // Get user's financial context (you could add more context here later)
            $context = [
                'user_id' => $userId,
                // Add more financial context as needed
            ];
            
            $response = $this->aiService->chat($message, $context);
            
            return response()->json([
                'success' => true,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Categorize transaction with AI
     */
    public function categorizeTransaction(Request $request): JsonResponse
    {
        try {
            $description = $request->input('description');
            $amount = $request->input('amount');
            
            // For now, provide simple rule-based categorization until full AI integration
            $category = $this->simpleCategorize($description, $amount);
            
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simple categorization logic
     */
    private function simpleCategorize(string $description, ?float $amount): string
    {
        $description = strtolower($description);
        
        if (str_contains($description, 'grocery') || str_contains($description, 'food') || str_contains($description, 'supermarket')) {
            return 'Groceries';
        }
        if (str_contains($description, 'gas') || str_contains($description, 'fuel') || str_contains($description, 'petrol')) {
            return 'Transportation';
        }
        if (str_contains($description, 'restaurant') || str_contains($description, 'cafe') || str_contains($description, 'dining')) {
            return 'Dining Out';
        }
        if (str_contains($description, 'utility') || str_contains($description, 'electric') || str_contains($description, 'water')) {
            return 'Utilities';
        }
        if (str_contains($description, 'rent') || str_contains($description, 'mortgage')) {
            return 'Housing';
        }
        
        return 'Miscellaneous';
    }

    /**
     * Detect spending anomalies
     */
    public function detectAnomalies(): JsonResponse
    {
        try {
            // For now, return sample anomalies until we implement transaction analysis
            $anomalies = [
                [
                    'type' => 'Unusual Spending',
                    'description' => 'Shopping expenses were 40% higher than usual this week',
                    'amount' => '$234.50'
                ],
                [
                    'type' => 'Duplicate Transaction',
                    'description' => 'Possible duplicate charge at Coffee Shop on Main St',
                    'amount' => '$4.25'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'anomalies' => $anomalies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
