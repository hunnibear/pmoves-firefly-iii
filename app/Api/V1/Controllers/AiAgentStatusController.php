<?php
declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers;

use FireflyIII\Api\V1\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Class AiAgentStatusController
 */
class AiAgentStatusController extends Controller
{
    /**
     * Get AI agent status
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            // Check if AI agent is running
            $agentUrl = config('firefly.ai_agent_url', 'http://firefly-agent:8000');
            $isRunning = $this->checkAgentHealth($agentUrl);
            
            $stats = [
                'status' => $isRunning ? 'running' : 'stopped',
                'processed_today' => $this->getProcessedToday(),
                'total_processed' => $this->getTotalProcessed(),
                'last_processed' => $this->getLastProcessedTime(),
                'agent_url' => $agentUrl,
                'last_checked' => now()->toISOString()
            ];

            return response()->json([
                'data' => $stats,
                'links' => [],
                'meta' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting AI agent status: ' . $e->getMessage());
            
            return response()->json([
                'data' => [
                    'status' => 'error',
                    'processed_today' => 0,
                    'total_processed' => 0,
                    'last_processed' => null,
                    'agent_url' => null,
                    'last_checked' => now()->toISOString(),
                    'error' => $e->getMessage()
                ],
                'links' => [],
                'meta' => []
            ]);
        }
    }

    /**
     * Check if AI agent is healthy
     *
     * @param string $agentUrl
     * @return bool
     */
    private function checkAgentHealth(string $agentUrl): bool
    {
        try {
            $response = Http::timeout(5)->get($agentUrl . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            Log::debug('AI agent health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get documents processed today
     *
     * @return int
     */
    private function getProcessedToday(): int
    {
        // Get from cache or calculate
        $cacheKey = 'ai_agent_processed_today_' . date('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            // Check processed files from today
            $processedPath = storage_path('app/watch/processed');
            
            if (!is_dir($processedPath)) {
                return 0;
            }

            $files = glob($processedPath . '/*');
            $todayFiles = 0;
            $today = Carbon::today();
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = Carbon::createFromTimestamp(filemtime($file));
                    if ($fileTime->isSameDay($today)) {
                        $todayFiles++;
                    }
                }
            }
            
            return $todayFiles;
        });
    }

    /**
     * Get total processed documents
     *
     * @return int
     */
    private function getTotalProcessed(): int
    {
        // Get from cache or calculate
        return Cache::remember('ai_agent_total_processed', 1800, function () {
            $processedPath = storage_path('app/watch/processed');
            
            if (!is_dir($processedPath)) {
                return 0;
            }

            $files = glob($processedPath . '/*');
            return count(array_filter($files, 'is_file'));
        });
    }

    /**
     * Get last processed time
     *
     * @return string|null
     */
    private function getLastProcessedTime(): ?string
    {
        $processedPath = storage_path('app/watch/processed');
        
        if (!is_dir($processedPath)) {
            return null;
        }

        $files = glob($processedPath . '/*');
        $files = array_filter($files, 'is_file');
        
        if (empty($files)) {
            return null;
        }

        $latestTime = 0;
        
        foreach ($files as $file) {
            $fileTime = filemtime($file);
            if ($fileTime > $latestTime) {
                $latestTime = $fileTime;
            }
        }

        return $latestTime > 0 ? Carbon::createFromTimestamp($latestTime)->toISOString() : null;
    }
}