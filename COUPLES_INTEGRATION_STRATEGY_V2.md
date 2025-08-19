# Couples Integration Strategy V2 - Enterprise Architecture

## Strategic Overview

**Current Status**: Transitioning from basic HTML implementation to enterprise-grade Firefly III + Supabase + LangExtract architecture.

**Key Decision**: Leveraging existing robust infrastructure rather than building standalone solutions.

## Architecture Foundation

### Core Infrastructure Stack

#### 1. **Firefly III Core (Proven Foundation)**
- AdminLTE framework for enterprise UI
- Complete transaction, account, and budget management
- User authentication and authorization
- Laravel backend with proven scalability

#### 2. **Supabase Real-time Layer (Live Collaboration)**
- PostgreSQL database with real-time subscriptions
- Edge functions for server-side processing
- Vector embeddings for intelligent matching
- Row-level security for multi-user support

#### 3. **AI Services Ecosystem (Smart Processing)**
- **Ollama (Local)**: Privacy-focused Llama 3.2 for sensitive financial data
- **OpenAI/Groq (Cloud)**: Advanced reasoning and insights
- **LangExtract (Document Processing)**: OCR + AI for receipt and statement processing

#### 4. **Enhanced Couples Dashboard (Current Implementation)**
- Location: `resources/views/couples/dashboard.twig`
- Real-time partner collaboration features
- AI-powered transaction insights
- Mobile-first responsive design

## LangExtract Integration Strategy

### Document Processing Pipeline

```
Document Upload → LangExtract Processing → Structured Data → AI Categorization → Couples Assignment → Firefly III Transaction
```

### Implementation Phases

#### Phase 1: Receipt Processing (Weeks 1-2)

**Setup LangExtract Service**
```bash
# Install LangExtract with Ollama integration
pip install langextract[all]

# Configure for local privacy-focused processing
export LANGEXTRACT_MODEL_PROVIDER=ollama
export LANGEXTRACT_MODEL_NAME=llama3.2
export LANGEXTRACT_ENDPOINT=http://localhost:11434
```

**Receipt Processing Schema**
```python
RECEIPT_SCHEMA = {
    "merchant": {
        "type": "string",
        "description": "Name of the merchant or store"
    },
    "amount": {
        "type": "number", 
        "description": "Total transaction amount"
    },
    "date": {
        "type": "date",
        "description": "Transaction date in YYYY-MM-DD format"
    },
    "category": {
        "type": "string",
        "description": "Expense category (groceries, dining, gas, etc.)"
    },
    "items": {
        "type": "array",
        "description": "Individual line items purchased"
    },
    "tax_amount": {
        "type": "number",
        "description": "Tax amount if separately listed"
    },
    "payment_method": {
        "type": "string", 
        "description": "Credit card, cash, debit, etc."
    }
}
```

#### Phase 2: Bank Statement Processing (Weeks 3-4)

**Bank Statement Schema**
```python
BANK_STATEMENT_SCHEMA = {
    "account_info": {
        "account_number": "string",
        "account_type": "string",
        "bank_name": "string"
    },
    "statement_period": {
        "start_date": "date",
        "end_date": "date"
    },
    "transactions": {
        "type": "array",
        "items": {
            "date": "date",
            "description": "string", 
            "amount": "number",
            "balance": "number",
            "transaction_type": "string"
        }
    },
    "summary": {
        "starting_balance": "number",
        "ending_balance": "number",
        "total_deposits": "number",
        "total_withdrawals": "number"
    }
}
```

### Backend Integration

#### Enhanced CouplesController

