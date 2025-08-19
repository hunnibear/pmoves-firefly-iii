# Firefly III Data Importer Setup & Testing Guide
*Complete guide for robust data import with AI-enhanced capabilities*

## 🎯 Current Status

### ✅ What's Working
- **Data Importer Service**: Running on http://localhost:8081
- **Firefly III Core**: Running on http://localhost:8080  
- **Couples Configuration**: JSON template ready
- **Sample Data**: CSV file with couples transactions ready
- **Docker Integration**: All services properly networked

### ⚠️ What Needs Configuration
- **Personal Access Token**: Must be generated in Firefly III UI
- **Environment Variables**: FIREFLY_TOKEN and AUTO_IMPORT_SECRET
- **Account Mapping**: Create accounts that match the CSV data

## 🚀 Step-by-Step Setup

### Step 1: Generate Firefly III Personal Access Token

1. **Open Firefly III**: http://localhost:8080
2. **Login/Register**: Create account if first time
3. **Go to Profile**: Click your profile in top-right
4. **Select "Profile & Preferences"**
5. **Navigate to OAuth tab**
6. **Click "Personal Access Tokens"**
7. **Create New Token**:
   - Name: `Data Importer Integration`
   - Select scopes: `transactions`, `accounts`, `budgets`, `categories`
8. **Copy the generated token**

### Step 2: Update Environment Configuration

```bash
# Update the .env.local file with the generated token
FIREFLY_TOKEN=YOUR_GENERATED_TOKEN_HERE
AUTO_IMPORT_SECRET=couples_import_secret_2025_secure_key
```

### Step 3: Restart Data Importer Service

```powershell
# Restart to pick up new environment variables
docker-compose -f docker-compose.local.yml restart importer
```

### Step 4: Create Required Accounts in Firefly III

Before importing, create accounts that match your CSV data:

1. **Go to Accounts** in Firefly III
2. **Create Asset Account**:
   - Name: `Joint Checking`
   - Type: `Asset account`
   - Currency: `USD`
   - Initial balance: `5000.00`

### Step 5: Test Data Import

1. **Open Data Importer**: http://localhost:8081
2. **Select Import Type**: `CSV file`
3. **Upload Configuration**: 
   - Upload `couples-configs/couples-basic-config.json`
4. **Upload Data File**:
   - Upload `import-data/couples-sample-transactions.csv`
5. **Review Mapping**:
   - Verify account mapping
   - Check category assignments
   - Confirm couples-specific roles
6. **Run Import**
7. **Verify in Firefly III**: Check transactions appear correctly

## 🤖 AI-Enhanced Import Capabilities

### Current AI Integration Opportunities

#### 1. **OCR Receipt Processing**
**Goal**: Upload receipt images and automatically extract transaction data

**Implementation Options**:

##### Option A: Integrate with Existing AI Services
```python
# Using OpenAI Vision API for receipt OCR
import openai
import json

def extract_receipt_data(image_path):
    with open(image_path, "rb") as image_file:
        response = openai.chat.completions.create(
            model="gpt-4-vision-preview",
            messages=[
                {
                    "role": "user",
                    "content": [
                        {
                            "type": "text",
                            "text": "Extract transaction data from this receipt in JSON format: {date, description, amount, merchant, category}"
                        },
                        {
                            "type": "image_url",
                            "image_url": {"url": f"data:image/jpeg;base64,{image_file}"}
                        }
                    ]
                }
            ]
        )
    return json.loads(response.choices[0].message.content)
```

##### Option B: Use Microsoft TrOCR (Transformer-based OCR)
Microsoft's TrOCR is the state-of-the-art OCR model available on Hugging Face:

