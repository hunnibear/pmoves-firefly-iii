<?php

namespace FireflyIII\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use FireflyIII\Services\WatchFolderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for managing watch folder configurations
 */
class WatchFolderController extends Controller
{
    private WatchFolderService $watchFolderService;

    public function __construct(WatchFolderService $watchFolderService)
    {
        $this->middleware('auth');
        $this->watchFolderService = $watchFolderService;
    }

    /**
     * Get all watch folder configurations
     */
    public function index(): JsonResponse
    {
        try {
            $watchFolders = $this->watchFolderService->getWatchFolders();
            $statistics = $this->watchFolderService->getStatistics();

            return response()->json([
                'status' => 'success',
                'watch_folders' => $watchFolders,
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get watch folders', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve watch folders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a new watch folder
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string|max:500',
                'auto_create_transactions' => 'boolean',
                'use_vision_model' => 'boolean',
                'move_after_processing' => 'boolean',
                'min_file_age' => 'integer|min:1|max:300',
                'include_patterns' => 'array',
                'include_patterns.*' => 'string|max:100',
                'exclude_patterns' => 'array',
                'exclude_patterns.*' => 'string|max:100',
                'type_mappings' => 'array',
                'processed_folder' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->input('path');
            $userId = auth()->id();

            // Check if directory exists
            if (!is_dir($path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Directory does not exist',
                    'path' => $path
                ], 400);
            }

            // Check if directory is readable
            if (!is_readable($path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Directory is not readable',
                    'path' => $path
                ], 400);
            }

            $settings = [
                'auto_create_transactions' => $request->boolean('auto_create_transactions', false),
                'use_vision_model' => $request->boolean('use_vision_model', true),
                'move_after_processing' => $request->boolean('move_after_processing', true),
                'min_file_age' => $request->input('min_file_age', 5),
                'include_patterns' => $request->input('include_patterns', []),
                'exclude_patterns' => $request->input('exclude_patterns', ['*.tmp', '*.processing']),
                'type_mappings' => $request->input('type_mappings', []),
                'processed_folder' => $request->input('processed_folder')
            ];

            $success = $this->watchFolderService->addWatchFolder($path, $userId, $settings);

            if ($success) {
                Log::info('Watch folder added via API', [
                    'user_id' => $userId,
                    'path' => $path,
                    'settings' => $settings
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Watch folder added successfully',
                    'path' => $path,
                    'settings' => $settings
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add watch folder'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to add watch folder', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add watch folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a watch folder
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->input('path');
            $success = $this->watchFolderService->removeWatchFolder($path);

            if ($success) {
                Log::info('Watch folder removed via API', [
                    'user_id' => auth()->id(),
                    'path' => $path
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Watch folder removed successfully',
                    'path' => $path
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Watch folder not found or could not be removed'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Failed to remove watch folder', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove watch folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test a watch folder path
     */
    public function testPath(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->input('path');
            $results = [];

            // Check if directory exists
            $results['exists'] = is_dir($path);
            if (!$results['exists']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Directory does not exist',
                    'path' => $path,
                    'results' => $results
                ]);
            }

            // Check permissions
            $results['readable'] = is_readable($path);
            $results['writable'] = is_writable($path);

            // Scan for files
            $supportedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'csv', 'xlsx', 'xls', 'txt'];
            $files = [];
            $totalSize = 0;

            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $extension = strtolower($file->getExtension());
                        if (in_array($extension, $supportedExtensions)) {
                            $fileSize = $file->getSize();
                            $files[] = [
                                'name' => $file->getFilename(),
                                'path' => $file->getPathname(),
                                'extension' => $extension,
                                'size' => $fileSize,
                                'modified' => date('Y-m-d H:i:s', $file->getMTime())
                            ];
                            $totalSize += $fileSize;
                        }
                    }
                }

                $results['files_found'] = count($files);
                $results['total_size'] = $totalSize;
                $results['sample_files'] = array_slice($files, 0, 10); // First 10 files

            } catch (\Exception $e) {
                $results['scan_error'] = $e->getMessage();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Path test completed',
                'path' => $path,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to test watch folder path', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to test path',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually trigger processing for a watch folder
     */
    public function triggerProcessing(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $specificPath = $request->input('path');

            if ($specificPath) {
                // Process specific path
                if (!is_dir($specificPath)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Directory does not exist'
                    ], 400);
                }

                // Add temporary watch folder and process
                $this->watchFolderService->addWatchFolder($specificPath, auth()->id(), [
                    'auto_create_transactions' => true,
                    'use_vision_model' => true
                ]);
            }

            // Trigger processing
            $this->watchFolderService->startWatching();

            Log::info('Manual watch folder processing triggered', [
                'user_id' => auth()->id(),
                'specific_path' => $specificPath
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Processing triggered successfully',
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger watch folder processing', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to trigger processing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get processing statistics and status
     */
    public function status(): JsonResponse
    {
        try {
            $statistics = $this->watchFolderService->getStatistics();
            
            // Add system information and return a stable envelope
            $status = [
                'statistics' => $statistics,
                'system_info' => [
                    'php_version' => PHP_VERSION,
                    'memory_usage' => memory_get_usage(true),
                    'memory_limit' => ini_get('memory_limit'),
                    'queue_connection' => config('queue.default'),
                    'timezone' => config('app.timezone'),
                    'environment' => app()->environment()
                ],
                'queue_status' => [
                    'pending_jobs' => 0, // Would need to query queue
                    'failed_jobs' => 0   // Would need to query failed jobs
                ]
            ];

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->toISOString(),
                'data' => $status,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get watch folder status', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}