```php
<?php

namespace FireflyIII\Http\Controllers;

use FireflyIII\Services\LangExtractService;
use FireflyIII\Services\AIService;
use Illuminate\Http\Request;

class CouplesController extends Controller
{
    protected $langExtractService;
    protected $aiService;

    public function __construct(LangExtractService $langExtract, AIService $ai)
    {
        $this->langExtractService = $langExtract;
        $this->aiService = $ai;
    }

    /**
     * Process uploaded receipt with LangExtract
     */
    public function uploadReceipt(Request $request)
    {
        $receiptFile = $request->file('receipt');
        
        // Process with LangExtract
        $extractedData = $this->langExtractService->processReceipt($receiptFile);
        
        // AI-enhance categorization for couples context
        $aiCategory = $this->aiService->categorizeForCouples(
            $extractedData['description'] ?? $extractedData['merchant'],
            $extractedData['amount'],
            auth()->user()->couples_profile
        );
        
        // Suggest partner assignment
        $partnerAssignment = $this->suggestPartnerAssignment($extractedData, $aiCategory);
        
        return response()->json([
            'extracted_data' => $extractedData,
            'ai_suggestions' => [
                'category' => $aiCategory,
                'partner_assignment' => $partnerAssignment,
                'confidence' => $this->calculateConfidence($extractedData)
            ],
            'preview_transaction' => $this->buildTransactionPreview($extractedData, $aiCategory)
        ]);
    }

    /**
     * Process bank statement upload
     */
    public function processBankStatement(Request $request)
    {
        $statementFile = $request->file('bank_statement');
        
        // Extract transactions with LangExtract
        $extractedData = $this->langExtractService->processBankStatement($statementFile);
        
        $processedTransactions = [];
        
        foreach ($extractedData['transactions'] as $transaction) {
            // AI categorization for each transaction
            $aiCategory = $this->aiService->categorizeForCouples(
                $transaction['description'],
                $transaction['amount'],
                auth()->user()->couples_profile
            );
            
            // Check for duplicates in Firefly III
            $isDuplicate = $this->checkForDuplicate($transaction);
            
            $processedTransactions[] = [
                'raw_data' => $transaction,
                'ai_category' => $aiCategory,
                'partner_assignment' => $this->suggestPartnerAssignment($transaction, $aiCategory),
                'is_duplicate' => $isDuplicate,
                'confidence' => $this->calculateConfidence($transaction)
            ];
        }
        
        return response()->json([
            'statement_info' => $extractedData['account_info'],
            'period' => $extractedData['statement_period'],
            'processed_transactions' => $processedTransactions,
            'summary' => [
                'total_transactions' => count($processedTransactions),
                'new_transactions' => count(array_filter($processedTransactions, fn($t) => !$t['is_duplicate'])),
                'duplicates_found' => count(array_filter($processedTransactions, fn($t) => $t['is_duplicate']))
            ]
        ]);
    }

    /**
     * Approve and import processed transactions
     */
    public function approveAndImport(Request $request)
    {
        $approvedTransactions = $request->input('transactions');
        $importResults = [];
        
        foreach ($approvedTransactions as $transactionData) {
            try {
                // Create Firefly III transaction
                $transaction = $this->createFireflyTransaction($transactionData);
                
                // Add couples-specific tags and metadata
                $this->applyCouplesMetadata($transaction, $transactionData);
                
                // Trigger real-time update via Supabase
                $this->broadcastCouplesUpdate('transaction_added', $transaction);
                
                $importResults[] = [
                    'status' => 'success',
                    'transaction_id' => $transaction->id,
                    'original_data' => $transactionData
                ];
                
            } catch (\Exception $e) {
                $importResults[] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'original_data' => $transactionData
                ];
            }
        }
        
        return response()->json([
            'import_summary' => [
                'total_processed' => count($importResults),
                'successful' => count(array_filter($importResults, fn($r) => $r['status'] === 'success')),
                'failed' => count(array_filter($importResults, fn($r) => $r['status'] === 'error'))
            ],
            'results' => $importResults
        ]);
    }

    /**
     * Suggest partner assignment based on AI analysis
     */
    private function suggestPartnerAssignment($transactionData, $aiCategory)
    {
        $couplesProfile = auth()->user()->couples_profile;
        
        // AI prompt for partner assignment
        $prompt = "Based on this transaction, suggest which partner should be assigned:
        Transaction: {$transactionData['description']} - \${$transactionData['amount']}
        Category: {$aiCategory}
        Partner 1: {$couplesProfile->partner1_name} (role: {$couplesProfile->partner1_role})
        Partner 2: {$couplesProfile->partner2_name} (role: {$couplesProfile->partner2_role})
        Shared expense categories: {$couplesProfile->shared_categories}
        
        Respond with: partner1, partner2, or shared";
        
        return $this->aiService->query($prompt);
    }

    /**
     * Broadcast real-time update to partners
     */
    private function broadcastCouplesUpdate($eventType, $data)
    {
        // Integration with Supabase real-time
        event(new CouplesRealtimeEvent(auth()->user()->couples_profile, $eventType, $data));
    }
}
```

