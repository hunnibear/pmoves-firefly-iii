# Couples Data Import Integration

## Overview
This integration leverages Firefly III's existing **Data Importer** service instead of building our own CSV import system. The Data Importer is a separate Docker service that handles CSV files, bank connections, and API imports with sophisticated mapping and duplicate detection.

## Services Added

### Firefly III Data Importer
- **Container**: `firefly_iii_importer`
- **Port**: `8081` (Web interface)
- **Image**: `fireflyiii/data-importer:latest`

## Directory Structure
```
├── import-data/           # CSV files for import
│   └── couples-sample-transactions.csv
├── couples-configs/       # JSON configuration files
│   └── couples-basic-config.json
└── docker-compose.local.yml (updated)
```

## Usage

### 1. Start the Enhanced Services
```bash
docker compose -f docker-compose.local.yml up -d
```

### 2. Access the Data Importer
- **Web Interface**: http://localhost:8081
- **API Endpoint**: http://localhost:8081/autoupload

### 3. Manual Import via Web Interface
1. Visit http://localhost:8081
2. Upload `couples-sample-transactions.csv`
3. Select `couples-basic-config.json`
4. Review the mapping and proceed with import
5. Transactions will be imported with proper categorization

### 4. Automated Import via API
```bash
# Upload CSV and config in one request
curl -X POST "http://localhost:8081/autoupload?secret=${AUTO_IMPORT_SECRET}" \
  -H "Authorization: Bearer ${FIREFLY_TOKEN}" \
  -F "importable=@import-data/couples-sample-transactions.csv" \
  -F "json=@couples-configs/couples-basic-config.json"
```

### 5. Post-Import Couples Processing
After import, run couples-specific processing:
```bash
docker exec firefly_iii_core php artisan couples:process-import
```

## Sample Data Explanation

The `couples-sample-transactions.csv` contains:
- **Person 1 Transactions**: Groceries, gas, salary
- **Person 2 Transactions**: Coffee, shopping, freelance income  
- **Shared Transactions**: Rent, utilities, date night

Each transaction has a `Category` field that indicates the person or shared nature:
- `Person 1 Expense/Income`
- `Person 2 Expense/Income`
- `Shared Expense`

## Configuration Details

The `couples-basic-config.json` file:
- Maps CSV columns to Firefly III transaction fields
- Configures category mapping for person identification
- Enables duplicate detection
- Sets up account assignment

## Benefits

✅ **Proven Import Engine**: Uses Firefly III's battle-tested data importer  
✅ **No Code Duplication**: Leverages existing CSV parsing and validation  
✅ **Bank Integration Ready**: Can connect to bank APIs when needed  
✅ **Flexible Mapping**: Handle any CSV format with column mapping  
✅ **API Compatible**: Works with existing automation tools  
✅ **Duplicate Detection**: Built-in hash-based duplicate prevention  

## Next Steps

1. **Test the Integration**: Import the sample CSV to verify functionality
2. **Customize Mappings**: Adjust the JSON config for your specific CSV format
3. **Add Post-Processing**: Implement couples-specific tagging and analysis
4. **Create More Configs**: Build templates for different import scenarios
5. **API Integration**: Connect with external services for automated imports

This approach gives us sophisticated data import capabilities without reinventing any wheels, while perfectly supporting our couples use case!