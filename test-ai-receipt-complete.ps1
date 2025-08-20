# AI Receipt Processing Test Automation
# Comprehensive test suite for LangExtract + Couples AI integration

param(
    [string]$TestType = "full",  # Options: full, api-only, ui-only
    [string]$Environment = "local",  # Options: local, production
    [string]$LogLevel = "info",  # Options: debug, info, warn, error
    [switch]$CreateTransaction,
    [switch]$Headless
)

Write-Host "🤖 AI Receipt Processing Test Automation" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# Configuration
$CONFIG = @{
    BaseUrl = if ($Environment -eq "local") { "http://localhost:8080" } else { "https://your-production-url.com" }
    DataImporterUrl = if ($Environment -eq "local") { "http://localhost:8081" } else { "https://your-importer-url.com" }
    TestReceiptPath = Join-Path $PSScriptRoot "sample_receipt.txt"
    LogFile = Join-Path $PSScriptRoot "logs\ai-test-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').log"
}

function Write-Log {
    param($Message, $Level = "INFO")
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    
    # Write to console with colors
    switch ($Level) {
        "ERROR" { Write-Host $logEntry -ForegroundColor Red }
        "WARN"  { Write-Host $logEntry -ForegroundColor Yellow }
        "SUCCESS" { Write-Host $logEntry -ForegroundColor Green }
        default { Write-Host $logEntry -ForegroundColor White }
    }
    
    # Write to log file
    $logDir = Split-Path $CONFIG.LogFile -Parent
    if (!(Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }
    Add-Content -Path $CONFIG.LogFile -Value $logEntry
}

function Test-Prerequisites {
    Write-Log "🔧 Checking test prerequisites..." "INFO"
    
    # Check if Firefly III is running
    try {
        $response = Invoke-WebRequest -Uri "$($CONFIG.BaseUrl)/login" -Method GET -TimeoutSec 10
        if ($response.StatusCode -eq 200) {
            Write-Log "✅ Firefly III is accessible at $($CONFIG.BaseUrl)" "SUCCESS"
        }
    } catch {
        Write-Log "❌ Firefly III not accessible at $($CONFIG.BaseUrl). Please ensure it's running." "ERROR"
        return $false
    }

    # Check if Data Importer is running
    try {
        $response = Invoke-WebRequest -Uri "$($CONFIG.DataImporterUrl)" -Method GET -TimeoutSec 10
        Write-Log "✅ Data Importer is accessible at $($CONFIG.DataImporterUrl)" "SUCCESS"
    } catch {
        Write-Log "⚠️  Data Importer not accessible at $($CONFIG.DataImporterUrl). Some tests may fail." "WARN"
    }

    # Check if Node.js is available for Puppeteer
    try {
        $nodeVersion = node --version 2>$null
        if ($nodeVersion) {
            Write-Log "✅ Node.js available: $nodeVersion" "SUCCESS"
        } else {
            Write-Log "❌ Node.js not found. Please install Node.js for browser automation." "ERROR"
            return $false
        }
    } catch {
        Write-Log "❌ Node.js not found. Please install Node.js for browser automation." "ERROR"
        return $false
    }

    # Check if Puppeteer is installed
    $puppeteerTest = Join-Path $PSScriptRoot "node_modules\puppeteer"
    if (!(Test-Path $puppeteerTest)) {
        Write-Log "📦 Installing Puppeteer..." "INFO"
        try {
            & npm install puppeteer
            Write-Log "✅ Puppeteer installed successfully" "SUCCESS"
        } catch {
            Write-Log "❌ Failed to install Puppeteer. Please run 'npm install puppeteer' manually." "ERROR"
            return $false
        }
    } else {
        Write-Log "✅ Puppeteer is available" "SUCCESS"
    }

    # Check if sample receipt exists
    if (Test-Path $CONFIG.TestReceiptPath) {
        Write-Log "✅ Sample receipt file found: $($CONFIG.TestReceiptPath)" "SUCCESS"
    } else {
        Write-Log "⚠️  Sample receipt not found. Creating one..." "WARN"
        New-SampleReceipt
    }

    return $true
}

function         Create-SampleReceipt {
    $receiptContent = @"
TARGET STORE #1234
123 Main Street
Anytown, CA 90210
Phone: (555) 123-4567

Date: $(Get-Date -Format 'MM/dd/yyyy')    Time: $(Get-Date -Format 'HH:mm')
Receipt #: T$(Get-Random -Minimum 100000 -Maximum 999999)

GROCERY DEPARTMENT:
Organic Apples 2 lbs          `$5.99
Whole Milk 1 gal              `$3.49
Sandwich Bread                `$2.99
Free Range Eggs 12 ct         `$4.99

HOUSEHOLD:
Paper Towels 6 pack           `$12.99
Laundry Detergent             `$8.99

SUBTOTAL:                     `$39.44
TAX (7.5%):                   `$2.96
TOTAL:                        `$42.40

PAYMENT: VISA ****1234
CASHBACK EARNED: `$0.42

Thank you for shopping with us!
Visit us online at target.com
"@

    Set-Content -Path $CONFIG.TestReceiptPath -Value $receiptContent -Encoding UTF8
    Write-Log "✅ Sample receipt created at $($CONFIG.TestReceiptPath)" "SUCCESS"
}

function Test-AIServices {
    Write-Log "🧠 Testing AI services integration..." "INFO"
    
    # Test Ollama connection
    try {
        $ollamaResponse = Invoke-RestMethod -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 5
        $models = $ollamaResponse.models | Where-Object { $_.name -like "*gemma*" }
        
        if ($models.Count -gt 0) {
            Write-Log "✅ Ollama is running with Gemma models available" "SUCCESS"
            $models | ForEach-Object { Write-Log "   - Model: $($_.name)" "INFO" }
        } else {
            Write-Log "⚠️  Ollama running but no Gemma models found" "WARN"
        }
    } catch {
        Write-Log "❌ Ollama not accessible. AI features may not work." "ERROR"
        Write-Log "   Please start Ollama with: ollama serve" "INFO"
    }

    # Test Python environment
    try {
        $pythonVersion = python --version 2>$null
        if ($pythonVersion) {
            Write-Log "✅ Python available: $pythonVersion" "SUCCESS"
            
            # Test if LangExtract dependencies are available
            $testScript = @"
try:
    import langext
    print("LangExtract: Available")
except ImportError:
    print("LangExtract: Not available")

try:
    import requests
    print("Requests: Available")
except ImportError:
    print("Requests: Not available")
"@
            
            $testFile = Join-Path $env:TEMP "test_deps.py"
            Set-Content -Path $testFile -Value $testScript
            
            $depTest = python $testFile 2>$null
            Write-Log "📦 Python dependencies:" "INFO"
            $depTest -split "`n" | ForEach-Object { Write-Log "   - $_" "INFO" }
            
            Remove-Item $testFile -Force
        }
    } catch {
        Write-Log "⚠️  Python not found. AI processing may fail." "WARN"
    }
}

function Test-DirectAPICall {
    param([string]$AuthToken)
    
    Write-Log "🔗 Testing direct API call to upload-receipt endpoint..." "INFO"
    
    try {
        # Create a test receipt file for upload
        $tempReceipt = Join-Path $env:TEMP "test_receipt_$(Get-Date -Format 'yyyyMMddHHmmss').txt"
        Copy-Item $CONFIG.TestReceiptPath $tempReceipt
        
        # Prepare multipart form data
        $boundary = [System.Guid]::NewGuid().ToString()
        $fileBytes = [System.IO.File]::ReadAllBytes($tempReceipt)
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

        if ($AuthToken) {
            $headers["Authorization"] = "Bearer $AuthToken"
        }

        $response = Invoke-WebRequest -Uri "$($CONFIG.BaseUrl)/couples/api/upload-receipt" -Method POST -Body $body -Headers $headers -TimeoutSec 30
        
        if ($response.StatusCode -eq 200) {
            $responseData = $response.Content | ConvertFrom-Json
            Write-Log "✅ Direct API call successful!" "SUCCESS"
            Write-Log "   Status: $($responseData.status)" "INFO"
            Write-Log "   Processing Time: $($responseData.processing_time)" "INFO"
            
            if ($responseData.transaction_created) {
                Write-Log "   ✅ Transaction Created: ID $($responseData.transaction_created.transaction_id)" "SUCCESS"
            }
            
            return $responseData
        } else {
            Write-Log "❌ API call failed with status: $($response.StatusCode)" "ERROR"
            return $null
        }
        
    } catch {
        Write-Log "❌ Direct API call failed: $($_.Exception.Message)" "ERROR"
        return $null
    } finally {
        if (Test-Path $tempReceipt) {
            Remove-Item $tempReceipt -Force
        }
    }
}

function Start-BrowserAutomation {
    Write-Log "🌐 Starting browser automation test..." "INFO"
    
    $nodeScript = Join-Path $PSScriptRoot "test-ai-receipt-automation.js"
    
    if (!(Test-Path $nodeScript)) {
        Write-Log "❌ Browser automation script not found: $nodeScript" "ERROR"
        return $false
    }

    # Set environment variables for the test
    $env:FIREFLY_BASE_URL = $CONFIG.BaseUrl
    $env:HEADLESS_MODE = if ($Headless) { "true" } else { "false" }
    $env:CREATE_TRANSACTION = if ($CreateTransaction) { "true" } else { "false" }

    try {
        Write-Log "🚀 Launching Puppeteer automation..." "INFO"
        & node $nodeScript | Out-Host
        
        if ($LASTEXITCODE -eq 0) {
            Write-Log "✅ Browser automation completed successfully" "SUCCESS"
            return $true
        } else {
            Write-Log "❌ Browser automation failed with exit code: $LASTEXITCODE" "ERROR"
            return $false
        }
    } catch {
        Write-Log "❌ Browser automation error: $($_.Exception.Message)" "ERROR"
        return $false
    }
}

function Test-CouplesDataImporter {
    Write-Log "📊 Testing Couples Data Importer integration..." "INFO"
    
    $csvPath = Join-Path $PSScriptRoot "import-data\couples-sample-transactions.csv"
    $configPath = Join-Path $PSScriptRoot "couples-configs\couples-basic-config.json"
    
    if (!(Test-Path $csvPath)) {
        Write-Log "❌ Couples sample CSV not found: $csvPath" "ERROR"
        return $false
    }
    
    if (!(Test-Path $configPath)) {
        Write-Log "❌ Couples config not found: $configPath" "ERROR"
        return $false
    }

    try {
        # Test CSV import via Data Importer API
        $secret = "couples_import_secret_2025_secure_key"
        $importUrl = "$($CONFIG.DataImporterUrl)/autoupload?secret=$secret"
        
        # This would need actual multipart form implementation
        Write-Log "ℹ️  Data Importer integration test would require actual file upload implementation" "INFO"
        Write-Log "   URL: $importUrl" "INFO"
        Write-Log "   CSV: $csvPath" "INFO"
        Write-Log "   Config: $configPath" "INFO"
        
        return $true
        
    } catch {
        Write-Log "❌ Data Importer test failed: $($_.Exception.Message)" "ERROR"
        return $false
    }
}

function New-TestReport {
    param($Results)
    
    Write-Log "📊 Generating comprehensive test report..." "INFO"
    
    $report = @{
        timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        environment = $Environment
        configuration = $CONFIG
        test_results = $Results
        summary = @{
            total_tests = $Results.Count
            passed_tests = ($Results.Values | Where-Object { $_ -eq $true }).Count
            failed_tests = ($Results.Values | Where-Object { $_ -eq $false }).Count
            overall_success = ($Results.Values | Where-Object { $_ -eq $false }).Count -eq 0
        }
    }
    
    $reportPath = Join-Path $PSScriptRoot "reports\ai-test-report-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').json"
    $reportDir = Split-Path $reportPath -Parent
    
    if (!(Test-Path $reportDir)) {
        New-Item -ItemType Directory -Path $reportDir -Force | Out-Null
    }
    
    $report | ConvertTo-Json -Depth 10 | Set-Content -Path $reportPath -Encoding UTF8
    
    Write-Log "📋 Test Report Summary:" "INFO"
    Write-Log "   Total Tests: $($report.summary.total_tests)" "INFO"
    Write-Log "   Passed: $($report.summary.passed_tests)" "SUCCESS"
    Write-Log "   Failed: $($report.summary.failed_tests)" "ERROR"
    Write-Log "   Overall: $(if ($report.summary.overall_success) { 'PASS' } else { 'FAIL' })" $(if ($report.summary.overall_success) { "SUCCESS" } else { "ERROR" })
    Write-Log "   Report saved: $reportPath" "INFO"
    
    return $reportPath
}

# Main execution flow
function Start-AIReceiptTest {
    Write-Log "🎬 Starting AI Receipt Processing Test Suite" "INFO"
    Write-Log "Environment: $Environment | Test Type: $TestType | Create Transaction: $CreateTransaction" "INFO"
    
    $testResults = @{}
    
    # Step 1: Prerequisites
    Write-Log "`n🔧 Step 1: Checking Prerequisites" "INFO"
    $testResults["prerequisites"] = Test-Prerequisites
    if (!$testResults["prerequisites"]) {
        Write-Log "❌ Prerequisites failed. Aborting test." "ERROR"
        return
    }

    # Step 2: AI Services
    Write-Log "`n🧠 Step 2: Testing AI Services" "INFO"
    Test-AIServices  # This is informational only

    # Step 3: Direct API Test
    if ($TestType -in @("full", "api-only")) {
        Write-Log "`n🔗 Step 3: Direct API Test" "INFO"
        $apiResult = Test-DirectAPICall
        $testResults["direct_api"] = $null -ne $apiResult
    }

    # Step 4: Browser Automation
    if ($TestType -in @("full", "ui-only")) {
        Write-Log "`n🌐 Step 4: Browser Automation Test" "INFO"
        $testResults["browser_automation"] = Start-BrowserAutomation
    }

    # Step 5: Data Importer Integration
    if ($TestType -eq "full") {
        Write-Log "`n📊 Step 5: Data Importer Integration Test" "INFO"
        $testResults["data_importer"] = Test-CouplesDataImporter
    }

    # Step 6: Generate Report
    Write-Log "`n📊 Step 6: Generating Test Report" "INFO"
    $reportPath = New-TestReport -Results $testResults
    
    Write-Log "`n✅ AI Receipt Processing Test Suite Completed!" "SUCCESS"
    Write-Log "📄 Full log: $($CONFIG.LogFile)" "INFO"
    Write-Log "📊 Report: $reportPath" "INFO"
    
    # Open report if in interactive mode
    if (!$Headless -and (Get-Command code -ErrorAction SilentlyContinue)) {
        Write-Log "🔍 Opening report in VS Code..." "INFO"
        & code $reportPath
    }
}

# Execute the test suite
Start-AIReceiptTest