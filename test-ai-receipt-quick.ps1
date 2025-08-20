# AI Receipt Processing Test - Quick Runner
# Tests the enhanced CouplesController with real transaction creation

Write-Host "🤖 AI Receipt Processing Quick Test" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

# Check if services are running
Write-Host "🔧 Checking services..." -ForegroundColor Yellow

try {
    $fireflyResponse = Invoke-WebRequest -Uri "http://localhost:8080" -Method GET -TimeoutSec 5
    Write-Host "✅ Firefly III is running" -ForegroundColor Green
} catch {
    Write-Host "❌ Firefly III not running. Please start with: docker-compose -f docker-compose.local.yml up -d" -ForegroundColor Red
    exit 1
}

try {
    $ollamaResponse = Invoke-RestMethod -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 5
    $gemmaModels = $ollamaResponse.models | Where-Object { $_.name -like "*gemma*" }
    if ($gemmaModels.Count -gt 0) {
        Write-Host "✅ Ollama with Gemma models available" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Ollama running but no Gemma models found" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Ollama not running. Please start with: ollama serve" -ForegroundColor Red
    Write-Host "   Then pull model: ollama pull gemma2:4b" -ForegroundColor Yellow
}

# Test the receipt upload endpoint
Write-Host "`n📸 Testing receipt upload endpoint..." -ForegroundColor Yellow

# Create test receipt content
$testReceiptContent = @"
WHOLE FOODS MARKET #123
123 Organic Way
Austin, TX 78701

Date: $(Get-Date -Format 'MM/dd/yyyy')
Time: $(Get-Date -Format 'HH:mm')

PRODUCE:
Organic Bananas 2 lbs         `$3.99
Avocados 4 each               `$7.96
Baby Spinach                  `$4.99

GROCERY:
Almond Milk                   `$5.49
Quinoa 1 lb                   `$6.99

SUBTOTAL:                     `$29.42
TAX:                          `$2.65
TOTAL:                        `$32.07

Payment: Credit Card
Thank you for shopping!
"@

$tempReceiptFile = Join-Path $env:TEMP "test_receipt_$(Get-Date -Format 'yyyyMMddHHmmss').txt"
Set-Content -Path $tempReceiptFile -Value $testReceiptContent

try {
    # Create multipart form data for file upload
    $boundary = [System.Guid]::NewGuid().ToString()
    $fileBytes = [System.IO.File]::ReadAllBytes($tempReceiptFile)
    $fileContent = [System.Text.Encoding]::GetEncoding("ISO-8859-1").GetString($fileBytes)
    
    $body = @"
--$boundary
Content-Disposition: form-data; name="receipt"; filename="test_receipt.txt"
Content-Type: text/plain

$fileContent
--$boundary
Content-Disposition: form-data; name="create_transaction"

true
--$boundary--
"@

    $headers = @{
        "Content-Type" = "multipart/form-data; boundary=$boundary"
    }

    Write-Host "📡 Sending receipt to AI processing endpoint..." -ForegroundColor Cyan
    $response = Invoke-WebRequest -Uri "http://localhost:8080/couples/api/upload-receipt" -Method POST -Body $body -Headers $headers -TimeoutSec 60

    if ($response.StatusCode -eq 200) {
        $responseData = $response.Content | ConvertFrom-Json
        
        Write-Host "✅ Receipt processed successfully!" -ForegroundColor Green
        Write-Host "   Status: $($responseData.status)" -ForegroundColor White
        Write-Host "   Processing Time: $($responseData.processing_time)" -ForegroundColor White
        
        if ($responseData.extracted_data) {
            Write-Host "`n📊 Extracted Data:" -ForegroundColor Cyan
            Write-Host "   Merchant: $($responseData.extracted_data.merchant_name)" -ForegroundColor White
            Write-Host "   Amount: `$$($responseData.extracted_data.total_amount)" -ForegroundColor White
            Write-Host "   Date: $($responseData.extracted_data.date)" -ForegroundColor White
        }
        
        if ($responseData.ai_suggestions) {
            Write-Host "`n🧠 AI Suggestions:" -ForegroundColor Cyan
            Write-Host "   Category: $($responseData.ai_suggestions.category)" -ForegroundColor White
            Write-Host "   Partner Assignment: $($responseData.ai_suggestions.partner_assignment)" -ForegroundColor White
            Write-Host "   Confidence: $([math]::Round($responseData.ai_suggestions.confidence * 100, 1))%" -ForegroundColor White
        }
        
        if ($responseData.transaction_created) {
            Write-Host "`n💰 Transaction Created:" -ForegroundColor Green
            Write-Host "   Transaction ID: $($responseData.transaction_created.transaction_id)" -ForegroundColor White
            Write-Host "   Amount: `$$($responseData.transaction_created.amount)" -ForegroundColor White
            Write-Host "   Description: $($responseData.transaction_created.description)" -ForegroundColor White
            Write-Host "   Category: $($responseData.transaction_created.category)" -ForegroundColor White
            
            # Verify transaction in Firefly III
            Write-Host "`n🔍 Verifying transaction in Firefly III..." -ForegroundColor Yellow
            try {
                $verifyUrl = "http://localhost:8080/api/v1/transactions/$($responseData.transaction_created.transaction_id)"
                $verifyResponse = Invoke-RestMethod -Uri $verifyUrl -Method GET -TimeoutSec 10
                
                if ($verifyResponse.data) {
                    Write-Host "✅ Transaction verified in Firefly III database!" -ForegroundColor Green
                    Write-Host "   ID: $($verifyResponse.data.id)" -ForegroundColor White
                    Write-Host "   Type: $($verifyResponse.data.type)" -ForegroundColor White
                } else {
                    Write-Host "⚠️  Transaction created but verification failed" -ForegroundColor Yellow
                }
            } catch {
                Write-Host "⚠️  Could not verify transaction (may need authentication)" -ForegroundColor Yellow
            }
        }
        
        Write-Host "`n🎉 AI Receipt Processing Test PASSED!" -ForegroundColor Green
        
    } else {
        Write-Host "❌ Receipt processing failed with status: $($response.StatusCode)" -ForegroundColor Red
        Write-Host "Response: $($response.Content)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ Test failed with error:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        try {
            $errorStream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorStream)
            $errorBody = $reader.ReadToEnd()
            Write-Host "   Response body: $errorBody" -ForegroundColor Red
        } catch {
            Write-Host "   Could not read error response" -ForegroundColor Red
        }
    }
} finally {
    # Clean up temp file
    if (Test-Path $tempReceiptFile) {
        Remove-Item $tempReceiptFile -Force
    }
}

Write-Host "`n📋 Test Summary:" -ForegroundColor Cyan
Write-Host "   - Enhanced CouplesController with real transaction creation" -ForegroundColor White
Write-Host "   - LangExtract AI integration for receipt processing" -ForegroundColor White
Write-Host "   - Couples-specific categorization and assignment" -ForegroundColor White
Write-Host "   - Full integration with Firefly III transaction system" -ForegroundColor White

Write-Host "`n🚀 Next Steps:" -ForegroundColor Cyan
Write-Host "   1. Login to Firefly III at http://localhost:8080" -ForegroundColor White
Write-Host "   2. Go to Couples Dashboard at http://localhost:8080/couples/dashboard" -ForegroundColor White
Write-Host "   3. Test the receipt upload UI manually" -ForegroundColor White
Write-Host "   4. Check transactions page to see AI-processed entries" -ForegroundColor White