# AI Integration API Documentation

## Overview

The AI integration in Firefly III provides REST API endpoints for all AI functionality. These endpoints enable developers to integrate AI features into custom applications or extend the existing functionality.

## Base URL

All AI endpoints are prefixed with `/ai` and require user authentication.

```
Base URL: https://your-firefly-instance.com/ai
```

## Authentication

All endpoints require valid Firefly III user authentication. Include session cookies or API tokens as per standard Firefly III authentication.

## Endpoints

### 1. Dashboard

#### GET /ai/

Returns the main AI dashboard view.

**Response:** HTML dashboard page

### 2. Test Connectivity

#### GET /ai/test-connectivity

Tests connectivity to all configured AI models.

**Response:**
```json
{
  "status": "success",
  "models": {
    "ollama": {
      "available": true,
      "model": "llama3.2:latest",
      "response_time": "245ms"
    },
    "openai": {
      "available": false,
      "error": "API key not configured"
    },
    "groq": {
      "available": true,
      "model": "llama-3.1-70b-versatile",
      "response_time": "156ms"
    }
  }
}
```

### 3. Financial Insights

#### GET /ai/insights

Generates AI-powered financial insights based on user's transaction data.

**Response:**
```json
{
  "insights": [
    {
      "type": "spending_pattern",
      "title": "Increased Grocery Spending",
      "description": "Your grocery spending increased by 23% this month compared to last month.",
      "recommendation": "Consider meal planning to reduce food expenses.",
      "category": "groceries",
      "impact": "medium"
    }
  ],
  "summary": {
    "total_insights": 5,
    "categories_analyzed": 8,
    "generated_at": "2024-01-15T10:30:00Z"
  }
}
```

### 4. Chat with AI

#### POST /ai/chat

Send a message to the AI assistant for financial advice.

**Request Body:**
```json
{
  "message": "How much did I spend on dining out last month?",
  "model": "ollama",
  "_token": "csrf_token"
}
```

**Response:**
```json
{
  "response": "Based on your transaction data, you spent $342.50 on dining out last month across 12 transactions. This is 15% higher than your previous month. Consider setting a dining budget to better control these expenses.",
  "model_used": "ollama",
  "response_time": "1.2s",
  "context": {
    "transactions_analyzed": 12,
    "total_amount": 342.50,
    "comparison_period": "previous_month"
  }
}
```

### 5. Transaction Categorization

#### POST /ai/categorize-transaction

Categorize a transaction using AI.

**Request Body:**
```json
{
  "description": "Starbucks Coffee Downtown",
  "amount": 4.85,
  "model": "ollama",
  "_token": "csrf_token"
}
```

**Response:**
```json
{
  "category": "Dining Out",
  "confidence": 0.92,
  "subcategory": "Coffee & Tea",
  "reasoning": "Transaction at Starbucks indicates coffee purchase, which falls under dining out category.",
  "alternative_categories": [
    {
      "category": "Entertainment",
      "confidence": 0.15
    }
  ]
}
```

### 6. Anomaly Detection

#### GET /ai/detect-anomalies

Detect unusual spending patterns in recent transactions.

**Query Parameters:**
- `days` (optional): Number of days to analyze (default: 30)
- `sensitivity` (optional): Detection sensitivity level (low, medium, high)

**Response:**
```json
{
  "anomalies": [
    {
      "type": "unusual_amount",
      "transaction_id": 1234,
      "description": "Amazon.com Purchase",
      "amount": 1249.99,
      "date": "2024-01-14",
      "reason": "Amount is 400% higher than typical Amazon purchases",
      "severity": "high"
    },
    {
      "type": "duplicate_transaction",
      "transactions": [1235, 1236],
      "description": "Spotify Premium",
      "amount": 9.99,
      "reason": "Identical transactions within 1 hour",
      "severity": "medium"
    }
  ],
  "summary": {
    "total_anomalies": 2,
    "high_severity": 1,
    "medium_severity": 1,
    "low_severity": 0,
    "analysis_period": "30_days"
  }
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "error": true,
  "message": "Descriptive error message",
  "code": "ERROR_CODE",
  "details": {
    "additional": "error context"
  }
}
```

### Common Error Codes

- `AI_SERVICE_UNAVAILABLE`: No AI models are available
- `INVALID_MODEL`: Specified AI model is not configured
- `AUTHENTICATION_REQUIRED`: User is not authenticated
- `RATE_LIMIT_EXCEEDED`: Too many requests in short period
- `INVALID_TRANSACTION_DATA`: Malformed transaction data

## Rate Limiting

AI endpoints are rate-limited to prevent abuse:

- **Chat**: 10 requests per minute per user
- **Categorization**: 30 requests per minute per user
- **Insights**: 5 requests per minute per user
- **Anomaly Detection**: 3 requests per minute per user

## Model Configuration

### Supported AI Models

1. **Ollama (Local)**
   - Model: llama3.2:latest
   - Endpoint: http://localhost:11434
   - Privacy: Full local processing

2. **OpenAI**
   - Model: gpt-4
   - Configuration: Requires API key in environment
   - Features: Advanced reasoning

3. **Groq**
   - Model: llama-3.1-70b-versatile
   - Configuration: Requires API key
   - Features: High-speed inference

### Environment Variables

```bash
# Ollama Configuration
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2:latest

# OpenAI Configuration
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4

# Groq Configuration
GROQ_API_KEY=your_groq_key
GROQ_MODEL=llama-3.1-70b-versatile
```

## Examples

### JavaScript Fetch Example

```javascript
// Test AI connectivity
async function testAIConnectivity() {
  const response = await fetch('/ai/test-connectivity');
  const data = await response.json();
  console.log('AI Status:', data);
}

// Categorize transaction
async function categorizeTransaction(description, amount) {
  const response = await fetch('/ai/categorize-transaction', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      description: description,
      amount: amount,
      model: 'ollama'
    })
  });
  
  const data = await response.json();
  return data.category;
}

// Chat with AI
async function chatWithAI(message) {
  const response = await fetch('/ai/chat', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      message: message,
      model: 'ollama'
    })
  });
  
  const data = await response.json();
  return data.response;
}
```

### cURL Examples

```bash
# Test connectivity
curl -X GET "https://your-firefly.com/ai/test-connectivity" \
  -H "Authorization: Bearer your_token"

# Categorize transaction
curl -X POST "https://your-firefly.com/ai/categorize-transaction" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token" \
  -d '{
    "description": "McDonald'\''s Drive Thru",
    "amount": 12.45,
    "model": "ollama"
  }'

# Get financial insights
curl -X GET "https://your-firefly.com/ai/insights" \
  -H "Authorization: Bearer your_token"
```

## Response Times

Typical response times by model:

- **Ollama (Local)**: 500ms - 2s
- **OpenAI**: 1s - 3s
- **Groq**: 200ms - 800ms

Response times vary based on:
- Model complexity
- Transaction data volume
- Network latency (cloud models)
- Server resources (local models)
