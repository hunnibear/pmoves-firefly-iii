# Configuration Switcher for Firefly III
# Quickly switch between local and production setups

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("local", "production", "status")]
    [string]$Mode
)

Write-Host "🔧 Firefly III Configuration Switcher" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

function Switch-ToLocal {
    Write-Host "🏠 Switching to LOCAL development mode..." -ForegroundColor Yellow
    
    # Stop any running services
    Write-Host "🛑 Stopping existing services..." -ForegroundColor Blue
    docker-compose down 2>$null
    
    # Start local Supabase
    Write-Host "🗄️ Starting local Supabase..." -ForegroundColor Blue
    docker-compose -f docker-compose.supabase.yml --env-file .env.supabase up -d
    
    # Wait a bit for Supabase to start
    Start-Sleep -Seconds 15
    
    # Start Firefly III in local mode
    Write-Host "💰 Starting Firefly III (local mode)..." -ForegroundColor Blue
    docker-compose --profile local --env-file .env.local up -d
    
    Write-Host ""
    Write-Host "✅ LOCAL mode active!" -ForegroundColor Green
    Write-Host "   💰 Firefly III: http://localhost:8080" -ForegroundColor Cyan
    Write-Host "   📊 Supabase Studio: http://localhost:8000" -ForegroundColor Cyan
    Write-Host "   🗄️ Database: localhost:54322" -ForegroundColor Cyan
    Write-Host "   🤖 Ollama: http://localhost:11434" -ForegroundColor Cyan
}

function Switch-ToProduction {
    Write-Host "🌐 Switching to PRODUCTION mode..." -ForegroundColor Yellow
    
    # Stop any running services
    Write-Host "🛑 Stopping local services..." -ForegroundColor Blue
    docker-compose down 2>$null
    docker-compose -f docker-compose.supabase.yml down 2>$null
    
    # Start Firefly III in production mode
    Write-Host "💰 Starting Firefly III (production mode)..." -ForegroundColor Blue
    docker-compose --env-file .env.production up -d
    
    Write-Host ""
    Write-Host "✅ PRODUCTION mode active!" -ForegroundColor Green
    Write-Host "   💰 Firefly III: http://localhost:8080" -ForegroundColor Cyan
    Write-Host "   🗄️ Database: supabasepmoves.cataclysmstudios.net:5432" -ForegroundColor Cyan
    Write-Host "   📊 Supabase: https://supabasepmoves.cataclysmstudios.net" -ForegroundColor Cyan
}

function Show-Status {
    Write-Host "📊 Current Status:" -ForegroundColor Yellow
    Write-Host "=================" -ForegroundColor Yellow
    
    Write-Host ""
    Write-Host "🏠 LOCAL services (Supabase):" -ForegroundColor Blue
    docker-compose -f docker-compose.supabase.yml ps
    
    Write-Host ""
    Write-Host "💰 FIREFLY III services:" -ForegroundColor Blue
    docker-compose ps
    
    Write-Host ""
    Write-Host "🌐 Available endpoints:" -ForegroundColor Green
    
    # Check if local Supabase is running
    $supabaseRunning = docker-compose -f docker-compose.supabase.yml ps -q | Measure-Object | Select-Object -ExpandProperty Count
    if ($supabaseRunning -gt 0) {
        Write-Host "   📊 Supabase Studio: http://localhost:8000 (LOCAL)" -ForegroundColor Cyan
        Write-Host "   🗄️ Database: localhost:54322 (LOCAL)" -ForegroundColor Cyan
    }
    
    # Check if Firefly is running
    $fireflyRunning = docker-compose ps -q | Measure-Object | Select-Object -ExpandProperty Count
    if ($fireflyRunning -gt 0) {
        Write-Host "   💰 Firefly III: http://localhost:8080" -ForegroundColor Cyan
        
        # Check if Ollama is running (local mode)
        $ollamaRunning = docker-compose ps ollama -q 2>$null
        if ($ollamaRunning) {
            Write-Host "   🤖 Ollama: http://localhost:11434 (LOCAL)" -ForegroundColor Cyan
        }
    }
    
    Write-Host ""
    Write-Host "💡 To switch modes:" -ForegroundColor Yellow
    Write-Host "   .\switch-config.ps1 local       # Switch to local development" -ForegroundColor Cyan
    Write-Host "   .\switch-config.ps1 production  # Switch to production" -ForegroundColor Cyan
}

# Execute based on mode
switch ($Mode) {
    "local" {
        Switch-ToLocal
    }
    "production" {
        Switch-ToProduction
    }
    "status" {
        Show-Status
    }
}

Write-Host ""
Write-Host "🔧 Configuration switch complete!" -ForegroundColor Green
