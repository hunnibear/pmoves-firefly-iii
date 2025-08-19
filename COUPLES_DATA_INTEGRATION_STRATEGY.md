# Couples Data Integration Strategy
*Leveraging Firefly III's Existing Import Infrastructure*

## Overview
Instead of duplicating Firefly III's robust data import capabilities, we'll integrate our couples features with their existing **Firefly III Data Importer** service.

## Current Firefly III Import Capabilities

### 1. Data Importer Service
- **Separate Docker Service**: `fireflyiii/data-importer:latest`
- **Web Interface**: Import configuration and file upload
- **API Endpoints**: Automated import via REST API
- **CLI Tools**: Command-line batch processing
- **Scheduled Processing**: Cron-based automation

### 2. Supported Import Sources
- **CSV Files**: Any format with configurable mapping
- **Bank APIs**: GoCardless (Nordigen) and Spectre
- **JSON Configuration**: Detailed import rules
- **Local Directory**: Batch processing of multiple files

### 3. Import Features We Can Leverage
- **Column Mapping**: Flexible CSV structure support
- **Account Management**: Auto-creation and mapping
- **Duplicate Detection**: Hash-based prevention
- **Transaction Tagging**: Automatic tag assignment
- **Date Format Handling**: Multiple format support
- **Amount Processing**: Credit/debit, positive/negative

## Integration Strategy for Couples Features

### Phase 1: Enhance Import Configuration
Instead of building new import tools, we'll **extend the existing JSON configuration** to support couples-specific features:

```json
{
  "couples_mode": true,
  "couples_config": {
    "person1_identifier": "P1",
    "person2_identifier": "P2", 
    "shared_identifier": "SHARED",
    "auto_tag_couples": true,
    "default_split_method": "equal",
    "couples_tags": ["couple-p1", "couple-p2", "couple-shared"]
  },
  "mapping": {
    "3": {
      "person_column": true,
      "couples_mapping": {
        "P1": "couple-p1",
        "P2": "couple-p2",
        "SHARED": "couple-shared"
      }
    }
  }
}
```

### Phase 2: Post-Import Processing
Add couples-specific processing **after** Firefly III's standard import:

```bash
# Standard Firefly III import
php artisan importer:import couples-config.json transactions.csv

# Our couples post-processing
php artisan couples:process-import --import-id=12345
```

### Phase 3: API Enhancement
Extend the Data Importer's API endpoints to support couples features:

```http
POST /autoupload?secret=SECRET&couples_mode=true
Content-Type: multipart/form-data

importable=@transactions.csv
json=@couples-config.json
```

## Implementation Plan

### 1. Docker Compose Integration
Update our `docker-compose.local.yml` to include the Data Importer:

```yaml
services:
  firefly_importer:
    image: fireflyiii/data-importer:latest
    container_name: firefly_iii_importer
    restart: unless-stopped
    ports:
      - "8081:8080"
    environment:
      - FIREFLY_III_ACCESS_TOKEN=${FIREFLY_TOKEN}
      - FIREFLY_III_URL=http://app:8080
      - IMPORT_DIR_ALLOWLIST=/import
      - CAN_POST_FILES=true
      - COUPLES_MODE_ENABLED=true  # Our extension
    volumes:
      - ./import-data:/import
      - ./couples-configs:/configurations
    depends_on:
      - app
    networks:
      - firefly_iii
```

### 2. Couples Configuration Templates
Create pre-built JSON configurations for common couples scenarios:

```
couples-configs/
├── joint-account-split.json      # Joint account with person indicators
├── separate-accounts-merge.json  # Separate accounts to joint view
├── expense-splitting.json        # Automatic expense splitting
└── shared-goals-tracking.json    # Shared savings goals
```

### 3. Enhanced Web Interface
Add couples-specific options to the import interface:
- **Couples Mode Toggle**: Enable couples processing
- **Person Assignment**: Automatic P1/P2/Shared tagging
- **Split Rules**: Configure how shared expenses are handled
- **Account Mapping**: Map external accounts to joint/separate accounts

### 4. API Extensions
Extend existing endpoints rather than creating new ones:

```php
// In app/Http/Controllers/Import/ImportController.php
public function store(ImportRequest $request)
{
    $result = parent::store($request);
    
    if ($request->get('couples_mode')) {
        $this->processCouplesImport($result);
    }
    
    return $result;
}
```

## Benefits of This Approach

### 1. **No Feature Duplication**
- Leverage existing CSV parsing
- Use proven import infrastructure
- Maintain compatibility with bank APIs

### 2. **Seamless Integration**
- Works with existing Firefly III workflows
- Compatible with scheduled imports
- Supports all current import sources

### 3. **Minimal Code Changes**
- Extend rather than replace
- Add couples logic as post-processing
- Maintain upgrade compatibility

### 4. **User Experience**
- Familiar import interface
- Enhanced with couples features
- Backward compatible

## Sample Couples CSV Import

### Input CSV
```csv
Date,Description,Amount,Person,Account,Category
2025-08-15,"Grocery Store",-85.50,P1,"Joint Checking","Groceries"
2025-08-15,"Coffee Shop",-12.50,P2,"Joint Checking","Dining"
2025-08-15,"Rent Payment",-1250.00,SHARED,"Joint Checking","Housing"
2025-08-16,"Salary Deposit",3500.00,P1,"Joint Checking","Income"
```

### JSON Configuration
```json
{
  "date": "Y-m-d",
  "default_account": 1,
  "delimiter": "comma", 
  "headers": true,
  "couples_mode": true,
  "couples_config": {
    "person_column": 3,
    "auto_tag": true,
    "split_shared": false
  },
  "roles": [
    "date_transaction",
    "description", 
    "amount",
    "person_indicator",
    "account-name",
    "category-name"
  ]
}
```

### Expected Result
- **Transaction 1**: Tagged with `couple-p1`, assigned to Person 1
- **Transaction 2**: Tagged with `couple-p2`, assigned to Person 2  
- **Transaction 3**: Tagged with `couple-shared`, marked as shared expense
- **Transaction 4**: Tagged with `couple-p1`, income for Person 1

## Next Steps

1. **Research Integration Points**: Examine Data Importer source code
2. **Create Extension Framework**: Build couples processing modules
3. **Test with Sample Data**: Validate the approach with real scenarios
4. **Update Documentation**: Document couples import workflows

This strategy leverages Firefly III's battle-tested import system while adding the couples-specific functionality we need, avoiding duplication and maintaining compatibility.