#!/usr/bin/env pwsh
# Test AI functionality

Write-Host "🧪 Testing AI Services" -ForegroundColor Cyan
Write-Host "=====================" -ForegroundColor Cyan

# Test Ollama API
Write-Host "`n🤖 Testing Local Ollama AI..." -ForegroundColor Yellow
try {
    $ollamaResponse = Invoke-RestMethod -Uri "http://localhost:11434/api/generate" -Method POST -Body @{
        model = "llama3.2:1b"
        prompt = "Categorize this transaction: 'Coffee at Starbucks $4.50'"
        stream = $false
    } | ConvertTo-Json -Depth 3
    
    $result = Invoke-RestMethod -Uri "http://localhost:11434/api/generate" -Method POST -Headers @{
        "Content-Type" = "application/json"
        "Access-Control-Allow-Origin" = "*"
    } -Body @{
        model = "llama3.2:1b"
        prompt = "Categorize this transaction: 'Coffee at Starbucks $4.50'. Reply with just the category name."
        stream = $false
    } | ConvertTo-Json -Depth 3

    if ($result) {
        Write-Host "✅ Ollama AI is working!" -ForegroundColor Green
        Write-Host "Response: $result" -ForegroundColor Gray
    }
} catch {
    Write-Host "❌ Ollama AI test failed: $_" -ForegroundColor Red
}

# Test Supabase connection
Write-Host "`n🗄️ Testing Supabase Database..." -ForegroundColor Yellow
try {
    $supabaseTest = Invoke-RestMethod -Uri "http://localhost:54321/rest/v1/" -Headers @{
        "apikey" = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZS1kZW1vIiwicm9sZSI6ImFub24iLCJleHAiOjE5ODM4MTI5OTZ9.CRXP1A7WOeoJeXxjNni43kdQwgnWNReilDMblYTn_I0"
    } -Method GET
    
    if ($supabaseTest) {
        Write-Host "✅ Supabase Database is working!" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Supabase test failed: $_" -ForegroundColor Red
}

Write-Host "`n🎯 All services tested!" -ForegroundColor Cyan
Write-Host "Your AI dashboard should now be fully functional." -ForegroundColor Green
Write-Host "`n💡 Next steps:" -ForegroundColor Yellow
Write-Host "   1. Open firefly-ai-dashboard.html in your browser" -ForegroundColor Gray
Write-Host "   2. Try adding a transaction to test AI categorization" -ForegroundColor Gray
Write-Host "   3. Chat with your financial data using the AI assistant" -ForegroundColor Gray
