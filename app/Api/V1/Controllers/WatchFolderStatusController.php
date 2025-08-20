<?php
declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers;

use FireflyIII\Api\V1\Controllers\Controller;
use FireflyIII\Support\Facades\Steam;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Class WatchFolderStatusController
 */
class WatchFolderStatusController extends Controller
{
    /**
     * Get watch folder status
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            $watchPath = storage_path('app/watch');
            
            $stats = [
                'incoming_files' => $this->countFiles($watchPath . '/incoming'),
                'processed_files' => $this->countFiles($watchPath . '/processed'),
                'failed_files' => $this->countFiles($watchPath . '/failed'),
                'processing_files' => 0, // TODO: implement processing queue count
                'last_processed' => $this->getLastProcessedTime($watchPath . '/processed'),
                'status' => 'active'
            ];

            return response()->json([
                'data' => $stats,
                'links' => [],
                'meta' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting watch folder status: ' . $e->getMessage());
            
            return response()->json([
                'data' => [
                    'incoming_files' => 0,
                    'processed_files' => 0,
                    'failed_files' => 0,
                    'processing_files' => 0,
                    'last_processed' => null,
                    'status' => 'error'
                ],
                'links' => [],
                'meta' => []
            ]);
        }
    }

    /**
     * Count files in a directory
     *
     * @param string $path
     * @return int
     */
    private function countFiles(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $files = glob($path . '/*');
        return count(array_filter($files, 'is_file'));
    }

    /**
     * Get last processed time
     *
     * @param string $path
     * @return string|null
     */
    private function getLastProcessedTime(string $path): ?string
    {
        if (!is_dir($path)) {
            return null;
        }

        $files = glob($path . '/*');
        $files = array_filter($files, 'is_file');
        
        if (empty($files)) {
            return null;
        }

        $latestFile = '';
        $latestTime = 0;
        
        foreach ($files as $file) {
            $fileTime = filemtime($file);
            if ($fileTime > $latestTime) {
                $latestTime = $fileTime;
                $latestFile = $file;
            }
        }

        return $latestTime > 0 ? date('Y-m-d H:i:s', $latestTime) : null;
    }
}