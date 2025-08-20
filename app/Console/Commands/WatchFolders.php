<?php

namespace FireflyIII\Console\Commands;

use Illuminate\Console\Command;
use FireflyIII\Services\WatchFolderService;
use Illuminate\Support\Facades\Log;

/**
 * Console command to run watch folder monitoring
 */
class WatchFolders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'watch-folders:run 
                          {--interval=30 : Monitoring interval in seconds}
                          {--once : Run once instead of continuous monitoring}
                          {--path= : Specific path to monitor (overrides config)}
                          {--user= : User ID for processing (required if using --path)}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor watch folders for new documents and process them automatically';

    /**
     * Watch folder service instance
     */
    private WatchFolderService $watchFolderService;

    /**
     * Create a new command instance.
     */
    public function __construct(WatchFolderService $watchFolderService)
    {
        parent::__construct();
        $this->watchFolderService = $watchFolderService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $runOnce = $this->option('once');
        $specificPath = $this->option('path');
        $userId = $this->option('user');

        // Handle specific path monitoring
        if ($specificPath) {
            if (!$userId) {
                $this->error('User ID is required when monitoring a specific path');
                return 1;
            }

            return $this->monitorSpecificPath($specificPath, (int) $userId, $interval, $runOnce);
        }

        // Regular monitoring
        $this->info('Starting watch folder monitoring...');
        $this->info("Monitoring interval: {$interval} seconds");
        $this->info("Run once mode: " . ($runOnce ? 'Yes' : 'No'));

        // Display current configuration
        $this->displayWatchFolderConfiguration();

        do {
            $startTime = microtime(true);
            
            try {
                $this->watchFolderService->startWatching();
                
                $processingTime = round((microtime(true) - $startTime), 2);
                $this->info("Watch cycle completed in {$processingTime}s");

            } catch (\Exception $e) {
                $this->error("Error during watch cycle: " . $e->getMessage());
                Log::error('Watch folder monitoring error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            if (!$runOnce) {
                $this->info("Waiting {$interval} seconds until next cycle...");
                sleep($interval);
            }

        } while (!$runOnce);

        $this->info('Watch folder monitoring completed');
        return 0;
    }

    /**
     * Monitor a specific path
     */
    private function monitorSpecificPath(string $path, int $userId, int $interval, bool $runOnce): int
    {
        if (!is_dir($path)) {
            $this->error("Directory does not exist: {$path}");
            return 1;
        }

        $this->info("Monitoring specific path: {$path}");
        $this->info("User ID: {$userId}");

        // Add temporary watch folder configuration
        $settings = [
            'auto_create_transactions' => true,
            'use_vision_model' => true,
            'move_after_processing' => true
        ];

        $this->watchFolderService->addWatchFolder($path, $userId, $settings);

        do {
            $startTime = microtime(true);
            
            try {
                $this->watchFolderService->startWatching();
                
                $processingTime = round((microtime(true) - $startTime), 2);
                $this->info("Processing completed in {$processingTime}s");

            } catch (\Exception $e) {
                $this->error("Error during processing: " . $e->getMessage());
                Log::error('Specific path monitoring error', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            }

            if (!$runOnce) {
                sleep($interval);
            }

        } while (!$runOnce);

        return 0;
    }

    /**
     * Display current watch folder configuration
     */
    private function displayWatchFolderConfiguration(): void
    {
        $watchFolders = $this->watchFolderService->getWatchFolders();
        $statistics = $this->watchFolderService->getStatistics();

        $this->info('=== Watch Folder Configuration ===');
        
        if (empty($watchFolders)) {
            $this->warn('No watch folders configured');
            return;
        }

        foreach ($watchFolders as $index => $config) {
            $this->info("Watch Folder #" . ($index + 1) . ":");
            $this->line("  Path: {$config['path']}");
            $this->line("  User ID: {$config['user_id']}");
            $this->line("  Auto Create Transactions: " . ($config['settings']['auto_create_transactions'] ? 'Yes' : 'No'));
            $this->line("  Use Vision Model: " . ($config['settings']['use_vision_model'] ? 'Yes' : 'No'));
            $this->line("  Move After Processing: " . ($config['settings']['move_after_processing'] ? 'Yes' : 'No'));
            
            if (!empty($config['settings']['include_patterns'])) {
                $this->line("  Include Patterns: " . implode(', ', $config['settings']['include_patterns']));
            }
            
            if (!empty($config['settings']['exclude_patterns'])) {
                $this->line("  Exclude Patterns: " . implode(', ', $config['settings']['exclude_patterns']));
            }
            
            $this->line('');
        }

        $this->info('=== Statistics ===');
        $this->line("Supported Extensions: " . implode(', ', $statistics['supported_extensions']));
        $this->line("Max File Size: " . number_format($statistics['max_file_size'] / 1024 / 1024, 1) . " MB");
        $this->line("Total Watch Folders: " . $statistics['watch_folders']);
        $this->line('');
    }
}