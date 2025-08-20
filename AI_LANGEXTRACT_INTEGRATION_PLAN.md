# Couples Mobile App - Integration Roadmap with AI & LangExtract

**Date**: August 19, 2025  
**Status**: ✅ **Shadcn UI Foundation Complete - Ready for AI Integration**  
**Next Phase**: LangExtract AI Service Integration and Real-time Collaboration

---

## 🎉 Current Achievement Summary

### ✅ What's Already Complete

**🎨 Shadcn UI Foundation (Just Completed)**
- ✅ **26+ Professional Components**: Card, Badge, Button, Avatar, Progress, Tabs, Charts
- ✅ **CouplesDashboard.jsx**: Complete React dashboard with mobile-first design
- ✅ **Vite Build System**: React + Laravel integration (8.39s build time)
- ✅ **Mobile-Optimized**: Touch gestures, responsive design, PWA-ready
- ✅ **Charts & Analytics**: Budget progress, spending categories, trend analysis

**🏗️ Backend Infrastructure (Previously Complete)**
- ✅ **CouplesController**: API endpoints ready for AI integration
- ✅ **Database Schema**: Couples profiles, transactions, real-time events
- ✅ **Docker Environment**: AI services with Ollama GPU support
- ✅ **Authentication**: Firefly III user management integrated

**📱 Dashboard Features (Working)**
- ✅ **Partner Collaboration UI**: Avatars, shared transactions, assignments
- ✅ **Receipt Upload Interface**: Camera integration (UI ready)
- ✅ **Real-time Charts**: Budget visualization with Recharts
- ✅ **Quick Actions**: Add transactions, scan receipts (frontend complete)

---

## 🎯 Phase 2: AI & LangExtract Integration Plan

### **Critical Path - 4 Week Implementation**

### Week 1: LangExtract Service Foundation

#### **1.1 Environment Setup & Integration**

**Install LangExtract with Ollama Support**
```bash
# Install LangExtract for local AI processing
pip install langextract[all]

# Configure for privacy-focused local processing
export LANGEXTRACT_MODEL_PROVIDER=ollama
export LANGEXTRACT_MODEL_NAME=llama3.2
export LANGEXTRACT_ENDPOINT=http://localhost:11434

# Pull optimized models for receipt processing
docker-compose -f docker-compose.ai.yml exec ollama ollama pull gemma3:270m
docker-compose -f docker-compose.ai.yml exec ollama ollama pull llama3.2
```

**Create LangExtract Service Class**
```php
// File: app/Services/LangExtractService.php
<?php
namespace FireflyIII\Services;

class LangExtractService 
{
    private $ollamaUrl;
    private $defaultModel;
    
    public function __construct()
    {
        $this->ollamaUrl = config('ai.ollama_url', 'http://localhost:11434');
        $this->defaultModel = config('ai.receipt_model', 'gemma3:270m');
    }
    
    public function processReceipt($file, $schema = null): array
    {
        // Convert uploaded file to base64 for LangExtract
        $imageData = base64_encode(file_get_contents($file->getPathname()));
        
        // Default schema for receipt processing
        $schema = $schema ?: [
            'merchant' => 'string',
            'amount' => 'number',
            'date' => 'date',
            'category' => 'string',
            'items' => 'array',
            'tax' => 'number',
            'payment_method' => 'string'
        ];
        
        // Call LangExtract with local Ollama model
        $response = Http::post($this->ollamaUrl . '/api/langextract', [
            'model' => $this->defaultModel,
            'image' => $imageData,
            'schema' => $schema,
            'temperature' => 0.2, // Precise extraction
            'max_tokens' => 2048
        ]);
        
        if ($response->successful()) {
            $extractedData = $response->json();
            
            // Add confidence scoring
            $extractedData['confidence'] = $this->calculateConfidence($extractedData);
            $extractedData['processing_time'] = $response->header('X-Processing-Time');
            
            return $extractedData;
        }
        
        throw new \Exception('LangExtract processing failed: ' . $response->body());
    }
    
    public function processBankStatement($file, $schema = null): array
    {
        // Implement bank statement processing with different schema
        $schema = $schema ?: [
            'transactions' => 'array',
            'account_info' => 'object',
            'statement_period' => 'object',
            'balance_info' => 'object'
        ];
        
        // Similar processing for bank statements
        return $this->processDocument($file, $schema, 'bank_statement');
    }
    
    private function calculateConfidence($data): float
    {
        // Implement confidence scoring based on field completeness
        $requiredFields = ['merchant', 'amount', 'date'];
        $completedFields = array_filter($requiredFields, fn($field) => !empty($data[$field]));
        
        return (count($completedFields) / count($requiredFields)) * 100;
    }
}
```

