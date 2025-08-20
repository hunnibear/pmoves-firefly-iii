# 🗂️ Watch Folder System - Enhanced Document Processing

## Overview

The Watch Folder system provides automated document processing for Firefly III, allowing you to drop documents into monitored directories and have them automatically processed using AI-powered document analysis.

## Features

- **Automated Monitoring**: Continuously watch directories for new documents
- **Multi-Format Support**: Process JPG, PNG, PDF, CSV, XLSX, XLS, TXT files
- **AI Vision Processing**: Analyze images and photos using LLAVA vision model
- **Queue-Based Processing**: Background processing to handle multiple documents
- **Web Management**: REST API for managing watch folders
- **CLI Tools**: Command-line interface for monitoring and administration
- **Configurable Rules**: File patterns, user mapping, and processing options

## Quick Start

### 1. Setup

**Windows (PowerShell):**
```powershell
.\watch-folders.ps1 -Action setup
```

**Linux/macOS:**
```bash
php setup-watch-folders.php
```

### 2. Start Monitoring

**Continuous monitoring:**
```powershell
# Windows
.\watch-folders.ps1 -Action start

# Linux/macOS
php artisan watch-folders:run --interval=30
```

**One-time processing:**
```powershell
# Windows
.\watch-folders.ps1 -Action start -Once

# Linux/macOS
php artisan watch-folders:run --once
```

### 3. Drop Documents

Place supported documents in the watch folder:
```
storage/app/watch/
├── receipts/
├── bank-statements/
├── invoices/
└── other/
```

## System Architecture

### Components

1. **WatchFolderService** - Core directory monitoring service
2. **ProcessWatchFolderDocument** - Queue job for background processing
3. **WatchFolders Command** - CLI interface for monitoring
4. **WatchFolderController** - REST API for management
5. **LangExtract Integration** - AI-powered document analysis

### Processing Flow

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Drop File     │───▶│  File Detected  │───▶│ Queue Job Added │
│  in Watch Dir   │    │  by Service     │    │  for Processing │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                        │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Transaction     │◀───│   AI Analysis   │◀───│ Background Job  │
│ Created in FF   │    │  (LangExtract)  │    │   Processes     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Configuration

### Watch Folder Configuration

Edit your watch folder settings:

```php
// Default configuration in WatchFolderService
$config = [
    'path' => storage_path('app/watch'),
    'patterns' => ['*.jpg', '*.jpeg', '*.png', '*.pdf', '*.csv', '*.xlsx', '*.xls', '*.txt'],
    'auto_create_transactions' => true,
    'use_vision_model' => true,
    'move_after_processing' => true,
    'processed_folder' => 'processed',
    'error_folder' => 'errors',
    'file_age_minutes' => 1
];
```

### Environment Variables

Add to your `.env` file:

```env
# Queue Configuration
QUEUE_CONNECTION=database

# AI Processing
LANGEXTRACT_API_URL=http://localhost:8001
OLLAMA_BASE_URL=http://localhost:11434

# Watch Folder Settings
WATCH_FOLDER_ENABLED=true
WATCH_FOLDER_INTERVAL=30
WATCH_FOLDER_FILE_AGE=1
```

## Usage Examples

### CLI Commands

**Monitor all configured watch folders:**
```bash
php artisan watch-folders:run
```

**Monitor specific folder:**
```bash
php artisan watch-folders:run --path="/path/to/documents" --user=1
```

**Run with custom interval:**
```bash
php artisan watch-folders:run --interval=60
```

**One-time processing:**
```bash
php artisan watch-folders:run --once
```

### PowerShell Script (Windows)

**Setup system:**
```powershell
.\watch-folders.ps1 -Action setup
```

**Start monitoring with custom interval:**
```powershell
.\watch-folders.ps1 -Action start -Interval 60
```

**Monitor specific folder:**
```powershell
.\watch-folders.ps1 -Action start -WatchPath "C:\MyDocuments" -UserId 1
```

**Check system status:**
```powershell
.\watch-folders.ps1 -Action status
```

