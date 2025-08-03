# AI Integration Documentation for Firefly III

## 🎉 AI Dashboard Successfully Integrated!

This document contains comprehensive documentation for the completed AI integration in Firefly III. The AI Dashboard is now fully functional and provides intelligent financial management capabilities.

## ✅ Completed Features

### 🤖 AI Dashboard Overview
- **Location**: Available at `/ai` in your Firefly III installation
- **Access**: Navigate via the AI Assistant menu item in the sidebar
- **Authentication**: Protected by user authentication middleware

### 🚀 Core AI Features

#### 1. **AI Service Status Monitoring**
- Real-time connectivity testing for local and cloud AI models
- Support for multiple AI providers:
  - 🏠 **Ollama (Local)**: Llama 3.2 model running on port 11434
  - ☁️ **OpenAI**: GPT-4 integration for cloud-based processing
  - ⚡ **Groq**: High-speed AI inference

#### 2. **Financial Insights Generator**
- AI-powered analysis of spending patterns
- Personalized financial recommendations
- Monthly spending comparisons and alerts

#### 3. **Smart Transaction Categorization**
- Intelligent categorization of transactions based on description and amount
- Rule-based fallback system for reliable categorization
- Categories include: Groceries, Transportation, Dining Out, Utilities, Housing, etc.

#### 4. **Spending Anomaly Detection**
- Identifies unusual spending patterns
- Detects potential duplicate transactions
- Alerts for spending that deviates from normal patterns

#### 5. **Interactive AI Chat Assistant**
- Real-time chat interface for financial advice
- Context-aware responses based on user's financial data
- Natural language processing for financial queries

## 🛠 Technical Implementation

### Architecture Components

#### Backend Services
- **AIService** (`app/Services/Internal/AIService.php`): Core AI integration service
- **DashboardController** (`app/Http/Controllers/AI/DashboardController.php`): Main AI dashboard controller
- **Service Registration**: Properly configured in Laravel's service container

#### Frontend Interface
- **Dashboard Template** (`resources/views/ai/dashboard.twig`): Responsive AI dashboard UI
- **Menu Integration**: AI menu item in sidebar navigation
- **Chat Modal**: Full-featured chat interface with real-time messaging

#### Route Configuration
```php
// AI Dashboard Routes
Route::group([
    'middleware' => ['user-full-auth'],
    'namespace'  => 'FireflyIII\Http\Controllers\AI',
    'prefix'     => 'ai',
    'as'         => 'ai.',
], static function (): void {
    Route::get('/', ['uses' => 'DashboardController@index', 'as' => 'index']);
    Route::get('test-connectivity', ['uses' => 'DashboardController@testConnectivity', 'as' => 'test-connectivity']);
    Route::get('insights', ['uses' => 'DashboardController@getInsights', 'as' => 'insights']);
    Route::post('chat', ['uses' => 'DashboardController@chat', 'as' => 'chat']);
    Route::post('categorize-transaction', ['uses' => 'DashboardController@categorizeTransaction', 'as' => 'categorize-transaction']);
    Route::get('detect-anomalies', ['uses' => 'DashboardController@detectAnomalies', 'as' => 'detect-anomalies']);
});
```

## 📱 User Interface Features

### Dashboard Cards
1. **AI Status Card** - Shows connectivity status with test button
2. **Financial Insights Card** - Displays AI-generated insights count
3. **Anomaly Detection Card** - Shows detected spending anomalies
4. **Chat Assistant Card** - Access to interactive AI chat

### Interactive Tools
- **Transaction Categorization Tool**: Input description and amount for AI categorization
- **Model Selection**: Choose between Local (Llama), OpenAI, or Groq models
- **Real-time Chat**: Full conversation interface with the AI assistant

## 🔧 Configuration

### AI Model Setup
The system is configured to work with:
- **Ollama**: Running on `localhost:11434` with Llama 3.2 model
- **OpenAI**: API key configuration in environment variables
- **Groq**: Cloud-based inference for high-speed processing