#### **1.2 Backend API Enhancement**

**Update CouplesController with Real AI Processing**
```php
// File: app/Http/Controllers/CouplesController.php

public function uploadReceipt(Request $request): JsonResponse 
{
    $request->validate([
        'receipt' => 'required|image|max:10240', // 10MB max
        'couples_profile_id' => 'required|exists:couples_profiles,id'
    ]);
    
    try {
        $receiptFile = $request->file('receipt');
        $couplesProfile = CouplesProfile::findOrFail($request->couples_profile_id);
        
        // Process receipt with LangExtract
        $langExtractService = app(LangExtractService::class);
        $extractedData = $langExtractService->processReceipt($receiptFile);
        
        // AI categorization with couples context
        $aiService = app(AIService::class);
        $aiCategory = $aiService->categorizeForCouples($extractedData, $couplesProfile);
        
        // Store receipt image
        $receiptPath = $receiptFile->store('receipts', 'public');
        
        // Create receipt record
        $receipt = Receipt::create([
            'couples_profile_id' => $couplesProfile->id,
            'file_path' => $receiptPath,
            'extracted_data' => $extractedData,
            'ai_suggestions' => $aiCategory,
            'confidence_score' => $extractedData['confidence'],
            'processing_time' => $extractedData['processing_time'] ?? null,
            'status' => $extractedData['confidence'] > 80 ? 'auto_processed' : 'needs_review'
        ]);
        
        // Broadcast real-time update to partner
        $this->broadcastReceiptProcessed($couplesProfile, $receipt);
        
        return response()->json([
            'success' => true,
            'receipt_id' => $receipt->id,
            'extracted_data' => $extractedData,
            'ai_suggestions' => $aiCategory,
            'confidence' => $extractedData['confidence'],
            'requires_review' => $extractedData['confidence'] < 80,
            'suggested_transaction' => $this->buildSuggestedTransaction($extractedData, $aiCategory)
        ]);
        
    } catch (\Exception $e) {
        Log::error('Receipt processing failed', [
            'error' => $e->getMessage(),
            'couples_profile_id' => $request->couples_profile_id
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Receipt processing failed. Please try again.',
            'details' => app()->environment('local') ? $e->getMessage() : null
        ], 500);
    }
}

private function buildSuggestedTransaction($extractedData, $aiCategory): array
{
    return [
        'description' => $extractedData['merchant'] ?? 'Unknown Merchant',
        'amount' => $extractedData['amount'] ?? 0,
        'date' => $extractedData['date'] ?? now()->format('Y-m-d'),
        'category_id' => $aiCategory['category_id'] ?? null,
        'partner_assignment' => $aiCategory['partner_assignment'] ?? 'shared',
        'notes' => 'Auto-generated from receipt scan',
        'tags' => $aiCategory['suggested_tags'] ?? []
    ];
}
```

### Week 2: AI Service Enhancement

#### **2.1 Couples-Specific AI Categorization**

