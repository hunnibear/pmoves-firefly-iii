# Production Docker Startup Script for Firefly III with AI
# This script sets up and starts the complete AI-enabled environment

Write-Host "=== Firefly III AI Production Startup ===" -ForegroundColor Green

# Check for NVIDIA runtime
try {
    $gpu = nvidia-smi --query-gpu=name,memory.total --format=csv,noheader 2>$null
    if ($gpu) {
        Write-Host "✓ NVIDIA GPU detected:" -ForegroundColor Green
        Write-Host $gpu
    }
} catch {
    Write-Host "⚠ Warning: No NVIDIA GPU detected. AI processing will use CPU only." -ForegroundColor Yellow
}

# Check Docker Compose
try {
    docker-compose --version | Out-Null
    Write-Host "✓ Docker Compose found" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: docker-compose not found. Please install Docker Compose." -ForegroundColor Red
    exit 1
}

# Create necessary directories
$directories = @(
    "storage\logs",
    "storage\ai-temp", 
    "storage\langextract",
    "storage\obsidian",
    "storage\output"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "✓ Created directory: $dir" -ForegroundColor Green
    }
}

Write-Host "✓ Directories created" -ForegroundColor Green

# Start the environment
Write-Host "Starting Firefly III AI environment..." -ForegroundColor Blue
docker-compose -f docker-compose.ai.yml up -d

Write-Host "✓ Services starting..." -ForegroundColor Green

# Wait for core services
Write-Host "Waiting for database to be ready..." -ForegroundColor Blue
Start-Sleep -Seconds 10

# Check service health
Write-Host "Checking service status..." -ForegroundColor Blue
docker-compose -f docker-compose.ai.yml ps

Write-Host ""
Write-Host "=== Startup Complete ===" -ForegroundColor Green
Write-Host "Access Firefly III at: http://localhost:8080" -ForegroundColor Cyan
Write-Host "Ollama API available at: http://localhost:11434" -ForegroundColor Cyan
Write-Host ""
Write-Host "To check logs:" -ForegroundColor Yellow
Write-Host "  docker-compose -f docker-compose.ai.yml logs -f" -ForegroundColor White
Write-Host ""
Write-Host "To stop all services:" -ForegroundColor Yellow
Write-Host "  docker-compose -f docker-compose.ai.yml down" -ForegroundColor White
Write-Host ""
Write-Host "To test AI functionality:" -ForegroundColor Yellow
Write-Host "  Invoke-RestMethod -Uri 'http://localhost:8080/api/couples/upload-receipt' -Method POST -InFile 'test.jpg' -Headers @{'Authorization'='Bearer YOUR_TOKEN'}" -ForegroundColor White