### Security Features
- **Content Security Policy (CSP)**: All JavaScript properly nonces for security
- **Authentication**: Protected by Firefly III's user authentication system
- **CSRF Protection**: All POST requests include CSRF token validation

## 📚 Original Integration Documentation

The following sections contain the research and planning documentation that guided the implementation:

## Table of Contents

1. [Laravel Framework Documentation](#laravel-framework-documentation)
2. [Ollama Local LLM Documentation](#ollama-local-llm-documentation)
3. [Hugging Face Transformers Documentation](#hugging-face-transformers-documentation)
4. [Model Context Protocol (MCP) Documentation](#model-context-protocol-mcp-documentation)
5. [Coolify Deployment Documentation](#coolify-deployment-documentation)
6. [Implementation Recommendations](#implementation-recommendations)

## Laravel Framework Documentation

### Key Laravel Features for AI Integration

#### Background Jobs and Queues
Laravel's queue system is essential for AI processing, which can be time-consuming:

```bash
# Generate a new job
php artisan make:job ProcessPodcast

# Run queue workers
php artisan queue:work

# Run queue workers with Docker/Coolify
php artisan queue:work --queue=high,default --tries=3 --timeout=60
```

#### Artisan Commands for AI Tasks
Create custom Artisan commands for AI operations:

```bash
# Generate new command
php artisan make:command AnalyzeSpending

# Queue commands for background processing
php artisan queue:work redis --queue=scout
```

#### API Routes for AI Endpoints
Structure for creating AI API endpoints:

```php
// In routes/api.php
Route::post('/ai/categorize-transaction', [AIController::class, 'categorizeTransaction']);
Route::post('/ai/generate-insights', [AIController::class, 'generateInsights']);
Route::post('/ai/create-rule', [AIController::class, 'createRule']);
```

#### Docker Configuration for Laravel
Environment variables for Docker deployment:

```bash
APP_DEBUG=false
APP_ENV=production
APP_KEY= #YourAppKey
CACHE_STORE=redis
DB_CONNECTION=mysql
DB_HOST=<DB_HOST>
DB_PORT=3306
QUEUE_CONNECTION=redis
REDIS_HOST=<REDIS_HOST>
REDIS_PORT=6379
```

## Ollama Local LLM Documentation

### API Endpoints

Ollama provides REST API endpoints for local LLM integration:

#### Text Generation
```bash
# Generate text completion
curl http://localhost:11434/api/generate -d '{
  "model": "llama3.2",
  "prompt": "If the description contains 'Starbucks', categorize it as 'Coffee'"
}'

# Chat completion
curl http://localhost:11434/api/chat -d '{
  "model": "llama3.2",
  "messages": [
    {
      "role": "user",
      "content": "Analyze this transaction: $4.50 at Starbucks"
    }
  ]
}'
```

#### Docker Deployment
```bash
# Run Ollama in Docker
docker run -d -p 11434:11434 ollama/ollama

# Pull a model
docker exec -it ollama ollama pull llama3.2
```

### Integration with Laravel Services

Create a Laravel service to communicate with Ollama:

```php
class OllamaService
{
    public function categorizeTransaction(string $description): string
    {
        $response = Http::post('http://localhost:11434/api/chat', [
            'model' => 'llama3.2',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a financial transaction categorizer.'
                ],
                [
                    'role' => 'user',
                    'content' => "Categorize this transaction: {$description}"
                ]
            ]
        ]);
        
        return $response->json()['message']['content'];
    }
}
```

## Hugging Face Transformers Documentation

### Text Generation with Transformers

For self-hosted models using Hugging Face Transformers:

#### Docker Text Generation Inference (TGI)
```bash
# Serve a model with TGI
docker run --gpus all --shm-size 1g -p 8080:80 \
  -v $volume:/data \
  ghcr.io/huggingface/text-generation-inference:latest \
  --model-id microsoft/DialoGPT-large
```

#### Python Pipeline Integration
```python
from transformers import pipeline

# Create text generation pipeline
pipeline = pipeline(
    task="text-generation",
    model="microsoft/DialoGPT-large",
    torch_dtype=torch.float16,
    device=0
)

# Generate response
response = pipeline("Analyze this spending pattern: $50/week on coffee")
```

### Integration Options

1. **Direct API calls** to TGI server from Laravel
2. **Python service** that Laravel communicates with via HTTP
3. **Queue-based processing** for heavy model inference

## Model Context Protocol (MCP) Documentation

### MCP Server Implementation

Create an MCP server for Firefly III to expose financial data to AI agents:

#### Python MCP Server
```python
import asyncio
import mcp.types as types
from mcp.server import Server
from mcp.server.stdio import stdio_server

app = Server("firefly-iii-server")

@app.list_resources()
async def list_resources() -> list[types.Resource]:
    return [
        types.Resource(
            uri="firefly://transactions",
            name="Firefly III Transactions"
        ),
        types.Resource(
            uri="firefly://accounts",
            name="Firefly III Accounts"
        )
    ]

@app.list_tools()
async def list_tools() -> list[types.Tool]:
    return [
        types.Tool(
            name="create_rule",
            description="Create a new transaction rule",
            inputSchema={
                "type": "object",
                "properties": {
                    "description": {"type": "string"},
                    "category": {"type": "string"}
                },
                "required": ["description", "category"]
            }
        )
    ]

@app.call_tool()
async def call_tool(name: str, arguments: dict) -> list[types.TextContent]:
    if name == "create_rule":
        # Implement rule creation logic
        result = create_firefly_rule(arguments)
        return [types.TextContent(type="text", text=str(result))]
    
    raise ValueError(f"Tool not found: {name}")

async def main():
    async with stdio_server() as streams:
        await app.run(streams[0], streams[1], app.create_initialization_options())

if __name__ == "__main__":
    asyncio.run(main())
```

#### TypeScript MCP Server
```typescript
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";

const server = new Server({
  name: "firefly-iii-server",
  version: "1.0.0"
}, {
  capabilities: {
    resources: {},
    tools: {}
  }
});

// Handle transaction analysis requests
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  if (request.params.name === "analyze_spending") {
    const analysis = await analyzeSpendingPatterns(request.params.arguments);
    return {
      content: [{ type: "text", text: analysis }]
    };
  }
});

const transport = new StdioServerTransport();
await server.connect(transport);
```

### MCP Client Integration

Integrate MCP clients within Firefly III to communicate with AI agents:

```php
class MCPClient
{
    public function analyzeSpending(array $transactions): string
    {
        $process = new Process([
            'node',
            '/path/to/mcp-client.js',
            json_encode($transactions)
        ]);
        
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        
        return $process->getOutput();
    }
}
```

## Coolify Deployment Documentation

### Docker Compose Configuration for Firefly III with AI

Create a comprehensive Docker Compose setup:

```yaml
services:
  firefly-iii:
    image: fireflyiii/core:latest
    environment:
      - APP_ENV=production
      - DB_CONNECTION=mysql
      - DB_HOST=firefly-db
      - DB_DATABASE=firefly
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=firefly-redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - firefly-db
      - firefly-redis
    volumes:
      - firefly-uploads:/var/www/html/storage/upload

  firefly-db:
    image: mysql:8.0
    environment:
      - MYSQL_DATABASE=firefly
      - MYSQL_USER=${DB_USERNAME}
      - MYSQL_PASSWORD=${DB_PASSWORD}
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
    volumes:
      - firefly-db-data:/var/lib/mysql

  firefly-redis:
    image: redis:7-alpine
    volumes:
      - firefly-redis-data:/data

  firefly-queue:
    image: fireflyiii/core:latest
    command: php artisan queue:work --queue=ai-processing,default --tries=3
    environment:
      - APP_ENV=production
      - DB_CONNECTION=mysql
      - DB_HOST=firefly-db
      - REDIS_HOST=firefly-redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - firefly-db
      - firefly-redis

  ollama:
    image: ollama/ollama:latest
    ports:
      - "11434:11434"
    volumes:
      - ollama-data:/root/.ollama
    environment:
      - OLLAMA_HOST=0.0.0.0

  text-generation-inference:
    image: ghcr.io/huggingface/text-generation-inference:latest
    ports:
      - "8080:80"
    environment:
      - MODEL_ID=microsoft/DialoGPT-large
    volumes:
      - tgi-data:/data

volumes:
  firefly-uploads:
  firefly-db-data:
  firefly-redis-data:
  ollama-data:
  tgi-data:
```

### Environment Variables for Coolify

Configure these environment variables in Coolify dashboard:

```bash
# Firefly III Configuration
APP_ENV=production
APP_KEY=${FIREFLY_APP_KEY}
DB_CONNECTION=mysql
DB_HOST=firefly-db
DB_DATABASE=firefly
DB_USERNAME=${SERVICE_USER_MYSQL}
DB_PASSWORD=${SERVICE_PASSWORD_MYSQL}
REDIS_HOST=firefly-redis
QUEUE_CONNECTION=redis

# AI Service Configuration
OLLAMA_HOST=ollama
OLLAMA_PORT=11434
TGI_HOST=text-generation-inference
TGI_PORT=80

# MCP Configuration
MCP_SERVER_PATH=/usr/local/bin/firefly-mcp-server
MCP_CLIENT_ENABLED=true

# API Keys (if using external services)
OPENAI_API_KEY=${OPENAI_API_KEY}
ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY}
```

### Post-Deployment Commands

Add these to Coolify's post-deployment script:

```bash
# Laravel optimization
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# Database migrations
php artisan migrate --force

# Queue setup
php artisan queue:restart

# Install Ollama models (if needed)
docker exec ollama ollama pull llama3.2
docker exec ollama ollama pull codellama
```

## Implementation Recommendations

### 1. Architecture Overview

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Firefly III   │────│   AI Gateway     │────│  Local LLMs     │
│   (Laravel)     │    │   (MCP/API)      │    │  (Ollama/TGI)   │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         │              ┌────────┴────────┐              │
         │              │                 │              │
         ▼              ▼                 ▼              ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Queue       │    │     Redis       │    │   Database      │
│   Workers       │    │     Cache       │    │   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 2. Implementation Steps

1. **Phase 1: Infrastructure Setup**
   - Deploy Firefly III with Coolify
   - Set up Ollama for local LLM hosting
   - Configure Redis for caching and queues

2. **Phase 2: AI Service Integration**
   - Create Laravel services for AI communication
   - Implement background job processing
   - Set up MCP server for external AI agents

3. **Phase 3: Feature Implementation**
   - Natural language rule creation
   - Automatic transaction categorization
   - Spending analysis and insights
   - Financial advice system

4. **Phase 4: Optimization**
   - Performance tuning
   - Caching strategies
   - Queue optimization
   - Model fine-tuning

### 3. Security Considerations

- Use environment variables for all sensitive data
- Implement rate limiting for AI endpoints
- Validate all AI-generated content before applying
- Use HTTPS for all external communications
- Regularly update AI models and dependencies

### 4. Monitoring and Logging

```php
// Add to Laravel logging configuration
'channels' => [
    'ai' => [
        'driver' => 'daily',
        'path' => storage_path('logs/ai.log'),
        'level' => 'info',
        'days' => 14,
    ],
],
```

### 5. Performance Optimization

- Use Redis for caching AI responses
- Implement request queuing for expensive operations
- Consider model quantization for faster inference
- Use connection pooling for database operations

This documentation provides a comprehensive foundation for implementing AI features in your Firefly III deployment using Coolify and Docker. The combination of local LLMs (Ollama), enterprise-grade inference (Hugging Face TGI), and standardized protocols (MCP) creates a robust, privacy-focused AI integration platform.