## Supabase Real-time Integration

### Database Schema Extensions

```sql
-- Real-time couples collaboration
CREATE TABLE couples_realtime_events (
    id SERIAL PRIMARY KEY,
    couples_profile_id INTEGER REFERENCES couples_profiles(id),
    event_type VARCHAR(50) NOT NULL,
    event_data JSONB NOT NULL,
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    acknowledged_by JSONB DEFAULT '[]'::jsonb
);

-- Document processing jobs
CREATE TABLE document_processing_jobs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    couples_profile_id INTEGER REFERENCES couples_profiles(id),
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL, -- 'receipt', 'bank_statement', 'invoice'
    processing_status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'processing', 'completed', 'failed'
    extracted_data JSONB,
    ai_enhancements JSONB,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    completed_at TIMESTAMP
);

-- Enable real-time subscriptions
ALTER PUBLICATION supabase_realtime ADD TABLE couples_realtime_events;
ALTER PUBLICATION supabase_realtime ADD TABLE document_processing_jobs;
```

### Frontend Real-time Updates

```javascript
// Enhanced dashboard with Supabase real-time
class CouplesEnhancedDashboard {
    constructor() {
        this.user = {{ auth_user() | json_encode | raw }};
        this.supabaseClient = this.initializeSupabase();
        this.setupRealtimeSubscriptions();
        this.init();
    }

    initializeSupabase() {
        return supabase.createClient(
            '{{ config('supabase.url') }}',
            '{{ config('supabase.anon_key') }}'
        );
    }

    setupRealtimeSubscriptions() {
        // Subscribe to couples events
        this.supabaseClient
            .channel('couples-events')
            .on('postgres_changes', {
                event: '*',
                schema: 'public',
                table: 'couples_realtime_events',
                filter: `couples_profile_id=eq.${this.user.couples_profile_id}`
            }, (payload) => {
                this.handleRealtimeEvent(payload);
            })
            .subscribe();

        // Subscribe to document processing updates
        this.supabaseClient
            .channel('document-processing')
            .on('postgres_changes', {
                event: '*',
                schema: 'public', 
                table: 'document_processing_jobs',
                filter: `user_id=eq.${this.user.id}`
            }, (payload) => {
                this.handleDocumentProcessingUpdate(payload);
            })
            .subscribe();
    }

    handleRealtimeEvent(payload) {
        const { event_type, event_data, created_by } = payload.new;
        
        // Don't show notifications for events created by current user
        if (created_by === this.user.id) return;
        
        switch (event_type) {
            case 'transaction_added':
                this.showPartnerNotification('New transaction added by partner', event_data);
                this.refreshDashboardData();
                break;
            case 'budget_updated':
                this.showPartnerNotification('Budget updated by partner', event_data);
                this.refreshBudgetDisplay();
                break;
            case 'goal_modified':
                this.showPartnerNotification('Goal modified by partner', event_data);
                this.refreshGoalsDisplay();
                break;
        }
    }

    handleDocumentProcessingUpdate(payload) {
        const { processing_status, file_type, extracted_data } = payload.new;
        
        if (processing_status === 'completed') {
            this.showDocumentProcessingComplete(file_type, extracted_data);
        } else if (processing_status === 'failed') {
            this.showDocumentProcessingError(file_type);
        }
    }

    // Document upload with real-time processing
    async uploadReceipt(file) {
        const formData = new FormData();
        formData.append('receipt', file);
        
        try {
            // Show processing indicator
            this.showProcessingIndicator('Processing receipt...');
            
            const response = await fetch('/couples/documents/upload-receipt', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
            
            const result = await response.json();
            
            // Show extracted data for review
            this.showExtractedDataReview(result);
            
        } catch (error) {
            this.showError('Failed to process receipt: ' + error.message);
        } finally {
            this.hideProcessingIndicator();
        }
    }

    showExtractedDataReview(extractedData) {
        // Show modal with extracted data for user review and approval
        const modal = document.getElementById('extracted-data-review-modal');
        
        // Populate extracted data
        document.getElementById('extracted-merchant').value = extractedData.extracted_data.merchant;
        document.getElementById('extracted-amount').value = extractedData.extracted_data.amount;
        document.getElementById('extracted-date').value = extractedData.extracted_data.date;
        
        // Show AI suggestions
        document.getElementById('ai-category').textContent = extractedData.ai_suggestions.category;
        document.getElementById('ai-assignment').textContent = extractedData.ai_suggestions.partner_assignment;
        document.getElementById('ai-confidence').textContent = extractedData.ai_suggestions.confidence + '%';
        
        // Show modal
        new bootstrap.Modal(modal).show();
    }
}

// Initialize enhanced dashboard
document.addEventListener('DOMContentLoaded', () => {
    window.couplesApp = new CouplesEnhancedDashboard();
});
```

