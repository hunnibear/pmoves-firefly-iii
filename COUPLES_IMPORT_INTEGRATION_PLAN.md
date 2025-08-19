# Couples Import Integration Plan

## Summary
Instead of building our own CSV import system, we'll **extend Firefly III's existing Data Importer** to support couples-specific features. This approach avoids duplication and leverages their battle-tested import infrastructure.

## Quick Implementation Steps

### 1. Add Data Importer to Docker Compose

Update `docker-compose.local.yml`:

```yaml
services:
  firefly_importer:
    image: fireflyiii/data-importer:latest
    container_name: firefly_iii_importer
    restart: unless-stopped
    ports:
      - "8081:8080"  # Import interface on port 8081
    environment:
      - FIREFLY_III_ACCESS_TOKEN=${FIREFLY_TOKEN}
      - FIREFLY_III_URL=http://app:8080
      - IMPORT_DIR_ALLOWLIST=/import
      - CAN_POST_FILES=true
    volumes:
      - ./import-data:/import
    depends_on:
      - app
    networks:
      - firefly_iii
```

### 2. Create Couples-Specific Import Configurations

Create `import-data/couples-config.json`:

```json
{
  "date": "Y-m-d",
  "default_account": 1,
  "delimiter": "comma",
  "headers": true,
  "rules": true,
  "add_import_tag": true,
  "specifics": ["AppendHash"],
  "roles": [
    "date_transaction",
    "description", 
    "amount",
    "note",
    "account-name",
    "opposing-name"
  ],
  "do_mapping": {
    "5": true  # Enable mapping for opposing account names
  },
  "mapping": {
    "5": {
      "Person 1 Expense": 1,
      "Person 2 Expense": 2, 
      "Shared Expense": 3
    }
  }
}
```

### 3. Sample Couples CSV Format

Create `import-data/couples-sample.csv`:

```csv
Date,Description,Amount,Note,Account,Category
2025-08-15,Grocery Store,-85.50,Person 1 purchase,Joint Checking,Person 1 Expense
2025-08-15,Coffee Shop,-12.50,Person 2 purchase,Joint Checking,Person 2 Expense  
2025-08-15,Rent Payment,-1250.00,Monthly rent,Joint Checking,Shared Expense
2025-08-16,Salary Deposit,3500.00,Person 1 salary,Joint Checking,Person 1 Income
```

### 4. Post-Import Couples Processing

Add to `app/Console/Commands/CouplesProcessImport.php`:

```php
<?php

namespace FireflyIII\Console\Commands;

use Illuminate\Console\Command;

class CouplesProcessImport extends Command
{
    protected $signature = 'couples:process-import {--tag=import}';
    protected $description = 'Process imported transactions for couples features';

    public function handle()
    {
        $tag = $this->option('tag');
        
        // Find recently imported transactions with the tag
        $transactions = $this->getImportedTransactions($tag);
        
        foreach ($transactions as $transaction) {
            $this->processCouplesTransaction($transaction);
        }
        
        $this->info("Processed {$transactions->count()} transactions for couples features");
    }
    
    private function processCouplesTransaction($transaction)
    {
        // Extract person indicator from description or category
        $category = $transaction->category?->name;
        
        switch ($category) {
            case 'Person 1 Expense':
            case 'Person 1 Income':
                $transaction->tag('couple-p1');
                break;
            case 'Person 2 Expense': 
            case 'Person 2 Income':
                $transaction->tag('couple-p2');
                break;
            case 'Shared Expense':
                $transaction->tag('couple-shared');
                break;
        }
    }
}
```

### 5. API Integration for Automated Imports

Create couples-aware import endpoint in `routes/api.php`:

```php
Route::post('/couples/import', function (Request $request) {
    // Use existing Firefly III import functionality
    $importResult = app(\FireflyIII\Services\ImportService::class)
        ->import($request->file('csv'), $request->get('config'));
    
    // Add couples post-processing
    Artisan::call('couples:process-import', [
        '--tag' => $importResult->import_tag
    ]);
    
    return response()->json([
        'success' => true,
        'import_id' => $importResult->id,
        'couples_processed' => true
    ]);
});
```

## Usage Workflow

### Manual Import via Web Interface

1. **Access Data Importer**: Visit `http://localhost:8081`
2. **Upload CSV**: Use our couples-sample.csv
3. **Apply Configuration**: Select couples-config.json
4. **Review Mapping**: Verify person/category assignments
5. **Execute Import**: Let Firefly III process the data
6. **Post-Process**: Run `php artisan couples:process-import`

### Automated Import via API

```bash
# Upload and import in one step
curl -X POST http://localhost:8081/autoupload?secret=SECRET \
  -F "importable=@couples-sample.csv" \
  -F "json=@couples-config.json"

# Process couples features
docker exec firefly_app php artisan couples:process-import
```

### Scheduled Import Processing

Add to crontab for regular processing:

```cron
# Process couples imports daily at 2 AM
0 2 * * * docker exec firefly_app php artisan couples:process-import
```

## Benefits of This Approach

✅ **No Code Duplication**: Use Firefly III's proven import engine  
✅ **Full CSV Support**: Handle any CSV format with flexible mapping  
✅ **Bank Integration**: Leverage existing GoCardless/Spectre connections  
✅ **API Compatibility**: Work with existing import automation  
✅ **Upgrade Safe**: Minimal changes to core Firefly III code  
✅ **User Familiar**: Same import interface users already know  

## Testing the Integration

1. **Start Services**: `docker compose -f docker-compose.local.yml up -d`
2. **Access Importer**: Open `http://localhost:8081`
3. **Test Sample Data**: Import the couples-sample.csv
4. **Verify Results**: Check transactions have couples tags
5. **Test API**: Use curl commands for automated import

This approach gives us powerful data import capabilities without reinventing the wheel, while adding the couples-specific features we need!