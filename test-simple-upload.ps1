# Simple upload test to debug 415 error
param(
    [string]$Token = $null
)

# Load environment if no token provided
if (-not $Token) {
    if (Test-Path ".env.local") {
        Get-Content ".env.local" | ForEach-Object {
            if ($_ -match "^([^=]+)=(.*)$") {
                [Environment]::SetEnvironmentVariable($matches[1], $matches[2])
            }
        }
        $Token = $env:FIREFLY_III_ACCESS_TOKEN
    }
}

if (-not $Token) {
    Write-Host "❌ No token found! Please provide token or ensure .env.local exists" -ForegroundColor Red
    exit 1
}

Write-Host "🔍 Testing simple file upload..." -ForegroundColor Yellow

# Create minimal test file
$testContent = "Test receipt content"
$tempFile = Join-Path $env:TEMP "minimal_test.txt"
Set-Content -Path $tempFile -Value $testContent -Encoding UTF8

try {
    # Test 1: Simple form-data without file
    Write-Host "`n📝 Test 1: Simple JSON POST..." -ForegroundColor Cyan
    
    $jsonHeaders = @{
        "Authorization" = "Bearer $Token"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $jsonBody = @{
        test = "value"
    } | ConvertTo-Json
    
    try {
        $response1 = Invoke-WebRequest -Uri "http://localhost:8080/api/v1/couples/upload-receipt" -Method POST -Body $jsonBody -Headers $jsonHeaders -TimeoutSec 30
        Write-Host "✅ JSON Response: $($response1.StatusCode)" -ForegroundColor Green
    } catch {
        Write-Host "❌ JSON Error: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            Write-Host "   Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
            Write-Host "   Content: $($_.Exception.Response | Get-Member)" -ForegroundColor Red
        }
    }
    
    # Test 2: PowerShell's built-in multipart
    Write-Host "`n📝 Test 2: PowerShell multipart..." -ForegroundColor Cyan
    
    $multipartHeaders = @{
        "Authorization" = "Bearer $Token"
        "Accept" = "application/json"
    }
    
    try {
        # Use PowerShell's InFile parameter for proper multipart
        $response2 = Invoke-RestMethod -Uri "http://localhost:8080/api/v1/couples/upload-receipt" -Method POST -InFile $tempFile -Headers $multipartHeaders -TimeoutSec 30
        Write-Host "✅ Multipart Response received" -ForegroundColor Green
        Write-Host "   Content: $($response2 | ConvertTo-Json -Depth 3)" -ForegroundColor White
    } catch {
        Write-Host "❌ Multipart Error: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            Write-Host "   Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
        }
    }
    
    # Test 3: Curl-style manual multipart
    Write-Host "`n📝 Test 3: Manual multipart with curl..." -ForegroundColor Cyan
    
    $curlArgs = @(
        "-X", "POST"
        "-H", "Authorization: Bearer $Token"
        "-H", "Accept: application/json"
        "-F", "receipt=@$tempFile"
        "-F", "create_transaction=true"
        "http://localhost:8080/api/v1/couples/upload-receipt"
    )
    
    try {
        $curlResult = & curl @curlArgs 2>&1
        Write-Host "✅ Curl result:" -ForegroundColor Green
        Write-Host "$curlResult" -ForegroundColor White
    } catch {
        Write-Host "❌ Curl Error: $($_.Exception.Message)" -ForegroundColor Red
    }

} finally {
    # Cleanup
    if (Test-Path $tempFile) {
        Remove-Item $tempFile -Force
    }
}

Write-Host "`n✅ Simple upload tests completed!" -ForegroundColor Green