## Implementation Timeline

### Phase 1: Core LangExtract Integration (Weeks 1-4)

**Week 1-2: Receipt Processing**
- [ ] Set up LangExtract service with Ollama integration
- [ ] Create receipt processing pipeline
- [ ] Implement backend endpoints for receipt upload and processing
- [ ] Add frontend receipt upload interface with real-time status

**Week 3-4: Bank Statement Processing**
- [ ] Extend LangExtract for bank statement processing
- [ ] Implement batch transaction processing
- [ ] Add duplicate detection and reconciliation
- [ ] Create approval workflow for imported transactions

### Phase 2: Real-time Collaboration (Weeks 5-8)

**Week 5-6: Supabase Real-time Setup**
- [ ] Configure Supabase real-time database subscriptions
- [ ] Implement real-time event broadcasting
- [ ] Add partner notification system
- [ ] Create conflict resolution for simultaneous edits

**Week 7-8: Advanced AI Integration**
- [ ] Enhance AI services for couples-specific context
- [ ] Implement intelligent partner assignment suggestions
- [ ] Add couples-aware spending pattern analysis
- [ ] Create AI chat assistant with couples financial context

### Phase 3: Production Optimization (Weeks 9-12)

**Week 9-10: Performance and Security**
- [ ] Optimize LangExtract processing for large documents
- [ ] Implement secure document storage and processing
- [ ] Add comprehensive error handling and retry logic
- [ ] Performance testing and optimization

**Week 11-12: Testing and Documentation**
- [ ] Comprehensive end-to-end testing
- [ ] Security audit and penetration testing
- [ ] Complete documentation and user guides
- [ ] Deployment preparation and rollout plan

## Success Metrics

### Technical Metrics
- **Document Processing Accuracy**: >95% for receipt data extraction
- **Real-time Latency**: <500ms for partner notifications
- **AI Categorization Accuracy**: >90% with couples context
- **System Uptime**: 99.9% availability

### User Experience Metrics
- **Document Processing Time**: <30 seconds average for receipts
- **User Adoption**: 80% of couples users utilize document processing
- **Partner Collaboration**: 70% of couples use real-time features
- **Error Rate**: <5% failed document processing

### Business Metrics
- **Data Entry Time Reduction**: 75% reduction vs manual entry
- **Categorization Accuracy**: 90% AI accuracy vs manual categorization
- **User Engagement**: 50% increase in transaction logging frequency
- **Partner Satisfaction**: >4.5/5 satisfaction rating

## Risk Mitigation

### Technical Risks
- **LangExtract Model Performance**: Implement confidence scoring and manual fallback
- **Supabase Rate Limits**: Monitor usage and implement caching strategies
- **Document Security**: Encrypt all uploaded documents and implement secure processing
- **AI Accuracy**: Continuous model improvement and user feedback integration

### Integration Risks
- **Firefly III Compatibility**: Maintain backward compatibility and comprehensive testing
- **Third-party Dependencies**: Implement graceful degradation and fallback options
- **Data Consistency**: Ensure transaction integrity across all systems
- **User Privacy**: Implement strict data access controls and audit logging

## Next Steps

### Immediate Actions (This Week)
1. **Set up LangExtract Development Environment**
2. **Create Document Processing Service Architecture** 
3. **Design Supabase Real-time Schema**
4. **Plan Enhanced Couples Dashboard Features**

### Key Implementation Decisions
1. **Document Storage Strategy**: Local vs cloud storage for processed documents
2. **AI Model Selection**: Optimal Ollama models for financial document processing
3. **Real-time Architecture**: Supabase vs WebSocket implementation
4. **Security Framework**: Document encryption and access control implementation

---

**Status**: Ready to implement enterprise-grade Firefly III + Supabase + LangExtract architecture for advanced couples budgeting with AI-powered document processing and real-time collaboration.