**Test configuration:**
```powershell
.\watch-folders.ps1 -Action test
```

**Stop monitoring:**
```powershell
.\watch-folders.ps1 -Action stop
```

### REST API

**Get all watch folders:**
```http
GET /api/watch-folders
Authorization: Bearer {token}
```

**Create new watch folder:**
```http
POST /api/watch-folders
Content-Type: application/json
Authorization: Bearer {token}

{
    "path": "/path/to/watch",
    "user_id": 1,
    "auto_create_transactions": true,
    "use_vision_model": true
}
```

**Test folder path:**
```http
POST /api/watch-folders/test-path
Content-Type: application/json
Authorization: Bearer {token}

{
    "path": "/path/to/test"
}
```

**Trigger manual processing:**
```http
POST /api/watch-folders/{id}/process
Authorization: Bearer {token}
```

**Get processing status:**
```http
GET /api/watch-folders/status
Authorization: Bearer {token}
```

## File Organization

### Default Directory Structure

```
storage/app/watch/
├── incoming/           # Drop files here
├── processed/          # Successfully processed files
├── errors/            # Files that failed processing
└── archive/           # Long-term storage
```

### Supported File Types

| Format | Extension | AI Processing | Notes |
|--------|-----------|---------------|-------|
| Images | .jpg, .jpeg, .png | ✅ Vision Model | Photos, receipts, documents |
| PDFs | .pdf | ✅ Text Extraction | Multi-page documents |
| Spreadsheets | .xlsx, .xls | ✅ Data Analysis | Bank statements, transactions |
| CSV | .csv | ✅ Data Import | Transaction lists |
| Text | .txt | ✅ Text Analysis | Plain text documents |

## Processing Rules

### File Age Requirement

Files must be at least 1 minute old before processing (configurable) to ensure complete uploads.

### Pattern Matching

Configure file patterns to process:
```php
'patterns' => [
    '*.jpg',      // JPEG images
    '*.png',      // PNG images  
    '*.pdf',      // PDF documents
    '*.csv',      // CSV files
    '*bank*.xlsx', // Bank statement spreadsheets
    'receipt_*.jpg' // Receipt photos
]
```

### User Assignment

Documents are processed for specific users:
- Default: User ID 1
- Configurable per watch folder
- Can be set via CLI parameter

## Queue Processing

### Enable Queue Worker

Start a queue worker to process documents in the background:

```bash
# Development
php artisan queue:work

# Production (with supervisor)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Queue Configuration

Configure queue settings in `config/queue.php`:

```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

### Monitor Queue

Check queue status:
```bash
php artisan queue:monitor
```

## Error Handling

### Processing Errors

Files that fail processing are moved to the `errors/` folder with error details:

```
storage/app/watch/errors/
├── failed_receipt_001.jpg
├── failed_receipt_001.jpg.error  # Error details
└── failed_bank_statement.pdf
```

### Error Log

Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

### Retry Failed Jobs

Retry failed queue jobs:
```bash
php artisan queue:retry all
```

## Monitoring and Logs

### Watch Folder Logs

Monitor watch folder activity:
```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep "WatchFolder"

# Check last 100 lines
php artisan tinker
>>> \App\Models\FailedJob::latest()->take(10)->get()
```

### Performance Monitoring

Track processing metrics:
- Processing time per document
- Success/failure rates
- Queue depth and processing speed

### Health Checks

Use the status command to verify system health:
```powershell
.\watch-folders.ps1 -Action status
```

## Production Deployment

### System Requirements

- **PHP 8.1+** with required extensions
- **Laravel Queue Worker** running
- **Ollama Server** with LLAVA model
- **File System Permissions** for watch directories
- **Cron Job** for scheduled processing

### Cron Setup

Add to crontab for continuous monitoring:
```bash
# Every minute - check for new files
* * * * * cd /path/to/firefly && php artisan watch-folders:run --once

# Every 5 minutes - process any pending files
*/5 * * * * cd /path/to/firefly && php artisan queue:work --stop-when-empty
```

### Supervisor Configuration

Use Supervisor to manage queue workers:

