<?php

declare(strict_types=1);

namespace FireflyIII\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Process Agent Event Job
 * 
 * Handles communication with the Python-based Transaction Intelligence Agent
 * Processes events asynchronously through the queue system
 */
class ProcessAgentEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The maximum number of seconds the job should run.
     */
    public int $timeout = 120;
    
    /**
     * Event data to be processed by the agent
     */
    protected array $eventData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;
        $this->onQueue('agent_processing');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing agent event', [
            'event_type' => $this->eventData['event_type'] ?? 'unknown',
            'source' => $this->eventData['source'] ?? 'unknown'
        ]);

        try {
            // Send event to Python Transaction Intelligence Agent
            $response = $this->sendToAgent($this->eventData);
            
            if ($response->successful()) {
                $result = $response->json();
                $this->handleAgentResponse($result);
                
                // Update metrics
                cache()->increment('agent.processed_count_today');
                cache()->put('agent.last_processed_at', now());
                
                Log::info('Agent event processed successfully', [
                    'event_type' => $this->eventData['event_type'],
                    'agent_response' => $result
                ]);
                
            } else {
                throw new \Exception("Agent service returned error: {$response->status()}");
            }
            
        } catch (\Exception $e) {
            Log::error('Agent event processing failed', [
                'event_data' => $this->eventData,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            // Update error metrics
            $this->updateErrorMetrics();
            
            // Re-throw to trigger retry logic
            throw $e;
        }
    }
    
    /**
     * Send event data to the Python Transaction Intelligence Agent
     *
     * @param array $eventData
     * @return \Illuminate\Http\Client\Response
     */
    private function sendToAgent(array $eventData): \Illuminate\Http\Client\Response
    {
        $agentServiceUrl = config('agent.service_url', 'http://localhost:8000');
        
        return Http::timeout(60)
            ->retry(2, 1000)
            ->post("{$agentServiceUrl}/api/process-event", [
                'event' => $eventData,
                'user_context' => $this->getUserContext(),
                'firefly_config' => $this->getFireflyConfig()
            ]);
    }
    
    /**
     * Handle the response from the agent service
     *
     * @param array $agentResponse
     */
    private function handleAgentResponse(array $agentResponse): void
    {
        $actions = $agentResponse['actions'] ?? [];
        
        foreach ($actions as $action) {
            $this->executeAction($action);
        }
        
        // Store agent insights if provided
        if (isset($agentResponse['insights'])) {
            $this->storeAgentInsights($agentResponse['insights']);
        }
        
        // Update rules if agent suggests improvements
        if (isset($agentResponse['rule_suggestions'])) {
            $this->processRuleSuggestions($agentResponse['rule_suggestions']);
        }
    }
    
    /**
     * Execute action suggested by the agent
     *
     * @param array $action
     */
    private function executeAction(array $action): void
    {
        $actionType = $action['type'] ?? '';
        
        switch ($actionType) {
            case 'categorize_transaction':
                $this->categorizeTransaction($action['data']);
                break;
                
            case 'create_rule':
                $this->createRule($action['data']);
                break;
                
            case 'flag_anomaly':
                $this->flagAnomaly($action['data']);
                break;
                
            case 'update_tag':
                $this->updateTag($action['data']);
                break;
                
            default:
                Log::warning('Unknown agent action type', ['action' => $action]);
        }
    }
    
    /**
     * Categorize transaction based on agent analysis
     *
     * @param array $data
     */
    private function categorizeTransaction(array $data): void
    {
        // TODO: Implement transaction categorization
        Log::info('Agent categorization action', $data);
    }
    
    /**
     * Create a new rule based on agent suggestion
     *
     * @param array $data
     */
    private function createRule(array $data): void
    {
        // TODO: Implement rule creation
        Log::info('Agent rule creation action', $data);
    }
    
    /**
     * Flag transaction as anomaly
     *
     * @param array $data
     */
    private function flagAnomaly(array $data): void
    {
        // TODO: Implement anomaly flagging
        Log::info('Agent anomaly flag action', $data);
    }
    
    /**
     * Update transaction tags
     *
     * @param array $data
     */
    private function updateTag(array $data): void
    {
        // TODO: Implement tag updates
        Log::info('Agent tag update action', $data);
    }
    
    /**
     * Store agent insights for future reference
     *
     * @param array $insights
     */
    private function storeAgentInsights(array $insights): void
    {
        // TODO: Implement insights storage
        Log::info('Storing agent insights', $insights);
    }
    
    /**
     * Process rule suggestions from agent
     *
     * @param array $suggestions
     */
    private function processRuleSuggestions(array $suggestions): void
    {
        // TODO: Implement rule suggestion processing
        Log::info('Processing rule suggestions', $suggestions);
    }
    
    /**
     * Get user context for agent processing
     *
     * @return array
     */
    private function getUserContext(): array
    {
        // TODO: Extract user preferences and context
        return [
            'user_id' => $this->eventData['event_data']['user_id'] ?? null,
            'preferences' => [],
            'rules' => [],
            'categories' => []
        ];
    }
    
    /**
     * Get Firefly III configuration for agent
     *
     * @return array
     */
    private function getFireflyConfig(): array
    {
        return [
            'api_url' => config('app.url'),
            'version' => config('firefly.version'),
            'currency' => config('firefly.default_currency')
        ];
    }
    
    /**
     * Update error metrics for monitoring
     */
    private function updateErrorMetrics(): void
    {
        $errorKey = 'agent.errors_24h';
        $totalKey = 'agent.total_24h';
        
        cache()->increment($errorKey);
        cache()->increment($totalKey);
        
        // Calculate error rate
        $errors = cache()->get($errorKey, 0);
        $total = cache()->get($totalKey, 1);
        $errorRate = $errors / max($total, 1);
        
        cache()->put('agent.error_rate_24h', $errorRate, now()->addDay());
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Agent event job failed permanently', [
            'event_data' => $this->eventData,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
        
        // TODO: Implement fallback handling or user notification
    }
}