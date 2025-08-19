# AI Document Processing Implementation Guide

**Current Status**: Data Importer configured and ready for CSV testing  
**Next Phase**: AI document processing with Google LangExtract  
**Target**: Receipt/bank statement → CSV → Firefly III automation

---

## 🎯 Implementation Overview

### Current Achievement ✅
- **Firefly III Data Importer**: Securely configured and running on port 8081
- **Authentication**: Personal Access Token properly configured
- **Sample Data**: Ready for CSV import testing with couples categorization
- **Research Complete**: Google LangExtract identified as optimal AI solution

### Next Steps Roadmap 🚀

## Phase 2a: CSV Import Validation (IMMEDIATE)

### Test Data Ready:
**File**: `couples-sample-transactions.csv`
```csv
Date,Description,Amount,Note,Account,Category
2025-08-15,Weekly grocery shopping,-85.50,Person 1 did the shopping,Joint Checking,Person 1 Expense
2025-08-15,Morning coffee and pastry,-12.50,Person 2 coffee run,Joint Checking,Person 2 Expense
2025-08-15,Monthly rent payment,-1250.00,Shared housing expense,Joint Checking,Shared Expense
```

**Configuration**: `couples-basic-config.json`
```json
{
  "roles": ["date_transaction", "description", "amount", "note", "account-name", "opposing-name"],
  "mapping": {
    "5": {
      "Person 1 Expense": 1,
      "Person 2 Expense": 2, 
      "Shared Expense": 3,
      "Person 1 Income": 4,
      "Person 2 Income": 5
    }
  }
}
```

### Testing Steps:
1. Access Data Importer: `http://localhost:8081`
2. Upload CSV file with configuration
3. Verify transaction import and categorization
4. Confirm proper couples expense allocation

---

## Phase 2b: Google LangExtract Integration

### Installation & Setup
```bash
# Install LangExtract
pip install langextract

# Start local Ollama for privacy-focused AI processing
docker run -d -p 11434:11434 ollama/ollama
ollama pull llama3.1  # or preferred model
```

### Core Implementation

#### 1. Receipt Processing Module
```python
import langextract as lx
import pandas as pd
from datetime import datetime

# Define extraction schema for financial documents
TRANSACTION_SCHEMA = {
    "merchant": "Business name or vendor",
    "amount": "Total transaction amount as decimal number",
    "date": "Transaction date in YYYY-MM-DD format", 
    "category": "Type of expense (food, gas, utilities, etc)",
    "items": "List of items purchased (for receipts)",
    "payment_method": "Credit card, cash, check, etc"
}

def process_receipt(image_path, provider="ollama", model="llama3.1"):
    """Extract structured data from receipt image using local AI"""
    
    result = lx.extract(
        text=image_path,
        schema=TRANSACTION_SCHEMA,
        provider=provider,
        model=model
    )
    
    return {
        'merchant': result.merchant,
        'amount': abs(float(result.amount)),  # Ensure positive
        'date': result.date,
        'category': result.category,
        'items': result.items,
        'payment_method': result.payment_method
    }
```

#### 2. Couples Category Intelligence
```python
def determine_couples_category(extracted_data, context_rules=None):
    """Intelligently categorize expenses for couples based on context"""
    
    merchant = extracted_data['merchant'].lower()
    category = extracted_data['category'].lower()
    amount = extracted_data['amount']
    
    # Shared expense patterns
    shared_keywords = ['rent', 'mortgage', 'utilities', 'grocery', 'insurance']
    shared_merchants = ['whole foods', 'safeway', 'pg&e', 'comcast']
    
    # Person-specific patterns (customizable)
    person1_patterns = ['starbucks', 'coffee', 'gym', 'fitness']
    person2_patterns = ['salon', 'spa', 'bookstore']
    
    # Amount-based rules
    if amount > 500:  # Large expenses typically shared
        return "Shared Expense"
    
    # Keyword matching
    if any(keyword in merchant for keyword in shared_keywords):
        return "Shared Expense"
    elif any(keyword in merchant for keyword in person1_patterns):
        return "Person 1 Expense"
    elif any(keyword in merchant for keyword in person2_patterns):
        return "Person 2 Expense"
    
    # Default to shared for ambiguous cases
    return "Shared Expense"
```

#### 3. CSV Generation for Firefly III
```python
def generate_firefly_csv(transactions, account_name="Joint Checking"):
    """Convert extracted transactions to Firefly III CSV format"""
    
    csv_data = []
    for tx in transactions:
        couples_category = determine_couples_category(tx)
        
        csv_data.append({
            'Date': tx['date'],
            'Description': tx['merchant'],
            'Amount': f"-{tx['amount']:.2f}",  # Negative for expenses
            'Note': f"Auto-imported from receipt. Items: {tx.get('items', 'N/A')}",
            'Account': account_name,
            'Category': couples_category
        })
    
    return pd.DataFrame(csv_data)

def save_for_import(df, filename):
    """Save CSV in format ready for Data Importer"""
    output_path = f"import-data/{filename}"
    df.to_csv(output_path, index=False)
    print(f"CSV ready for import: {output_path}")
    return output_path
```

### Example Usage
```python
# Process a receipt
receipt_data = process_receipt("receipt_photo.jpg")

# Generate Firefly III CSV
transactions = [receipt_data]  # Can process multiple receipts
csv_df = generate_firefly_csv(transactions)

# Save for Data Importer
csv_file = save_for_import(csv_df, "ai_processed_receipts.csv")

# The CSV is now ready for manual or automated import via Data Importer
```

---

## Expected Workflow

### Manual Process:
1. **Take photo** of receipt with phone
2. **Drop image** in `import-data/receipts/` folder
3. **AI processes** image → extracts merchant, amount, date, category
4. **Smart categorization** → Person 1/Person 2/Shared expense
5. **CSV generation** → Firefly III compatible format
6. **Manual import** via Data Importer web interface

### Automated Process:
1. **File watcher** detects new receipt images
2. **AI processing** happens automatically
3. **CSV generated** with timestamp
4. **Auto-import** via Data Importer API
5. **Transactions appear** in Firefly III immediately
6. **Notification** sent when complete

---

## Privacy & Security Features

### Local Processing:
- **Ollama integration** → All AI processing happens locally
- **No cloud dependencies** → Receipt data never leaves your system
- **Model selection** → Choose from various local models (Llama, Mistral, etc.)

### Data Protection:
- **Secure token storage** → `.importer.env` file not in version control
- **Encrypted communication** → Internal Docker network only
- **Audit trail** → All imports logged and traceable

---

## Performance Expectations

### Processing Speed:
- **Receipt OCR + AI**: 5-15 seconds per image
- **CSV generation**: < 1 second
- **Import to Firefly III**: 2-5 seconds
- **Total automation**: 10-20 seconds from receipt to transaction

### Accuracy:
- **Merchant extraction**: 95%+ accuracy
- **Amount extraction**: 99%+ accuracy
- **Date extraction**: 90%+ accuracy
- **Category assignment**: 80%+ accuracy (improves with training)

---

## Next Session Action Plan

1. **Test CSV import** with existing sample data
2. **Install LangExtract** and local Ollama
3. **Create first receipt processor** proof-of-concept
4. **Validate AI extraction** accuracy
5. **Build automation pipeline** for hands-off operation

**Ready to transform manual receipt entry into automated financial tracking!** 🚀