# Watch Folders for Firefly III

This directory is mounted into the Firefly III container for automatic document processing.

## Directory Structure

- **`incoming/`** - Drop files here for processing
- **`processed/`** - Successfully processed files are moved here automatically
- **`failed/`** - Failed files are moved here with error logs
- **`documents/`** - Sample files for testing (not monitored by default)

## How it Works

1. **Drop Files**: Place bank statements, receipts, or CSV files in the `incoming/` folder
2. **Automatic Processing**: The watch folder service runs every 30 seconds
3. **File Processing**: Files are processed using:
   - AI vision models for receipts and documents
   - CSV parsing for bank statements
   - Transaction Intelligence Agent for categorization
4. **Success**: Successfully processed files are moved to `processed/` folder with timestamp
5. **Failure**: Failed files are moved to `failed/` folder with detailed error logs

## Supported File Types

- **Images**: JPG, JPEG, PNG (receipts, invoices)
- **PDFs**: Bank statements, bills, invoices
- **CSV Files**: Bank exports, transaction lists
- **Excel**: XLSX, XLS (bank statements)
- **Text**: TXT files with transaction data

## File Naming Conventions

The system automatically detects file types based on naming patterns:

- `*statement*` → Bank Statement
- `*receipt*` → Receipt/Invoice
- `*invoice*` → Receipt/Invoice  
- `*bank*` → Bank Statement

## Watch Folder Configuration

Current settings:
- **Auto Create Transactions**: Yes
- **Use Vision Model**: Yes (AI processing for images)
- **Move After Processing**: Yes (to `processed/` folder)
- **Min File Age**: 5 seconds (prevents processing incomplete uploads)

## Testing

1. Copy a file from `documents/` to `incoming/`
2. Watch the logs: `docker logs firefly_iii_core -f`
3. Check the Firefly III interface for new transactions
4. Verify processed file moved to `processed/` folder

## Troubleshooting

- **Files not processing**: Check file permissions and supported formats
- **AI categorization**: Ensure Transaction Intelligence Agent is running
- **Duplicate transactions**: The system includes duplicate detection
- **Large files**: Max file size is 50MB

## Volume Mount

This folder is mounted in docker-compose.local.yml as:
```yaml
- ./watch-folders:/var/www/html/storage/app/watch
```

The container path `/var/www/html/storage/app/watch` maps to this local directory.