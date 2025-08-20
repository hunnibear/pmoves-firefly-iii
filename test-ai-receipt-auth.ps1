# AI Receipt Processing Test with Personal Access Token
# Tests the enhanced CouplesController API endpoints with proper authentication

param(
    [Parameter(Mandatory=$false)]
    [string]$PersonalAccessToken = $env:FIREFLY_PERSONAL_ACCESS_TOKEN
)

Write-Host "🤖 AI Receipt Processing API Test with Authentication" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan

# Check if personal access token is provided
if (-not $PersonalAccessToken) {
    Write-Host "❌ Personal Access Token required!" -ForegroundColor Red
    Write-Host "Please provide it in one of these ways:" -ForegroundColor Yellow
    Write-Host "   1. Parameter: .\test-ai-receipt-auth.ps1 -PersonalAccessToken 'your_token_here'" -ForegroundColor White
    Write-Host "   2. Environment: `$env:FIREFLY_PERSONAL_ACCESS_TOKEN = 'your_token_here'" -ForegroundColor White
    Write-Host "   3. Get token from: http://localhost:8080/profile/api-keys" -ForegroundColor White
    exit 1
}

# Configuration
$baseUrl = "http://localhost:8080"
$apiBaseUrl = "$baseUrl/api/v1"
$headers = @{
    "Authorization" = "Bearer $PersonalAccessToken"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

Write-Host "🔧 Testing API authentication..." -ForegroundColor Yellow

# Test basic API authentication first
try {
    $authTestResponse = Invoke-RestMethod -Uri "$apiBaseUrl/about" -Method GET -Headers $headers -TimeoutSec 10
    Write-Host "✅ API Authentication successful!" -ForegroundColor Green
    Write-Host "   Version: $($authTestResponse.data.version)" -ForegroundColor White
    Write-Host "   User: $($authTestResponse.data.user)" -ForegroundColor White
} catch {
    Write-Host "❌ API Authentication failed!" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   Please check your Personal Access Token" -ForegroundColor Yellow
    exit 1
}

# Check if services are running
Write-Host "`n🔧 Checking AI services..." -ForegroundColor Yellow

try {
    $ollamaResponse = Invoke-RestMethod -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 5
    $gemmaModels = $ollamaResponse.models | Where-Object { $_.name -like "*gemma*" }
    if ($gemmaModels.Count -gt 0) {
        Write-Host "✅ Ollama with Gemma models available" -ForegroundColor Green
        $gemmaModels | ForEach-Object { Write-Host "   - Model: $($_.name)" -ForegroundColor White }
    } else {
        Write-Host "⚠️  Ollama running but no Gemma models found" -ForegroundColor Yellow
        Write-Host "   Run: ollama pull gemma2:4b" -ForegroundColor Cyan
    }
} catch {
    Write-Host "❌ Ollama not running. Please start with: ollama serve" -ForegroundColor Red
}

# Test couples API state endpoint first
Write-Host "`n📊 Testing couples API state endpoint..." -ForegroundColor Yellow

try {
    $stateResponse = Invoke-RestMethod -Uri "$apiBaseUrl/couples/state" -Method GET -Headers $headers -TimeoutSec 10
    Write-Host "✅ Couples API state endpoint working!" -ForegroundColor Green
    Write-Host "   Total Balance: `$$($stateResponse.totalBalance)" -ForegroundColor White
    Write-Host "   Recent Transactions: $($stateResponse.recentTransactions.Count)" -ForegroundColor White
} catch {
    Write-Host "❌ Couples API state test failed:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
}

# Test the receipt upload endpoint with proper authentication
Write-Host "`n📸 Testing AI receipt upload with authentication..." -ForegroundColor Yellow

# Create test receipt content
$testReceiptContent = @"
TRADER JOE'S #456
456 Healthy Street
Portland, OR 97201

Date: $(Get-Date -Format 'MM/dd/yyyy')
Time: $(Get-Date -Format 'HH:mm')

PRODUCE:
Organic Bananas 3 lbs         `$4.99
Organic Avocados 6 each       `$11.94
Baby Spinach Bag              `$3.99

FROZEN:
Frozen Berries Mix            `$4.49
Cauliflower Rice              `$2.99

PANTRY:
Almond Butter                 `$7.99
Coconut Water 6-pack          `$5.99

SUBTOTAL:                     `$42.38
TAX:                          `$3.39
TOTAL:                        `$45.77

Payment: Debit Card
Member #: 123456789
Thank you for shopping!
"@

$tempReceiptFile = Join-Path $env:TEMP "test_receipt_auth_$(Get-Date -Format 'yyyyMMddHHmmss').txt"
Set-Content -Path $tempReceiptFile -Value $testReceiptContent -Encoding UTF8

try {
    # Create multipart form data for file upload with proper API headers
    $boundary = [System.Guid]::NewGuid().ToString()
    $fileBytes = [System.IO.File]::ReadAllBytes($tempReceiptFile)
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
        "--$boundary",
        'Content-Disposition: form-data; name="account_id"',
        '',
        '1',
        "--$boundary--"
    )
    
    $body = $bodyLines -join "`r`n"

    $uploadHeaders = @{
        "Authorization" = "Bearer $PersonalAccessToken"
        "Content-Type" = "multipart/form-data; boundary=$boundary"
        "Accept" = "application/json"
    }

    Write-Host "📡 Sending authenticated receipt to API endpoint..." -ForegroundColor Cyan
    $response = Invoke-WebRequest -Uri "$apiBaseUrl/couples/upload-receipt" -Method POST -Body $body -Headers $uploadHeaders -TimeoutSec 60

    if ($response.StatusCode -eq 200) {
        $responseData = $response.Content | ConvertFrom-Json
        
        Write-Host "✅ Receipt processed successfully with authentication!" -ForegroundColor Green
        Write-Host "   Status: $($responseData.status)" -ForegroundColor White
        Write-Host "   Processing Time: $($responseData.processing_time)" -ForegroundColor White
        
        if ($responseData.extracted_data) {
            Write-Host "`n📊 Extracted Data:" -ForegroundColor Cyan
            Write-Host "   Merchant: $($responseData.extracted_data.merchant_name)" -ForegroundColor White
            Write-Host "   Amount: `$$($responseData.extracted_data.total_amount)" -ForegroundColor White
            Write-Host "   Date: $($responseData.extracted_data.date)" -ForegroundColor White
            
            if ($responseData.extracted_data.items) {
                Write-Host "   Items: $($responseData.extracted_data.items.Count)" -ForegroundColor White
            }
        }
        
        if ($responseData.ai_suggestions) {
            Write-Host "`n🧠 AI Suggestions:" -ForegroundColor Cyan
            Write-Host "   Category: $($responseData.ai_suggestions.category)" -ForegroundColor White
            Write-Host "   Partner Assignment: $($responseData.ai_suggestions.partner_assignment)" -ForegroundColor White
            Write-Host "   Confidence: $([math]::Round($responseData.ai_suggestions.confidence * 100, 1))%" -ForegroundColor White
            
            if ($responseData.ai_suggestions.categorization_reasoning) {
                Write-Host "   Reasoning: $($responseData.ai_suggestions.categorization_reasoning)" -ForegroundColor Gray
            }
        }
        
        if ($responseData.transaction_created) {
            Write-Host "`n💰 Transaction Created:" -ForegroundColor Green
            Write-Host "   Transaction ID: $($responseData.transaction_created.transaction_id)" -ForegroundColor White
            Write-Host "   Amount: `$$($responseData.transaction_created.amount)" -ForegroundColor White
            Write-Host "   Description: $($responseData.transaction_created.description)" -ForegroundColor White
            Write-Host "   Category: $($responseData.transaction_created.category)" -ForegroundColor White
            Write-Host "   Partner Assignment: $($responseData.transaction_created.partner_assignment)" -ForegroundColor White
            Write-Host "   AI Confidence: $([math]::Round($responseData.transaction_created.ai_confidence * 100, 1))%" -ForegroundColor White
            
            # Verify transaction in Firefly III using API
            Write-Host "`n🔍 Verifying transaction via API..." -ForegroundColor Yellow
            try {
                $verifyUrl = "$apiBaseUrl/transactions/$($responseData.transaction_created.transaction_group_id)"
                $verifyResponse = Invoke-RestMethod -Uri $verifyUrl -Method GET -Headers $headers -TimeoutSec 10
                
                if ($verifyResponse.data) {
                    Write-Host "✅ Transaction verified in Firefly III database!" -ForegroundColor Green
                    Write-Host "   API ID: $($verifyResponse.data.id)" -ForegroundColor White
                    Write-Host "   Type: $($verifyResponse.data.type)" -ForegroundColor White
                    Write-Host "   Transactions in Group: $($verifyResponse.data.attributes.transactions.Count)" -ForegroundColor White
                    
                    # Show AI-specific tags
                    $firstTransaction = $verifyResponse.data.attributes.transactions[0]
                    if ($firstTransaction.tags) {
                        $aiTags = $firstTransaction.tags | Where-Object { $_ -like "*ai*" -or $_ -like "*couples*" -or $_ -like "*receipt*" }
                        if ($aiTags) {
                            Write-Host "   AI Tags: $($aiTags -join ', ')" -ForegroundColor Cyan
                        }
                    }
                } else {
                    Write-Host "⚠️  Transaction created but verification response empty" -ForegroundColor Yellow
                }
            } catch {
                Write-Host "⚠️  Transaction verification failed: $($_.Exception.Message)" -ForegroundColor Yellow
            }
        }
        
        Write-Host "`n🎉 AI Receipt Processing with Authentication Test PASSED!" -ForegroundColor Green
        
        # Show next steps
        Write-Host "`n🚀 Next Steps:" -ForegroundColor Cyan
        Write-Host "   1. Check transaction in Firefly III: $baseUrl/transactions" -ForegroundColor White
        Write-Host "   2. View couples dashboard: $baseUrl/couples/dashboard" -ForegroundColor White
        Write-Host "   3. Review AI processing logs for accuracy" -ForegroundColor White
        
    } else {
        Write-Host "❌ Receipt processing failed with status: $($response.StatusCode)" -ForegroundColor Red
        Write-Host "Response: $($response.Content)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ Authenticated test failed with error:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "   HTTP Status: $statusCode" -ForegroundColor Red
        
        if ($statusCode -eq 401) {
            Write-Host "   This indicates authentication failure. Please check your Personal Access Token." -ForegroundColor Yellow
        } elseif ($statusCode -eq 404) {
            Write-Host "   This indicates the API endpoint was not found. Routes may need to be updated." -ForegroundColor Yellow
        } elseif ($statusCode -eq 422) {
            Write-Host "   This indicates validation errors. Check the request format." -ForegroundColor Yellow
        }
        
        try {
            $errorStream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorStream)
            $errorBody = $reader.ReadToEnd()
            if ($errorBody) {
                Write-Host "   Response body: $errorBody" -ForegroundColor Red
            }
        } catch {
            Write-Host "   Could not read error response body" -ForegroundColor Red
        }
    }
} finally {
    # Clean up temp file
    if (Test-Path $tempReceiptFile) {
        Remove-Item $tempReceiptFile -Force
    }
}

Write-Host "`n📋 Authentication Test Summary:" -ForegroundColor Cyan
Write-Host "   - Personal Access Token authentication working: $(if ($authTestResponse) { '✅ YES' } else { '❌ NO' })" -ForegroundColor White
Write-Host "   - Couples API endpoints accessible: $(if ($stateResponse) { '✅ YES' } else { '❌ NO' })" -ForegroundColor White
Write-Host "   - AI receipt processing functional: $(if ($responseData) { '✅ YES' } else { '❌ NO' })" -ForegroundColor White
Write-Host "   - Transaction creation working: $(if ($responseData.transaction_created) { '✅ YES' } else { '❌ NO' })" -ForegroundColor White

Write-Host "`n💡 Tips:" -ForegroundColor Cyan
Write-Host "   - Get Personal Access Token: $baseUrl/profile/api-keys" -ForegroundColor White
Write-Host "   - API Documentation: $baseUrl/api/docs" -ForegroundColor White
Write-Host "   - Monitor Ollama logs: ollama logs" -ForegroundColor White