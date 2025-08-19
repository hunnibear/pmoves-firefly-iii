# 🎯 IMMEDIATE ACTION PLAN: Data Importer & AI Integration

## 🚨 Priority 1: Get Data Importer Working (Today)

### Step 1: Generate Personal Access Token

**Action Required**: 
1. Open Firefly III at http://localhost:8080
2. If not registered: Create account (use cataclysmstudios@gmail.com)
3. Navigate to: **Profile & Preferences** → **OAuth** → **Personal Access Tokens**
4. Create new token with name: `Data Importer Integration`
5. Select scopes: `transactions`, `accounts`, `budgets`, `categories`
6. **Copy the generated token immediately**

### Step 2: Update Environment Configuration

```bash
# Replace the placeholder in .env.local
FIREFLY_TOKEN=YOUR_COPIED_TOKEN_HERE
```

**Command to update**:
```powershell
# Edit .env.local and replace the FIREFLY_TOKEN value
(Get-Content .env.local) -replace 'FIREFLY_TOKEN=changeme_generate_in_firefly_ui', 'FIREFLY_TOKEN=YOUR_ACTUAL_TOKEN' | Set-Content .env.local
```

### Step 3: Restart Data Importer

```powershell
# Restart to pick up new token
docker-compose -f docker-compose.local.yml restart importer

# Verify it's running
docker-compose -f docker-compose.local.yml ps importer
```

### Step 4: Create Required Accounts

**In Firefly III**:
1. Go to **Accounts** → **Asset accounts**
2. Click **Create new asset account**
3. Fill in:
   - **Name**: `Joint Checking`
   - **Account type**: `Default account`
   - **Currency**: `USD`
   - **Current balance**: `5000.00`
   - **Account role**: `Default account`

### Step 5: Test First Import

**Data Importer Interface**: http://localhost:8081

1. **Select**: `Import from file`
2. **Upload Configuration**: Browse → `couples-configs/couples-basic-config.json`
3. **Upload CSV**: Browse → `import-data/couples-sample-transactions.csv`
4. **Review mapping** and click **Start import**
5. **Verify** transactions appear in Firefly III

---

## 🤖 Priority 2: AI Enhancement Research (Next Week)

### OCR Capabilities Assessment

**Available Options Identified**:

1. **Microsoft TrOCR** (Best for receipts)
   - Model: `microsoft/trocr-large-printed`
   - Downloads: 147.7K
   - Good for printed receipts

2. **MCP ImageSorcery OCR** (Local processing)
   - Uses EasyOCR
   - Already available in your setup
   - Privacy-focused (no cloud API calls)

3. **OpenAI Vision API** (Most accurate)
   - GPT-4 Vision for complex document analysis
   - You already have API key configured
   - Best for structured data extraction

### Proof of Concept Plan

**Week 1**: Test basic OCR extraction
```python
# Test script to evaluate OCR options
import requests
from PIL import Image

def test_ocr_capabilities():
    # Test 1: MCP ImageSorcery (local)
    result1 = test_mcp_ocr()
    
    # Test 2: OpenAI Vision API
    result2 = test_openai_vision()
    
    # Test 3: Microsoft TrOCR
    result3 = test_microsoft_trocr()
    
    return compare_results(result1, result2, result3)
```

**Week 2**: Document classification and routing
**Week 3**: Integration with Data Importer
**Week 4**: End-to-end testing with real receipts

---

## 🎯 Success Criteria

### Phase 1 (This Week) ✅
- [ ] Data Importer connects to Firefly III
- [ ] Couples CSV imports successfully 
- [ ] Transactions appear with correct categorization
- [ ] Account mapping works properly

### Phase 2 (Next Week) 🤖
- [ ] OCR extracts text from receipt images
- [ ] Document type classification works
- [ ] AI categorization integrates with existing system
- [ ] Processing pipeline handles errors gracefully

### Phase 3 (Future) 🚀
- [ ] Multi-format import (PDF, images, CSV)
- [ ] Automated bank statement processing
- [ ] Receipt upload via web interface
- [ ] Couples-specific expense assignment

---

## 🛠️ Technical Architecture

### Current Working Components
```
✅ Firefly III Core (localhost:8080)
✅ Data Importer Service (localhost:8081)  
✅ AI Services (AIService.php with multi-provider support)
✅ Couples Controller (working with transactions)
✅ Existing configurations and sample data
```

### Planned AI Enhancements
```
🔄 OCR Processing Layer
   ├── Receipt image upload
   ├── Text extraction (TrOCR/EasyOCR/OpenAI)
   ├── Structured data parsing
   └── CSV generation for import

🔄 Document Classification
   ├── Receipt vs Statement vs Invoice
   ├── Routing to appropriate processor
   └── Confidence scoring

🔄 Enhanced Import Pipeline
   ├── Multi-format input handling
   ├── AI-powered categorization
   ├── Couples-specific assignment
   └── Error handling and validation
```

### Integration Points

1. **Data Importer ↔ AI Services**
   - Pre-process uploads with OCR
   - AI categorization before import
   - Post-process with couples logic

2. **Couples Controller ↔ AI Services**
   - Enhanced transaction categorization
   - Partner-specific spending analysis
   - Goal tracking with AI insights

3. **Web Interface ↔ Processing Pipeline**
   - Drag-and-drop upload
   - Real-time processing status
   - Error feedback and corrections

---

## 📋 Action Items for Today

### Immediate (Next 30 minutes)
1. **Generate Firefly III token**
2. **Update .env.local file**
3. **Restart Data Importer service**
4. **Create Joint Checking account**

### Testing (Next hour)
1. **Test basic CSV import**
2. **Verify transactions in Firefly III**
3. **Check couples categorization**
4. **Document any issues**

### Planning (This afternoon)
1. **Research OCR model performance**
2. **Design document processing workflow**
3. **Plan AI integration approach**
4. **Create testing strategy**

---

## 🔧 Troubleshooting Quick Reference

### Data Importer Issues
```bash
# Check service status
docker-compose -f docker-compose.local.yml logs importer

# Common fixes
docker-compose -f docker-compose.local.yml restart importer
docker-compose -f docker-compose.local.yml pull importer
```

### Token Issues
- Ensure token has all required scopes
- Check token isn't expired
- Verify no extra spaces in .env.local

### Account Mapping Issues
- Account names must match exactly
- Check currency settings
- Verify account exists before import

---

**🎯 Goal: Working Data Importer by end of day, AI enhancement planning complete by end of week.**

Ready to proceed? Start with generating the Firefly III access token!