```python
# Using Microsoft TrOCR for receipt processing
from transformers import TrOCRProcessor, VisionEncoderDecoderModel
from PIL import Image
import json

class ReceiptOCRProcessor:
    def __init__(self):
        self.processor = TrOCRProcessor.from_pretrained('microsoft/trocr-large-printed')
        self.model = VisionEncoderDecoderModel.from_pretrained('microsoft/trocr-large-printed')
    
    def extract_text_from_receipt(self, image_path):
        # Load and process image
        image = Image.open(image_path).convert('RGB')
        pixel_values = self.processor(image, return_tensors="pt").pixel_values
        
        # Generate text
        generated_ids = self.model.generate(pixel_values)
        generated_text = self.processor.batch_decode(generated_ids, skip_special_tokens=True)[0]
        
        return generated_text
    
    def parse_receipt_to_transaction(self, extracted_text):
        # Use AI to structure the extracted text
        # This could be enhanced with additional NLP models
        # or integration with your existing AI services
        pass
```

##### Option C: Use Local MCP ImageSorcery OCR

The MCP ImageSorcery server provides OCR capabilities:

```python
# Using local OCR through MCP
def process_receipt_with_mcp(image_path):
    # Use mcp_imagesorcery_ocr tool
    result = mcp_imagesorcery_ocr(input_path=image_path, language='en')
    
    # Parse OCR results into transaction format
    return parse_ocr_results(result)
```

#### 2. **Intelligent Document Classification**

**Goal**: Automatically detect document types (receipts, bank statements, invoices) and route to appropriate processing

```python
# Document classification using existing AI services
class DocumentClassifier:
    def __init__(self, ai_service):
        self.ai_service = ai_service  # Your existing AIService
    
    def classify_document(self, image_path):
        prompt = """
        Analyze this document image and classify it as one of:
        - receipt (store/restaurant receipt)
        - bank_statement (bank transaction statement) 
        - invoice (business invoice)
        - other
        
        Return only the classification type.
        """
        return self.ai_service.analyze_image(image_path, prompt)
```

#### 3. **Bank Statement Processing**

**Goal**: Process PDF bank statements and extract transaction data

**Implementation Strategy**:

```python
# PDF processing with AI enhancement
import PyPDF2
import pandas as pd

class BankStatementProcessor:
    def __init__(self, ai_service):
        self.ai_service = ai_service
    
    def process_pdf_statement(self, pdf_path):
        # Extract text from PDF
        text = self.extract_pdf_text(pdf_path)
        
        # Use AI to structure transaction data
        structured_data = self.ai_service.structure_bank_data(text)
        
        # Convert to CSV format for import
        return self.convert_to_csv(structured_data)
    
    def extract_pdf_text(self, pdf_path):
        with open(pdf_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            text = ""
            for page in reader.pages:
                text += page.extract_text()
        return text
```

#### 4. **Multi-Format Import Pipeline**

**Goal**: Single interface to handle any financial document type

```python
class UniversalImportProcessor:
    def __init__(self):
        self.ocr_processor = ReceiptOCRProcessor()
        self.doc_classifier = DocumentClassifier()
        self.pdf_processor = BankStatementProcessor()
    
    def process_any_document(self, file_path):
        file_ext = os.path.splitext(file_path)[1].lower()
        
        if file_ext in ['.jpg', '.jpeg', '.png']:
            # Classify image type
            doc_type = self.doc_classifier.classify_document(file_path)
            
            if doc_type == 'receipt':
                return self.process_receipt(file_path)
            elif doc_type == 'bank_statement':
                return self.process_statement_image(file_path)
                
        elif file_ext == '.pdf':
            return self.pdf_processor.process_pdf_statement(file_path)
            
        elif file_ext == '.csv':
            return self.process_csv_with_ai_enhancement(file_path)
        
        else:
            raise ValueError(f"Unsupported file type: {file_ext}")
```

## 🔧 Enhanced Data Importer Integration

### Phase 1: Robust CSV Import (Current)

**Test the existing setup**:

1. **Generate Personal Access Token** in Firefly III
2. **Update environment variables**
3. **Test couples CSV import**
4. **Verify data integrity**