```ini
[program:firefly-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/path/to/firefly
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/firefly-queue.log
```

### Security Considerations

- **File Permissions**: Ensure watch directories have appropriate permissions
- **Input Validation**: All files are validated before processing
- **Error Isolation**: Failed files are quarantined in error folders
- **API Authentication**: REST API requires valid bearer tokens
- **Resource Limits**: Configure appropriate timeouts and memory limits

## Troubleshooting

### Common Issues

**No files being processed:**
1. Check file age requirement (default: 1 minute)
2. Verify file extensions match patterns
3. Ensure queue worker is running
4. Check file permissions

**AI processing failures:**
1. Verify Ollama server is running (`http://localhost:11434`)
2. Check LangExtract API availability (`http://localhost:8001`)
3. Ensure LLAVA model is downloaded
4. Review error logs for specific failures

**Queue jobs failing:**
1. Check database connection
2. Verify queue table exists (`jobs`, `failed_jobs`)
3. Review queue worker memory limits
4. Check Laravel log files

### Debug Commands

**Test AI services:**
```bash
# Test Ollama
curl http://localhost:11434/api/tags

# Test LangExtract
curl http://localhost:8001/health
```

**Test file processing:**
```bash
php artisan watch-folders:run --once --path="/test/path"
```

**Check queue status:**
```bash
php artisan queue:monitor
php artisan queue:failed
```

## Integration with Firefly III

### Transaction Creation

The system integrates directly with Firefly III's transaction repositories:
- **TransactionJournalRepositoryInterface** - Create transactions
- **AccountRepositoryInterface** - Manage accounts
- **CategoryRepositoryInterface** - Handle categorization
- **RuleRepositoryInterface** - Apply rules

### Data Mapping

Documents are mapped to Firefly III structures:
```php
$transactionData = [
    'type' => 'withdrawal',
    'description' => $extractedDescription,
    'amount' => $extractedAmount,
    'date' => $extractedDate,
    'source_account_id' => $sourceAccount,
    'destination_account_id' => $destinationAccount,
    'category_id' => $categoryId,
    'tags' => $extractedTags
];
```

### Compatibility

The watch folder system is fully compatible with:
- **Firefly III Core** - Native transaction creation
- **Data Importer** - Complementary processing
- **Existing Rules** - Automatic rule application
- **Categories** - Automatic categorization
- **Budgets** - Budget assignment support

## Advanced Configuration

### Custom Processing Rules

Create custom processing logic:

```php
// In WatchFolderService
protected function customProcessingRule($filePath, $userId)
{
    // Custom logic for specific file types or patterns
    if (Str::contains($filePath, 'bank_statement')) {
        return $this->processBankStatement($filePath, $userId);
    }
    
    if (Str::contains($filePath, 'receipt')) {
        return $this->processReceipt($filePath, $userId);
    }
    
    return $this->processGenericDocument($filePath, $userId);
}
```

### Multiple Watch Folders

Configure multiple watch folders for different document types:

```php
$watchConfigs = [
    [
        'path' => storage_path('app/watch/receipts'),
        'user_id' => 1,
        'patterns' => ['*.jpg', '*.png'],
        'use_vision_model' => true
    ],
    [
        'path' => storage_path('app/watch/statements'),
        'user_id' => 1,
        'patterns' => ['*.pdf', '*.csv'],
        'use_vision_model' => false
    ]
];
```

### Custom File Handlers

Add support for additional file types:

```php
protected function processCustomFormat($filePath, $userId)
{
    // Add custom processing logic
    // Return transaction data array
}
```

## Support and Development

### Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Submit a pull request

### Testing

Run the test suite:
```bash
php artisan test --filter WatchFolder
```

### Documentation

- API documentation available at `/api/documentation`
- Code documentation in source files
- Integration examples in `docs/` directory

## License

This enhanced document processing system is part of the Firefly III project and follows the same licensing terms.

---

**🎯 Need Help?**

- Check the troubleshooting section above
- Review Laravel and Firefly III logs
- Test individual components using the provided commands
- Use the PowerShell script status command for system health checks