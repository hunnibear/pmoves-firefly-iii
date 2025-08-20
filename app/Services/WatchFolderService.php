<?php

namespace FireflyIII\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\UploadedFile;
use FireflyIII\Jobs\ProcessWatchFolderDocument;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Watch Folder Service for automated document processing
 * Monitors directories for new files and processes them automatically
 */
class WatchFolderService
{
    private $watchPaths = [];
    private $processedFiles = [];
    private $supportedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'csv', 'xlsx', 'xls', 'txt'];
    private $maxFileSize = 52428800; // 50MB in bytes
    
    public function __construct()
    {
        // Load watch folder configuration
        $this->loadWatchConfiguration();
    }
    
    /**
     * Start monitoring configured watch folders
     */
    public function startWatching(): void
    {
        Log::info('Starting watch folder monitoring', [
            'watch_paths' => $this->watchPaths,
            'supported_extensions' => $this->supportedExtensions
        ]);
        
        foreach ($this->watchPaths as $config) {
            $this->processWatchFolder($config);
        }
    }
    
    /**
     * Process a single watch folder
     */
    private function processWatchFolder(array $config): void
    {
        $path = $config['path'];
        $userId = $config['user_id'];
        $settings = $config['settings'];
        
        if (!is_dir($path)) {
            Log::warning('Watch folder does not exist', ['path' => $path]);
            return;
        }
        
        $files = $this->scanDirectory($path);
        
        foreach ($files as $filePath) {
            if ($this->shouldProcessFile($filePath, $settings)) {
                $this->queueFileForProcessing($filePath, $userId, $settings);
            }
        }
    }
    
    /**
     * Scan directory for supported files
     */
    private function scanDirectory(string $path): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $this->supportedExtensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Check if file should be processed
     */
    private function shouldProcessFile(string $filePath, array $settings): bool
    {
        // Check if already processed
        $fileHash = md5_file($filePath);
        if (in_array($fileHash, $this->processedFiles)) {
            return false;
        }
        
        // Check file size
        if (filesize($filePath) > $this->maxFileSize) {
            Log::warning('File too large for processing', [
                'file' => $filePath,
                'size' => filesize($filePath),
                'max_size' => $this->maxFileSize
            ]);
            return false;
        }
        
        // Check file age (don't process files that are still being written)
        $fileAge = time() - filemtime($filePath);
        $minAge = $settings['min_file_age'] ?? 5; // Default 5 seconds
        
        if ($fileAge < $minAge) {
            return false;
        }
        
        // Check patterns if configured
        if (!empty($settings['include_patterns'])) {
            $fileName = basename($filePath);
            $matches = false;
            
            foreach ($settings['include_patterns'] as $pattern) {
                if (fnmatch($pattern, $fileName)) {
                    $matches = true;
                    break;
                }
            }
            
            if (!$matches) {
                return false;
            }
        }
        
        // Check exclude patterns
        if (!empty($settings['exclude_patterns'])) {
            $fileName = basename($filePath);
            
            foreach ($settings['exclude_patterns'] as $pattern) {
                if (fnmatch($pattern, $fileName)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Queue file for processing
     */
    private function queueFileForProcessing(string $filePath, int $userId, array $settings): void
    {
        $fileHash = md5_file($filePath);
        $this->processedFiles[] = $fileHash;
        
        Log::info('Queueing file for processing', [
            'file' => $filePath,
            'user_id' => $userId,
            'file_hash' => $fileHash
        ]);
        
        // Determine document type based on file and settings
        $documentType = $this->determineDocumentType($filePath, $settings);
        
        // Queue the processing job
        ProcessWatchFolderDocument::dispatch($filePath, $userId, $documentType, $settings);
        
        // Note: File movement (to processed/failed folders) is handled by the job after processing
    }
    
    /**
     * Determine document type from file path and settings
     */
    private function determineDocumentType(string $filePath, array $settings): string
    {
        $fileName = strtolower(basename($filePath));
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Check explicit mappings in settings
        if (!empty($settings['type_mappings'])) {
            foreach ($settings['type_mappings'] as $pattern => $type) {
                if (fnmatch($pattern, $fileName)) {
                    return $type;
                }
            }
        }
        
        // Default logic based on file name patterns
        if (preg_match('/.*statement.*|.*bank.*|.*account.*/i', $fileName)) {
            return 'statement';
        }
        
        if (preg_match('/.*receipt.*|.*invoice.*|.*bill.*/i', $fileName)) {
            return 'receipt';
        }
        
        // Image files default to photo processing
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return 'photo';
        }
        
        // Default to document
        return 'document';
    }
    
    /**
     * Move file to processed folder
     */
    private function moveToProcessedFolder(string $filePath, array $settings): void
    {
        $processedPath = $settings['processed_folder'] ?? dirname($filePath) . '/processed';
        
        if (!is_dir($processedPath)) {
            mkdir($processedPath, 0755, true);
        }
        
        $newPath = $processedPath . '/' . basename($filePath);
        
        // Handle filename conflicts
        $counter = 1;
        while (file_exists($newPath)) {
            $pathInfo = pathinfo($newPath);
            $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
            $counter++;
        }
        
        if (rename($filePath, $newPath)) {
            Log::info('File moved to processed folder', [
                'original' => $filePath,
                'new_path' => $newPath
            ]);
        } else {
            Log::error('Failed to move file to processed folder', [
                'file' => $filePath,
                'target' => $newPath
            ]);
        }
    }
    
    /**
     * Load watch folder configuration
     */
    private function loadWatchConfiguration(): void
    {
        // Load from database or config file
        // For now, use environment variables or default config
        $defaultConfig = [
            [
                'path' => env('WATCH_FOLDER_PATH', storage_path('app/watch')),
                'user_id' => 1, // Default user - should be configurable
                'settings' => [
                    'min_file_age' => 5,
                    'move_after_processing' => true,
                    'processed_folder' => null,
                    'include_patterns' => [],
                    'exclude_patterns' => ['*.tmp', '*.processing'],
                    'type_mappings' => [
                        '*statement*' => 'statement',
                        '*receipt*' => 'receipt',
                        '*invoice*' => 'receipt',
                        '*bank*' => 'statement'
                    ],
                    'auto_create_transactions' => true,
                    'use_vision_model' => true
                ]
            ]
        ];
        
        $this->watchPaths = $defaultConfig;
    }
    
    /**
     * Add a new watch folder configuration
     */
    public function addWatchFolder(string $path, int $userId, array $settings = []): bool
    {
        if (!is_dir($path)) {
            Log::error('Cannot add watch folder - directory does not exist', ['path' => $path]);
            return false;
        }
        
        $config = [
            'path' => $path,
            'user_id' => $userId,
            'settings' => array_merge([
                'min_file_age' => 5,
                'move_after_processing' => true,
                'processed_folder' => null,
                'include_patterns' => [],
                'exclude_patterns' => ['*.tmp', '*.processing'],
                'type_mappings' => [],
                'auto_create_transactions' => false,
                'use_vision_model' => true
            ], $settings)
        ];
        
        $this->watchPaths[] = $config;
        
        Log::info('Added new watch folder', [
            'path' => $path,
            'user_id' => $userId,
            'settings' => $settings
        ]);
        
        return true;
    }
    
    /**
     * Remove watch folder configuration
     */
    public function removeWatchFolder(string $path): bool
    {
        $originalCount = count($this->watchPaths);
        
        $this->watchPaths = array_filter($this->watchPaths, function($config) use ($path) {
            return $config['path'] !== $path;
        });
        
        $removed = count($this->watchPaths) < $originalCount;
        
        if ($removed) {
            Log::info('Removed watch folder', ['path' => $path]);
        }
        
        return $removed;
    }
    
    /**
     * Get current watch folder configurations
     */
    public function getWatchFolders(): array
    {
        return $this->watchPaths;
    }
    
    /**
     * Get processing statistics
     */
    public function getStatistics(): array
    {
        return [
            'watch_folders' => count($this->watchPaths),
            'processed_files' => count($this->processedFiles),
            'supported_extensions' => $this->supportedExtensions,
            'max_file_size' => $this->maxFileSize
        ];
    }
}