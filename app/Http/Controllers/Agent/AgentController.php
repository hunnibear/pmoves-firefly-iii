<?php

declare(strict_types=1);

namespace FireflyIII\Http\Controllers\Agent;

use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Jobs\ProcessAgentEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Agent Controller - Phase 1: Transaction Intelligence Agent
 * 
 * Handles webhook events from Firefly III and dispatches them to the 
 * Python-based Transaction Intelligence Agent for processing.
 */
class AgentController extends Controller
{
    /**
     * Handle incoming webhook events from Firefly III
     * 
     * This endpoint receives transaction events and dispatches them
     * to the agent processing queue for intelligent analysis.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleFireflyWebhook(Request $request): JsonResponse
    {
        Log::info('Agent webhook received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        try {
            // Validate webhook signature and source
            $this->validateWebhookSignature($request);
            
            // Extract event data
            $eventData = $request->all();
            $eventType = $request->header('X-Firefly-Event-Type', 'unknown');
            
            // Dispatch to agent processing queue
            ProcessAgentEvent::dispatch([
                'event_type' => $eventType,
                'event_data' => $eventData,
                'timestamp' => now()->toISOString(),
                'source' => 'firefly_webhook'
            ]);
            
            Log::info('Agent event dispatched', [
                'event_type' => $eventType,
                'data_keys' => array_keys($eventData)
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Event queued for agent processing',
                'event_type' => $eventType
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Agent webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook event',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get agent status and metrics
     *
     * @return JsonResponse
     */
    public function getAgentStatus(): JsonResponse
    {
        try {
            // TODO: Implement agent status check via Python service
            $status = [
                'agent_status' => 'active',
                'queue_length' => Queue::size('agent_processing'),
                'last_processed' => cache('agent.last_processed_at'),
                'processed_today' => cache('agent.processed_count_today', 0),
                'error_rate' => cache('agent.error_rate_24h', 0.0)
            ];
            
            return response()->json($status);
            
        } catch (\Exception $e) {
            Log::error('Failed to get agent status', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get agent status'
            ], 500);
        }
    }
    
    /**
     * Trigger manual agent analysis for a specific transaction
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function triggerManualAnalysis(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|integer',
            'analysis_type' => 'required|string|in:categorization,anomaly,rule_optimization'
        ]);
        
        try {
            ProcessAgentEvent::dispatch([
                'event_type' => 'manual_analysis',
                'event_data' => [
                    'transaction_id' => $request->transaction_id,
                    'analysis_type' => $request->analysis_type,
                    'user_id' => auth()->id()
                ],
                'timestamp' => now()->toISOString(),
                'source' => 'manual_trigger'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Manual analysis queued'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Manual analysis trigger failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $request->transaction_id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to trigger analysis'
            ], 500);
        }
    }
    
    /**
     * Validate webhook signature for security
     *
     * @param Request $request
     * @throws \Exception
     */
    private function validateWebhookSignature(Request $request): void
    {
        // TODO: Implement proper webhook signature validation
        // For now, just validate that it's coming from localhost or authorized source
        
        $allowedIps = ['127.0.0.1', '::1', 'localhost'];
        $clientIp = $request->ip();
        
        if (!in_array($clientIp, $allowedIps) && !config('app.debug')) {
            Log::warning('Unauthorized webhook attempt', ['ip' => $clientIp]);
            throw new \Exception('Unauthorized webhook source');
        }
    }
}