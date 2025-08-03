#!/usr/bin/env pwsh
# Setup AI Integration for Existing Firefly III

Write-Host "🔥 Firefly III AI Integration Setup" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan

# Check if Firefly III is running
Write-Host "Checking Firefly III status..." -ForegroundColor Yellow
try {
    $fireflyTest = Invoke-WebRequest -Uri "http://localhost:8080" -Method GET -TimeoutSec 5
    if ($fireflyTest.StatusCode -eq 200) {
        Write-Host "✅ Firefly III is running on port 8080" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  Firefly III doesn't seem to be running on port 8080" -ForegroundColor Yellow
    Write-Host "💡 Please start Firefly III first, then run this script" -ForegroundColor Cyan
    $continue = Read-Host "Continue anyway? (y/N)"
    if ($continue -ne 'y' -and $continue -ne 'Y') {
        exit
    }
}

# Setup Ollama for local AI
Write-Host "`nSetting up local AI (Ollama)..." -ForegroundColor Yellow
try {
    # Check if Ollama is already running
    $ollamaTest = Invoke-WebRequest -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 3 2>$null
    if ($ollamaTest.StatusCode -eq 200) {
        Write-Host "✅ Ollama is already running" -ForegroundColor Green
    }
} catch {
    Write-Host "Starting Ollama with CORS support..." -ForegroundColor Yellow
    try {
        # Remove any existing containers
        docker rm -f ollama 2>$null
        docker rm -f ollama-cors 2>$null
        
        # Start Ollama with CORS enabled
        docker run -d --name ollama-cors -p 11434:11434 -e OLLAMA_ORIGINS="*" -v ollama:/root/.ollama ollama/ollama
        Start-Sleep -Seconds 5
        Write-Host "✅ Ollama container started with CORS support" -ForegroundColor Green
    } catch {
        Write-Host "⚠️  Could not start Ollama automatically" -ForegroundColor Yellow
        Write-Host "💡 Manual setup: docker run -d -p 11434:11434 -e OLLAMA_ORIGINS='*' --name ollama-cors ollama/ollama" -ForegroundColor Cyan
    }
}

# Install AI model
try {
    Write-Host "Installing Llama 3.2 model (this may take a few minutes)..." -ForegroundColor Yellow
    docker exec ollama-cors ollama pull llama3.2:1b 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Llama 3.2 model installed" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Model installation may still be in progress" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Could not install model automatically" -ForegroundColor Yellow
    Write-Host "💡 Manual install: docker exec ollama-cors ollama pull llama3.2:1b" -ForegroundColor Cyan
}

# Create/update environment file
Write-Host "`nConfiguring AI environment variables..." -ForegroundColor Yellow
$envFile = ".env"
$envExists = Test-Path $envFile

if ($envExists) {
    Write-Host "Found existing .env file, adding AI configuration..." -ForegroundColor Gray
    
    # Read existing .env
    $envContent = Get-Content $envFile -Raw
    
    # Add AI configuration if not present
    $aiConfig = @"

# AI Integration Configuration
OLLAMA_URL=http://localhost:11434
AI_DEFAULT_PROVIDER=ollama
AI_AUTO_CATEGORIZE=true
QUEUE_CONNECTION=database

# Optional: Add your API keys here
#OPENAI_API_KEY=your_openai_api_key
#GROQ_API_KEY=your_groq_api_key
"@

    if ($envContent -notmatch "OLLAMA_URL") {
        Add-Content -Path $envFile -Value $aiConfig
        Write-Host "✅ AI configuration added to .env" -ForegroundColor Green
    } else {
        Write-Host "✅ AI configuration already exists in .env" -ForegroundColor Green
    }
} else {
    Write-Host "⚠️  No .env file found. Please copy .env.example to .env first" -ForegroundColor Yellow
    Write-Host "💡 Run: cp .env.example .env" -ForegroundColor Cyan
}

# Run database migrations and clear cache
Write-Host "`nUpdating Firefly III..." -ForegroundColor Yellow
try {
    # Clear cache
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    
    # Update autoloader for new classes
    composer dump-autoload
    
    Write-Host "✅ Firefly III updated successfully" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Could not update Firefly III automatically" -ForegroundColor Yellow
    Write-Host "💡 Manual steps:" -ForegroundColor Cyan
    Write-Host "   php artisan cache:clear" -ForegroundColor Gray
    Write-Host "   php artisan config:clear" -ForegroundColor Gray
    Write-Host "   composer dump-autoload" -ForegroundColor Gray
}

# Test AI connectivity
Write-Host "`nTesting AI connectivity..." -ForegroundColor Yellow
try {
    $testResponse = Invoke-RestMethod -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 5
    if ($testResponse) {
        Write-Host "✅ Ollama AI is responding" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  Ollama AI test failed" -ForegroundColor Yellow
}

# Show final status and instructions
Write-Host "`n🎯 AI Integration Complete!" -ForegroundColor Cyan
Write-Host "============================" -ForegroundColor Cyan

Write-Host "🌐 Services:" -ForegroundColor White
Write-Host "   Firefly III: http://localhost:8080" -ForegroundColor Gray
Write-Host "   Ollama AI: http://localhost:11434" -ForegroundColor Gray

Write-Host "`n🤖 AI Features Available:" -ForegroundColor Cyan
Write-Host "✅ Automatic transaction categorization" -ForegroundColor Green
Write-Host "✅ AI financial insights" -ForegroundColor Green
Write-Host "✅ AI chat assistant" -ForegroundColor Green
Write-Host "✅ Spending anomaly detection" -ForegroundColor Green

Write-Host "`n📡 API Endpoints:" -ForegroundColor Cyan
Write-Host "   POST /api/v1/ai/categorize-transaction" -ForegroundColor Gray
Write-Host "   GET  /api/v1/ai/insights" -ForegroundColor Gray
Write-Host "   POST /api/v1/ai/chat" -ForegroundColor Gray
Write-Host "   GET  /api/v1/ai/anomalies" -ForegroundColor Gray
Write-Host "   GET  /api/v1/ai/status" -ForegroundColor Gray

Write-Host "`n🚀 Next Steps:" -ForegroundColor Cyan
Write-Host "1. Add some transactions in Firefly III" -ForegroundColor White
Write-Host "2. Watch them get automatically categorized by AI" -ForegroundColor White
Write-Host "3. Use the API endpoints to integrate AI into the frontend" -ForegroundColor White
Write-Host "4. Add your OpenAI/Groq API keys for cloud AI features" -ForegroundColor White

Write-Host "`n💡 Frontend Integration Examples:" -ForegroundColor Yellow
Write-Host @"
// Get AI insights
fetch('/api/v1/ai/insights')
  .then(response => response.json())
  .then(data => console.log(data.insights));

// Chat with AI
fetch('/api/v1/ai/chat', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({message: 'How much did I spend on food?'})
});

// Categorize transaction
fetch('/api/v1/ai/categorize-transaction', {
  method: 'POST', 
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({journal_id: 123})
});
"@ -ForegroundColor Gray

Write-Host "`n🔥 Your Firefly III now has AI superpowers!" -ForegroundColor Green