**Enhance AIService for Couples Context**
```php
// File: app/Services/AIService.php

public function categorizeForCouples($transactionData, $couplesProfile): array
{
    // Get couples spending patterns for context
    $spendingPatterns = $this->getCouplesSpendingPatterns($couplesProfile);
    $sharedCategories = $couplesProfile->shared_categories ?? [];
    
    $prompt = $this->buildCouplesPrompt($transactionData, $couplesProfile, $spendingPatterns);
    
    $response = $this->callOllama([
        'model' => config('ai.analysis_model', 'llama3.2'),
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a financial categorization assistant specializing in couples budgeting. Analyze transactions and suggest appropriate categories and partner assignments.'
            ],
            [
                'role' => 'user', 
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 1024
    ]);
    
    return $this->parseAIResponse($response);
}

private function buildCouplesPrompt($transactionData, $couplesProfile, $patterns): string
{
    return \"\"\"
    Analyze this transaction for a couple and provide categorization:
    
    TRANSACTION DATA:
    - Merchant: {$transactionData['merchant']}
    - Amount: ${$transactionData['amount']}
    - Date: {$transactionData['date']}
    - Items: \" . json_encode($transactionData['items'] ?? []) . \"
    
    COUPLES CONTEXT:
    - Partner 1: {$couplesProfile->partner1_name}
    - Partner 2: {$couplesProfile->partner2_name}
    - Shared Categories: \" . implode(', ', $sharedCategories) . \"
    - Monthly Budget: ${$couplesProfile->monthly_budget}
    
    SPENDING PATTERNS:
    {$patterns}
    
    Please provide JSON response with:
    {
        \"category\": \"suggested category name\",
        \"category_id\": \"firefly_category_id or null\",
        \"partner_assignment\": \"partner1|partner2|shared\",
        \"confidence\": 85,
        \"reasoning\": \"Brief explanation of categorization\",
        \"suggested_tags\": [\"tag1\", \"tag2\"],
        \"budget_impact\": \"low|medium|high\",
        \"similar_transactions\": \"References to similar past transactions\"
    }
    \"\"\";
}

private function getCouplesSpendingPatterns($couplesProfile): string
{
    // Analyze last 3 months of transactions for patterns
    $recentTransactions = Transaction::where('couples_profile_id', $couplesProfile->id)
        ->where('created_at', '>=', now()->subMonths(3))
        ->with('category')
        ->get();
    
    $categorySpending = $recentTransactions->groupBy('category.name')
        ->map(fn($transactions) => [
            'total' => $transactions->sum('amount'),
            'count' => $transactions->count(),
            'avg_amount' => $transactions->avg('amount'),
            'frequency' => $transactions->count() / 12 // per month
        ])
        ->sortByDesc('total')
        ->take(10);
    
    return $categorySpending->map(fn($data, $category) => 
        \"- {$category}: ${$data['total']} total, {$data['count']} transactions, avg ${$data['avg_amount']}\"
    )->implode(\"\\n\");
}
```

### Week 3: Supabase Real-time Integration

#### **3.1 Real-time Database Setup**

**Create Real-time Schema**
```sql
-- File: database/migrations/add_supabase_realtime.sql

-- Real-time events for partner collaboration
CREATE TABLE couples_realtime_events (
    id SERIAL PRIMARY KEY,
    couples_profile_id INTEGER NOT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'receipt_processed', 'transaction_added', 'budget_updated'
    event_data JSONB NOT NULL,
    created_by INTEGER REFERENCES users(id),
    partner_notified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Enable real-time subscriptions for Supabase
ALTER PUBLICATION supabase_realtime ADD TABLE couples_realtime_events;
ALTER PUBLICATION supabase_realtime ADD TABLE transactions;
ALTER PUBLICATION supabase_realtime ADD TABLE receipts;
ALTER PUBLICATION supabase_realtime ADD TABLE budgets;

-- Indexes for performance
CREATE INDEX idx_couples_events_profile_id ON couples_realtime_events(couples_profile_id);
CREATE INDEX idx_couples_events_type ON couples_realtime_events(event_type);
CREATE INDEX idx_couples_events_created_at ON couples_realtime_events(created_at);

-- Row Level Security for multi-user support
ALTER TABLE couples_realtime_events ENABLE ROW LEVEL SECURITY;

CREATE POLICY \"Users can view their couples events\" ON couples_realtime_events
    FOR SELECT USING (
        couples_profile_id IN (
            SELECT id FROM couples_profiles 
            WHERE partner1_user_id = auth.uid() OR partner2_user_id = auth.uid()
        )
    );
```

#### **3.2 Frontend Real-time Integration**