### Phase 2: AI-Enhanced CSV Processing

**Add intelligent preprocessing**:

```php
// Add to Data Importer preprocessing
class CouplesAIPreprocessor 
{
    public function enhanceTransactionData($csvData) 
    {
        foreach ($csvData as &$row) {
            // Use existing AI service for categorization
            $aiCategory = app(AIService::class)->categorizeTransaction([
                'description' => $row['Description'],
                'amount' => $row['Amount']
            ]);
            
            // Apply couples-specific logic
            $row['ai_suggested_category'] = $aiCategory;
            $row['couples_assignment'] = $this->determineCouplesAssignment($row);
        }
        
        return $csvData;
    }
}
```

### Phase 3: Multi-Format Processing

**Extend Data Importer for multiple file types**:

1. **Add OCR endpoint** to Data Importer service
2. **Create document upload interface**
3. **Integrate with existing couples processing**
4. **Add AI categorization pipeline**

## 🎯 Implementation Roadmap

### Week 1: Foundation (Current Priority)
- [ ] Generate Firefly III access token
- [ ] Test basic CSV import with couples data
- [ ] Verify Data Importer ↔ Firefly III communication
- [ ] Document working configuration

### Week 2: AI Enhancement Planning
- [ ] Research best OCR models for receipts
- [ ] Test MCP ImageSorcery OCR capabilities
- [ ] Design document processing pipeline
- [ ] Create proof-of-concept receipt processor

### Week 3: OCR Integration
- [ ] Implement receipt OCR processing
- [ ] Add document classification
- [ ] Create upload interface for images
- [ ] Test end-to-end receipt → transaction flow

### Week 4: Advanced Features
- [ ] Add PDF bank statement processing
- [ ] Implement intelligent categorization
- [ ] Create unified import interface
- [ ] Performance testing and optimization

## 🧪 Testing Strategy

### Current Test Plan

1. **Basic Import Test**:
   ```bash
   # Test with existing couples sample data
   curl -X POST http://localhost:8081/api/import \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -F "config=@couples-configs/couples-basic-config.json" \
     -F "data=@import-data/couples-sample-transactions.csv"
   ```

2. **Verification in Firefly III**:
   - Check transactions appear with correct categories
   - Verify couples-specific tags are applied
   - Confirm account assignments

### Future AI Testing

1. **OCR Accuracy Testing**:
   - Test with various receipt formats
   - Measure extraction accuracy
   - Compare different OCR models

2. **Classification Testing**:
   - Test document type detection
   - Verify routing to correct processors
   - Measure classification accuracy

3. **End-to-End Testing**:
   - Upload receipt image
   - Verify extraction and processing
   - Check final transaction creation

## 📊 Success Metrics

### Phase 1 (Foundation) - Target: 95% Success Rate
- [ ] CSV imports complete without errors
- [ ] Transactions appear in Firefly III correctly
- [ ] Couples-specific categorization works
- [ ] Account mapping functions properly

### Phase 2 (AI Enhancement) - Target: 85% Accuracy
- [ ] OCR extracts receipt data accurately
- [ ] Document classification works reliably  
- [ ] AI categorization improves over time
- [ ] Processing time under 30 seconds per document

### Phase 3 (Multi-Format) - Target: 80% Automation
- [ ] Support 5+ document formats
- [ ] Minimal manual intervention required
- [ ] Accurate couples expense assignment
- [ ] Integration with existing AI systems

## 🚀 Next Steps

**Immediate Action Required**:

1. **Open Firefly III**: http://localhost:8080
2. **Generate Personal Access Token**
3. **Update .env.local with token**
4. **Test first CSV import**

Once basic import is working, we can proceed with AI-enhanced capabilities for OCR and automated document processing.

---

*This guide provides a complete roadmap from basic CSV import to advanced AI-powered document processing, building on your existing couples and AI infrastructure.*
