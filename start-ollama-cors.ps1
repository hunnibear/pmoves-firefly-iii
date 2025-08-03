#!/usr/bin/env pwsh
# Start Ollama with CORS support for browser access

Write-Host "🤖 Starting Ollama with CORS Support" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

# Stop existing Ollama container if running
Write-Host "Stopping existing Ollama container..." -ForegroundColor Yellow
docker stop ollama 2>$null
docker rm ollama 2>$null

# Start Ollama with CORS enabled
Write-Host "Starting Ollama with CORS enabled..." -ForegroundColor Yellow
try {
    docker run -d -p 11434:11434 `
        -e OLLAMA_ORIGINS="*" `
        -e OLLAMA_HOST="0.0.0.0" `
        --name ollama `
        ollama/ollama
    
    Write-Host "✅ Ollama started with CORS support" -ForegroundColor Green
    
    # Wait for Ollama to be ready
    Write-Host "Waiting for Ollama to be ready..." -ForegroundColor Yellow
    Start-Sleep -Seconds 5
    
    # Test connectivity
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 10
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ Ollama is responding" -ForegroundColor Green
        }
    } catch {
        Write-Host "⚠️  Ollama may still be starting..." -ForegroundColor Yellow
    }
    
    # Install Llama model if not present
    Write-Host "Checking for Llama 3.2 model..." -ForegroundColor Yellow
    try {
        $models = docker exec ollama ollama list 2>$null
        if ($models -notlike "*llama3.2*") {
            Write-Host "Installing Llama 3.2 model (this may take several minutes)..." -ForegroundColor Yellow
            docker exec ollama ollama pull llama3.2:1b
            Write-Host "✅ Llama 3.2 model installed" -ForegroundColor Green
        } else {
            Write-Host "✅ Llama 3.2 model already available" -ForegroundColor Green
        }
    } catch {
        Write-Host "⚠️  Could not check/install model. You can install manually:" -ForegroundColor Yellow
        Write-Host "   docker exec ollama ollama pull llama3.2:1b" -ForegroundColor Gray
    }
    
} catch {
    Write-Host "❌ Error starting Ollama: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`n🎯 Ollama Status:" -ForegroundColor Cyan
Write-Host "=================" -ForegroundColor Cyan
Write-Host "🌐 API URL: http://localhost:11434" -ForegroundColor White
Write-Host "🔓 CORS: Enabled (allows browser access)" -ForegroundColor Green
Write-Host "🤖 Model: Llama 3.2 (lightweight)" -ForegroundColor White

Write-Host "`n💡 Test Ollama in browser console:" -ForegroundColor Cyan
Write-Host "fetch('http://localhost:11434/api/tags').then(r => r.json()).then(console.log)" -ForegroundColor Gray

Write-Host "`n🔥 Ollama is ready for AI-powered financial analysis!" -ForegroundColor Green
Write-Host "Now refresh your dashboard to use local AI features." -ForegroundColor Yellow