**Add Supabase to CouplesDashboard.jsx**
```jsx
// File: resources/assets/v2/src/components/couples/CouplesDashboard.jsx

import { createClient } from '@supabase/supabase-js';
import { useState, useEffect } from 'react';
import { toast } from '@/components/ui/use-toast';

const supabase = createClient(
  process.env.REACT_APP_SUPABASE_URL || 'http://localhost:54321',
  process.env.REACT_APP_SUPABASE_ANON_KEY
);

export function CouplesDashboard() {
  const [transactions, setTransactions] = useState([]);
  const [partnerActivity, setPartnerActivity] = useState([]);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    // Subscribe to real-time partner updates
    const subscription = supabase
      .channel('couples-collaboration')
      .on('postgres_changes', {
        event: '*',
        schema: 'public',
        table: 'couples_realtime_events',
        filter: `couples_profile_id=eq.${couplesProfileId}`
      }, handlePartnerUpdate)
      .on('postgres_changes', {
        event: 'INSERT',
        schema: 'public',
        table: 'transactions',
        filter: `couples_profile_id=eq.${couplesProfileId}`
      }, handleNewTransaction)
      .subscribe((status) => {
        setIsConnected(status === 'SUBSCRIBED');
      });

    return () => {
      subscription.unsubscribe();
    };
  }, [couplesProfileId]);

  const handlePartnerUpdate = (payload) => {
    const { eventType, eventData, createdBy } = payload.new;
    
    // Show partner notification
    if (createdBy !== currentUserId) {
      switch (eventType) {
        case 'receipt_processed':
          toast({
            title: \"Receipt Processed\",
            description: `${partnerName} processed a receipt for ${eventData.merchant}`,
            action: <Button onClick={() => reviewReceipt(eventData.receiptId)}>Review</Button>
          });
          break;
          
        case 'transaction_added':
          toast({
            title: \"New Transaction\",
            description: `${partnerName} added: ${eventData.description} - $${eventData.amount}`
          });
          refreshTransactions();
          break;
          
        case 'budget_updated':
          toast({
            title: \"Budget Updated\",
            description: `${partnerName} updated the ${eventData.category} budget`
          });
          refreshBudgets();
          break;
      }
    }
    
    // Update activity timeline
    setPartnerActivity(prev => [payload.new, ...prev.slice(0, 9)]);
  };

  const handleNewTransaction = (payload) => {
    // Add new transaction to list
    setTransactions(prev => [payload.new, ...prev]);
    
    // Update budget charts
    updateBudgetProgress(payload.new);
  };

  const handleReceiptUpload = async (file) => {
    setUploading(true);
    
    try {
      const formData = new FormData();
      formData.append('receipt', file);
      formData.append('couples_profile_id', couplesProfileId);
      
      const response = await fetch('/api/couples/upload-receipt', {
        method: 'POST',
        body: formData,
        headers: {
          'Authorization': `Bearer ${apiToken}`,
          'X-CSRF-TOKEN': csrfToken,
        }
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Show AI extraction results
        showReceiptResults(result);
        
        // If high confidence, auto-create transaction
        if (result.confidence > 85) {
          await createTransactionFromReceipt(result.suggested_transaction);
        } else {
          // Show review dialog for low confidence
          showReviewDialog(result);
        }
      } else {
        throw new Error(result.error);
      }
      
    } catch (error) {
      toast({
        title: \"Upload Failed\",
        description: error.message,
        variant: \"destructive\"
      });
    } finally {
      setUploading(false);
    }
  };

  const createTransactionFromReceipt = async (transactionData) => {
    try {
      const response = await fetch('/api/couples/transactions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${apiToken}`,
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          ...transactionData,
          couples_profile_id: couplesProfileId,
          source: 'receipt_scan'
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        toast({
          title: \"Transaction Created\",
          description: `Added ${result.transaction.description} - $${result.transaction.amount}`,
          action: <Button onClick={() => viewTransaction(result.transaction.id)}>View</Button>
        });
      }
      
    } catch (error) {
      console.error('Transaction creation failed:', error);
    }
  };

  return (
    <div className=\"min-h-screen bg-background p-4 space-y-6\">
      {/* Connection Status */}
      <div className=\"flex items-center justify-between\">
        <div>
          <h1 className=\"text-2xl font-bold\">Couples Budget</h1>
          <div className=\"flex items-center gap-2\">
            <div className={`w-2 h-2 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`} />
            <p className=\"text-sm text-muted-foreground\">
              {isConnected ? 'Connected to partner' : 'Connecting...'}
            </p>
          </div>
        </div>
        <div className=\"flex items-center gap-2\">
          <Avatar className=\"h-8 w-8\">
            <AvatarFallback>{partner1Initial}</AvatarFallback>
          </Avatar>
          <Avatar className=\"h-8 w-8\">
            <AvatarFallback>{partner2Initial}</AvatarFallback>
          </Avatar>
        </div>
      </div>

      {/* Receipt Upload with AI Processing */}
      <div className=\"grid grid-cols-2 gap-4\">
        <Button 
          className=\"h-16 flex flex-col gap-1\"
          onClick={() => document.getElementById('receipt-input').click()}
          disabled={uploading}
        >
          <Camera className=\"h-5 w-5\" />
          <span className=\"text-sm\">
            {uploading ? 'Processing...' : 'Scan Receipt'}
          </span>
        </Button>
        <input
          id=\"receipt-input\"
          type=\"file\"
          accept=\"image/*\"
          capture=\"environment\"
          className=\"hidden\"
          onChange={(e) => e.target.files[0] && handleReceiptUpload(e.target.files[0])}
        />
        
        <Button variant=\"outline\" className=\"h-16 flex flex-col gap-1\">
          <Plus className=\"h-5 w-5\" />
          <span className=\"text-sm\">Add Transaction</span>
        </Button>
      </div>

      {/* Rest of dashboard components with real-time data */}
      {/* Budget Overview, Charts, Recent Transactions, etc. */}
    </div>
  );
}
```

### Week 4: Testing & Production Readiness

#### **4.1 End-to-End Testing**

**Complete Workflow Testing**
- Receipt upload → AI processing → Transaction creation → Partner notification
- Real-time collaboration with multiple browser sessions
- Mobile device testing with camera integration
- Error handling and recovery scenarios
- Performance testing with concurrent uploads

#### **4.2 Production Optimization**

**Performance Enhancements**
- Image compression before upload
- Caching AI responses for similar receipts
- Database query optimization
- Real-time connection management
- Mobile PWA features (offline support, push notifications)

---

## 🎯 Success Metrics

### **Technical Targets**
- **Receipt Processing Accuracy**: >95% for common receipts
- **Processing Time**: <30 seconds with real-time feedback
- **Real-time Latency**: <500ms for partner notifications  
- **Mobile Performance**: <3 second load time
- **AI Categorization**: >90% accuracy for couples context

### **User Experience Goals**
- **Seamless Receipt Workflow**: Camera → AI processing → Transaction creation
- **Real-time Collaboration**: Instant partner updates and notifications
- **Mobile App Experience**: PWA features with offline capabilities
- **Error Recovery**: Graceful handling of processing failures

---

## 📊 Implementation Checklist

### **Week 1: LangExtract Foundation**
- [ ] Install and configure LangExtract with Ollama
- [ ] Create LangExtractService class with receipt processing
- [ ] Update CouplesController::uploadReceipt() with real AI
- [ ] Test receipt upload workflow end-to-end
- [ ] Validate AI extraction accuracy (>90% target)

### **Week 2: AI Enhancement**
- [ ] Implement couples-specific AI categorization
- [ ] Add spending pattern analysis for context
- [ ] Create confidence scoring and review system
- [ ] Implement bank statement processing
- [ ] Add error handling and retry logic

### **Week 3: Real-time Integration**
- [ ] Configure Supabase database schema
- [ ] Implement real-time event broadcasting
- [ ] Add partner notification system to UI
- [ ] Create conflict resolution for simultaneous edits
- [ ] Test real-time updates between sessions

### **Week 4: Production Ready**
- [ ] End-to-end testing with real users
- [ ] Performance optimization and caching
- [ ] Mobile device testing and PWA features
- [ ] Security testing and error handling
- [ ] Documentation and deployment guide

---

## 🚀 Ready to Build

**Current State**: ✅ **Perfect foundation with Shadcn UI + Backend APIs ready**

**What's Working**:
- Complete modern UI component library
- Professional mobile-first couples dashboard  
- Backend API endpoints ready for integration
- Docker environment with AI services
- Database schema for couples collaboration

**What's Next**: **Connect the dots** - integrate AI services with the beautiful UI you've built!

**Success Criteria**: Receipt upload → AI extraction → Smart categorization → Real-time partner notification → Transaction creation - all with a beautiful, mobile-first interface! 🎉📱💕