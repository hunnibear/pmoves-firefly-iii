#!/usr/bin/env php
<?php

/**
 * Watch Folder Setup Script
 * Initializes the watch folder system and creates necessary directories
 */

// Bootstrap Laravel
require __DIR__ . '/bootstrap/app.php';
$app = $app ?? app();

use Illuminate\Support\Facades\Log;
use App\Services\WatchFolderService;

echo "🗂️  Firefly III Enhanced Document Processing - Watch Folder Setup\n";
echo "================================================================\n\n";

// Get watch folder service
$watchFolderService = app(WatchFolderService::class);

// Default watch folder path
$defaultWatchPath = storage_path('app/watch');
$processedPath = storage_path('app/watch/processed');
$failedPath = storage_path('app/watch/failed');

echo "Setting up watch folder system...\n";

// Create directories
$directories = [
    $defaultWatchPath => 'Main watch folder',
    $processedPath => 'Processed files folder',
    $failedPath => 'Failed files folder'
];

foreach ($directories as $path => $description) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "✅ Created: $description ($path)\n";
        } else {
            echo "❌ Failed to create: $description ($path)\n";
            exit(1);
        }
    } else {
        echo "✅ Exists: $description ($path)\n";
    }
}

echo "\n";

// Create sample configuration
$sampleConfig = [
    'path' => $defaultWatchPath,
    'user_id' => 1, // Default user
    'settings' => [
        'auto_create_transactions' => false, // Start with manual review
        'use_vision_model' => true,
        'move_after_processing' => true,
        'processed_folder' => $processedPath,
        'min_file_age' => 5,
        'include_patterns' => [],
        'exclude_patterns' => ['*.tmp', '*.processing', '*.failed.*'],
        'type_mappings' => [
            '*statement*' => 'statement',
            '*receipt*' => 'receipt',
            '*invoice*' => 'receipt',
            '*bank*' => 'statement'
        ]
    ]
];

// Add default watch folder
try {
    $watchFolderService->addWatchFolder(
        $sampleConfig['path'],
        $sampleConfig['user_id'],
        $sampleConfig['settings']
    );
    echo "✅ Added default watch folder configuration\n";
} catch (Exception $e) {
    echo "⚠️  Watch folder already configured or error occurred: " . $e->getMessage() . "\n";
}

echo "\n";

// Create sample files for testing
$sampleFiles = [
    'sample_receipt.txt' => "Sample Receipt\nMerchant: Test Store\nDate: " . date('Y-m-d') . "\nAmount: $25.99\nItems: Test item 1, Test item 2",
    'README.txt' => "Watch Folder Instructions\n\n1. Drop supported files (JPG, PNG, PDF, CSV, XLSX, XLS, TXT) into this folder\n2. Files will be automatically processed\n3. Processed files are moved to the 'processed' subfolder\n4. Failed files get '.failed' extension\n\nSupported document types:\n- Receipts (any image or text file with receipt content)\n- Bank statements (PDF, CSV, XLSX files)\n- Photos (JPG, PNG files - will use vision model)\n- General documents (any supported format)\n\nTo start monitoring, run:\nphp artisan watch-folders:run\n\nFor continuous monitoring:\nphp artisan watch-folders:run --interval=30\n\nFor one-time scan:\nphp artisan watch-folders:run --once"
];

foreach ($sampleFiles as $filename => $content) {
    $filePath = $defaultWatchPath . '/' . $filename;
    if (!file_exists($filePath)) {
        if (file_put_contents($filePath, $content) !== false) {
            echo "✅ Created sample file: $filename\n";
        } else {
            echo "❌ Failed to create sample file: $filename\n";
        }
    } else {
        echo "✅ Sample file exists: $filename\n";
    }
}

echo "\n";

// Display configuration
echo "📋 Watch Folder Configuration:\n";
echo "================================\n";
echo "Watch Path: $defaultWatchPath\n";
echo "Processed Path: $processedPath\n";
echo "Auto Create Transactions: " . ($sampleConfig['settings']['auto_create_transactions'] ? 'Yes' : 'No') . "\n";
echo "Use Vision Model: " . ($sampleConfig['settings']['use_vision_model'] ? 'Yes' : 'No') . "\n";
echo "Move After Processing: " . ($sampleConfig['settings']['move_after_processing'] ? 'Yes' : 'No') . "\n";
echo "Minimum File Age: " . $sampleConfig['settings']['min_file_age'] . " seconds\n";

echo "\n";

// Show commands
echo "🚀 Getting Started:\n";
echo "===================\n";
echo "1. Start watch folder monitoring:\n";
echo "   php artisan watch-folders:run\n\n";
echo "2. Run once to process existing files:\n";
echo "   php artisan watch-folders:run --once\n\n";
echo "3. Monitor specific folder:\n";
echo "   php artisan watch-folders:run --path=/path/to/folder --user=1\n\n";
echo "4. Check system status:\n";
echo "   curl http://localhost:8080/api/v1/couples/watch-folders/status\n\n";

// Environment recommendations
echo "⚙️  Environment Setup:\n";
echo "======================\n";
echo "For production use, consider:\n";
echo "1. Setting up a cron job for continuous monitoring\n";
echo "2. Configuring queue workers for background processing\n";
echo "3. Setting appropriate file permissions\n";
echo "4. Monitoring disk space in watch folders\n\n";

echo "📁 Sample Cron Job (every minute):\n";
echo "* * * * * cd " . base_path() . " && php artisan watch-folders:run --once\n\n";

echo "🔄 Queue Worker (for background processing):\n";
echo "php artisan queue:work --timeout=300\n\n";

// Docker integration
if (file_exists(base_path('docker-compose.yml'))) {
    echo "🐳 Docker Integration:\n";
    echo "=====================\n";
    echo "For Docker users, you can mount your local watch folder:\n";
    echo "Add to your docker-compose.yml:\n";
    echo "volumes:\n";
    echo "  - ./watch:/var/www/html/storage/app/watch\n\n";
}

echo "✅ Watch folder setup complete!\n";
echo "Drop some files into $defaultWatchPath and run the monitoring command to test.\n\n";