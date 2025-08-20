# Quick Login and API Test Script
# Logs you into Firefly III and tests the AI receipt processing

Write-Host "🚀 Firefly III Quick Login and API Test" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

# Get the token from .env.local
$envPath = ".\.env.local"
if (Test-Path $envPath) {
    $envContent = Get-Content $envPath -Raw
    if ($envContent -match "FIREFLY_TOKEN=(.+)") {
        $existingToken = $matches[1].Trim()
        Write-Host "✅ Found Personal Access Token in .env.local" -ForegroundColor Green
        Write-Host "   Token: $($existingToken.Substring(0, 20))..." -ForegroundColor Gray
    } else {
        Write-Host "❌ No FIREFLY_TOKEN found in .env.local" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "❌ .env.local file not found" -ForegroundColor Red
    exit 1
}

# Test the existing token
Write-Host "`n🔑 Testing existing Personal Access Token..." -ForegroundColor Yellow

$headers = @{
    "Authorization" = "Bearer $existingToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

try {
    $aboutResponse = Invoke-RestMethod -Uri "http://localhost:8080/api/v1/about" -Method GET -Headers $headers -TimeoutSec 10
    Write-Host "✅ Token is valid!" -ForegroundColor Green
    Write-Host "   Version: $($aboutResponse.data.version)" -ForegroundColor White
    Write-Host "   API URL: $($aboutResponse.data.api_base_url)" -ForegroundColor White
} catch {
    if ($_.Exception.Response.StatusCode -eq 401) {
        Write-Host "❌ Token expired or invalid. Need to generate a new one." -ForegroundColor Red
        Write-Host "🔧 Opening Firefly III to login and generate new token..." -ForegroundColor Yellow
        
        # Open browser for manual login
        Start-Process "http://localhost:8080/login"
        
        Write-Host "`n📋 Steps to get a new Personal Access Token:" -ForegroundColor Cyan
        Write-Host "   1. Login to Firefly III (opening now...)" -ForegroundColor White
        Write-Host "   2. Go to Profile & Preferences" -ForegroundColor White
        Write-Host "   3. Click 'API' or 'OAuth' tab" -ForegroundColor White
        Write-Host "   4. Click 'Personal Access Tokens'" -ForegroundColor White
        Write-Host "   5. Create new token with name 'AI Testing'" -ForegroundColor White
        Write-Host "   6. Copy the token and update .env.local" -ForegroundColor White
        
        Write-Host "`n⏳ Waiting 30 seconds for you to login..." -ForegroundColor Yellow
        Start-Sleep -Seconds 30
        
        # Try to open the direct API tokens page
        Start-Process "http://localhost:8080/profile/api-keys"
        
        Write-Host "`n🔄 Once you have a new token, update .env.local and run this script again." -ForegroundColor Cyan
        exit 1
    } else {
        Write-Host "❌ API test failed: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Test couples API
Write-Host "`n📊 Testing Couples API endpoint..." -ForegroundColor Yellow

try {
    $couplesResponse = Invoke-RestMethod -Uri "http://localhost:8080/api/v1/couples/state" -Method GET -Headers $headers -TimeoutSec 10
    Write-Host "✅ Couples API working!" -ForegroundColor Green
    Write-Host "   Total Balance: `$$($couplesResponse.totalBalance)" -ForegroundColor White
    Write-Host "   Recent Transactions: $($couplesResponse.recentTransactions.Count)" -ForegroundColor White
} catch {
    Write-Host "❌ Couples API test failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Now test the AI receipt upload
Write-Host "`n🤖 Testing AI Receipt Upload..." -ForegroundColor Yellow

# Create a simple test receipt
$receiptContent = @"
WHOLE FOODS MARKET
123 Main Street
Austin, TX 78701

Date: $(Get-Date -Format 'MM/dd/yyyy')
Time: $(Get-Date -Format 'HH:mm')

Organic Bananas           `$4.99
Avocados (4 ea)          `$7.96
Greek Yogurt             `$5.99
Sourdough Bread          `$3.50

SUBTOTAL:                `$22.44
TAX:                     `$1.80
TOTAL:                   `$24.24

Payment: Debit Card
Thank you!
"@

$tempFile = Join-Path $env:TEMP "test_receipt_$(Get-Date -Format 'yyyyMMddHHmmss').txt"
Set-Content -Path $tempFile -Value $receiptContent -Encoding UTF8

try {
    # Create multipart form data
    $boundary = [System.Guid]::NewGuid().ToString()
    $fileBytes = [System.IO.File]::ReadAllBytes($tempFile)
    $fileContent = [System.Text.Encoding]::GetEncoding("ISO-8859-1").GetString($fileBytes)
    
    $bodyLines = @(
        "--$boundary",
        'Content-Disposition: form-data; name="receipt"; filename="test_receipt.txt"',
        'Content-Type: text/plain',
        '',
        $fileContent,
        "--$boundary",
        'Content-Disposition: form-data; name="create_transaction"',
        '',
        'true',
        "--$boundary--"
    )
    
    $body = $bodyLines -join "`r`n"

    $uploadHeaders = @{
        "Authorization" = "Bearer $existingToken"
        "Content-Type" = "multipart/form-data; boundary=$boundary"
        "Accept" = "application/json"
    }

    Write-Host "📤 Uploading receipt for AI processing..." -ForegroundColor Cyan
    $uploadResponse = Invoke-WebRequest -Uri "http://localhost:8080/api/v1/couples/upload-receipt" -Method POST -Body $body -Headers $uploadHeaders -TimeoutSec 60

    if ($uploadResponse.StatusCode -eq 200) {
        $data = $uploadResponse.Content | ConvertFrom-Json
        
        Write-Host "🎉 AI Receipt Processing SUCCESS!" -ForegroundColor Green
        Write-Host "   Status: $($data.status)" -ForegroundColor White
        Write-Host "   Processing Time: $($data.processing_time)" -ForegroundColor White
        
        if ($data.extracted_data) {
            Write-Host "`n📊 Extracted Receipt Data:" -ForegroundColor Cyan
            Write-Host "   Merchant: $($data.extracted_data.merchant_name)" -ForegroundColor White
            Write-Host "   Total: `$$($data.extracted_data.total_amount)" -ForegroundColor White
            Write-Host "   Date: $($data.extracted_data.date)" -ForegroundColor White
            
            if ($data.extracted_data.items) {
                Write-Host "   Items Found: $($data.extracted_data.items.Count)" -ForegroundColor White
            }
        }
        
        if ($data.ai_suggestions) {
            Write-Host "`n🧠 AI Categorization:" -ForegroundColor Cyan
            Write-Host "   Category: $($data.ai_suggestions.category)" -ForegroundColor White
            Write-Host "   Partner Assignment: $($data.ai_suggestions.partner_assignment)" -ForegroundColor White
            Write-Host "   Confidence: $([math]::Round($data.ai_suggestions.confidence * 100, 1))%" -ForegroundColor White
        }
        
        if ($data.transaction_created) {
            Write-Host "`n💰 Transaction Created:" -ForegroundColor Green
            Write-Host "   Transaction ID: $($data.transaction_created.transaction_id)" -ForegroundColor White
            Write-Host "   Amount: `$$($data.transaction_created.amount)" -ForegroundColor White
            Write-Host "   Description: $($data.transaction_created.description)" -ForegroundColor White
            Write-Host "   Category: $($data.transaction_created.category)" -ForegroundColor White
            Write-Host "   Partner: $($data.transaction_created.partner_assignment)" -ForegroundColor White
        } else {
            Write-Host "`n⚠️  No transaction was created (may be disabled)" -ForegroundColor Yellow
        }
        
    } else {
        Write-Host "❌ Upload failed with status: $($uploadResponse.StatusCode)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ AI Receipt Upload failed:" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    
    # Check for common issues
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "   HTTP Status: $statusCode" -ForegroundColor Red
        
        switch ($statusCode) {
            401 { Write-Host "   Issue: Authentication failed - token may be expired" -ForegroundColor Yellow }
            404 { Write-Host "   Issue: API endpoint not found - routes may need updating" -ForegroundColor Yellow }
            422 { Write-Host "   Issue: Validation error - check request format" -ForegroundColor Yellow }
            500 { Write-Host "   Issue: Server error - check Firefly III logs" -ForegroundColor Yellow }
        }
    }
    
    # Check if AI services are running
    Write-Host "`n🔧 Checking AI services status..." -ForegroundColor Yellow
    
    try {
        $ollamaResponse = Invoke-RestMethod -Uri "http://localhost:11434/api/tags" -TimeoutSec 5
        $models = $ollamaResponse.models | Where-Object { $_.name -like "*gemma*" }
        if ($models.Count -gt 0) {
            Write-Host "   ✅ Ollama + Gemma models available" -ForegroundColor Green
        } else {
            Write-Host "   ⚠️  Ollama running but no Gemma models" -ForegroundColor Yellow
            Write-Host "   💡 Run: ollama pull gemma2:4b" -ForegroundColor Cyan
        }
    } catch {
        Write-Host "   ❌ Ollama not running - start with: ollama serve" -ForegroundColor Red
    }
} finally {
    if (Test-Path $tempFile) {
        Remove-Item $tempFile -Force
    }
}

Write-Host "`n🎯 Test Summary:" -ForegroundColor Cyan
Write-Host "   - Authentication: $(if ($aboutResponse) { '✅ WORKING' } else { '❌ FAILED' })" -ForegroundColor White
Write-Host "   - Couples API: $(if ($couplesResponse) { '✅ WORKING' } else { '❌ FAILED' })" -ForegroundColor White
Write-Host "   - AI Processing: $(if ($data.status -eq 'success') { '✅ WORKING' } else { '❌ FAILED' })" -ForegroundColor White
Write-Host "   - Transaction Creation: $(if ($data.transaction_created) { '✅ WORKING' } else { '⚠️  DISABLED' })" -ForegroundColor White

if ($data.status -eq 'success') {
    Write-Host "`n🎉 AI Receipt Processing is fully functional!" -ForegroundColor Green
    Write-Host "🌐 View your transactions: http://localhost:8080/transactions" -ForegroundColor Cyan
    Write-Host "🏠 Couples Dashboard: http://localhost:8080/couples/dashboard" -ForegroundColor Cyan
} else {
    Write-Host "`n💡 Next steps to fix issues:" -ForegroundColor Yellow
    Write-Host "   1. Ensure Ollama is running: ollama serve" -ForegroundColor White
    Write-Host "   2. Pull the model: ollama pull gemma2:4b" -ForegroundColor White
    Write-Host "   3. Check Firefly III logs for errors" -ForegroundColor White
    Write-Host "   4. Verify database connection" -ForegroundColor White
}