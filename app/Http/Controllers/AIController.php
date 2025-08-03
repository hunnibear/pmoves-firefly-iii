<?php

declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use FireflyIII\Services\Internal\AIService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class AIController
 *
 * Handles AI dashboard and features for Firefly III
 */
class AIController extends Controller
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->middleware('auth');
        $this->aiService = $aiService;
    }

    /**
     * Show the AI dashboard
     */
    public function index(): View
    {
        return view('ai.index');
    }

    /**
     * AI Chat interface
     */
    public function chat(): View
    {
        return view('ai.chat');
    }

    /**
     * Handle chat messages
     */
    public function handleChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $response = $this->aiService->chat($request->input('message'));
            
            return response()->json([
                'success' => true,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'AI service unavailable: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test AI connectivity
     */
    public function testConnection()
    {
        try {
            $result = $this->aiService->testConnectivity();
            
            return response()->json([
                'success' => true,
                'status' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
