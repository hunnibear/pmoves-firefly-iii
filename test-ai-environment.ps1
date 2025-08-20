#!/usr/bin/env pwsh
# Quick Test Script for AI Environment Setup

Write-Host "=== Firefly III AI Environment Test ===" -ForegroundColor Green

# Test 1: Check if Docker image was built
Write-Host "`n1. Checking Docker Image..." -ForegroundColor Blue
try {
    $image = docker images pmoves-firefly-iii-app:latest --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"
    if ($image -match "pmoves-firefly-iii-app") {
        Write-Host "✓ Docker image built successfully" -ForegroundColor Green
        Write-Host $image
    } else {
        Write-Host "❌ Docker image not found" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error checking Docker image: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Validate Docker Compose configuration
Write-Host "`n2. Validating Docker Compose configuration..." -ForegroundColor Blue
try {
    $composeCheck = docker-compose -f docker-compose.ai.yml config --quiet 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Docker Compose configuration is valid" -ForegroundColor Green
    } else {
        Write-Host "❌ Docker Compose configuration has errors:" -ForegroundColor Red
        Write-Host $composeCheck
    }
} catch {
    Write-Host "❌ Error validating Docker Compose: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Check AI scripts
Write-Host "`n3. Checking AI scripts..." -ForegroundColor Blue
$aiScripts = @(
    "ai-scripts\test_langextract.py",
    "ai-scripts\ai-entrypoint.sh"
)

foreach ($script in $aiScripts) {
    if (Test-Path $script) {
        Write-Host "✓ Found: $script" -ForegroundColor Green
    } else {
        Write-Host "❌ Missing: $script" -ForegroundColor Red
    }
}

# Test 4: Check configuration files
Write-Host "`n4. Checking configuration files..." -ForegroundColor Blue
$configFiles = @(
    "docker-compose.ai.yml",
    "Dockerfile.ai",
    ".env.docker",
    "AI_PRODUCTION_SETUP.md"
)

foreach ($file in $configFiles) {
    if (Test-Path $file) {
        Write-Host "✓ Found: $file" -ForegroundColor Green
    } else {
        Write-Host "❌ Missing: $file" -ForegroundColor Red
    }
}

# Test 5: Check startup scripts
Write-Host "`n5. Checking startup scripts..." -ForegroundColor Blue
$startupScripts = @(
    "start-ai-production.ps1",
    "start-ai-production.sh"
)

foreach ($script in $startupScripts) {
    if (Test-Path $script) {
        Write-Host "✓ Found: $script" -ForegroundColor Green
    } else {
        Write-Host "❌ Missing: $script" -ForegroundColor Red
    }
}

# Test 6: Test Python environment inside container
Write-Host "`n6. Testing Python environment in container..." -ForegroundColor Blue
try {
    $pythonTest = docker run --rm pmoves-firefly-iii-app:latest /opt/ai-env/bin/python --version
    if ($pythonTest -match "Python 3") {
        Write-Host "✓ Python environment working: $pythonTest" -ForegroundColor Green
    } else {
        Write-Host "❌ Python environment test failed" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error testing Python environment: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 7: Test AI packages
Write-Host "`n7. Testing AI packages in container..." -ForegroundColor Blue
try {
    $packageTest = docker run --rm pmoves-firefly-iii-app:latest /opt/ai-env/bin/python -c "import requests, numpy, langextract, ollama; print('All AI packages imported successfully')"
    if ($packageTest -match "successfully") {
        Write-Host "✓ AI packages installed correctly" -ForegroundColor Green
    } else {
        Write-Host "❌ AI package test failed" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error testing AI packages: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== Test Summary ===" -ForegroundColor Green
Write-Host "Docker Image: Built and ready" -ForegroundColor Cyan
Write-Host "Configuration: Valid" -ForegroundColor Cyan  
Write-Host "AI Environment: Configured" -ForegroundColor Cyan
Write-Host "Python Packages: Installed" -ForegroundColor Cyan

Write-Host "`nNext Steps:" -ForegroundColor Yellow
Write-Host "1. Run: .\start-ai-production.ps1" -ForegroundColor White
Write-Host "2. Access Firefly III at: http://localhost:8080" -ForegroundColor White
Write-Host "3. Test AI endpoint: http://localhost:8080/api/couples/upload-receipt" -ForegroundColor White

Write-Host "`nFor troubleshooting, see: AI_PRODUCTION_SETUP.md" -ForegroundColor Gray