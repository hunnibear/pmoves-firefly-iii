# Firefly III Local Development Setup Script for Windows PowerShell
# This script helps you get started with local development using Docker

param(
    [switch]$SkipOllama = $false,
    [switch]$Help = $false
)

# Colors for output
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Blue = "Cyan"

# Function to print colored output
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor $Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor $Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor $Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor $Red
}

# Show help
if ($Help) {
    Write-Host "Firefly III Local Development Setup Script for Windows"
    Write-Host ""
    Write-Host "Usage: .\setup-local.ps1 [OPTIONS]"
    Write-Host ""
    Write-Host "Options:"
    Write-Host "  -SkipOllama    Skip Ollama model installation"
    Write-Host "  -Help          Show this help message"
    Write-Host ""
    exit 0
}

Write-Host "🔥 Firefly III Local Development Setup (Windows)" -ForegroundColor Yellow
Write-Host "=================================================" -ForegroundColor Yellow

# Check if Docker is running
function Test-Docker {
    Write-Status "Checking Docker installation..."
    
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        Write-Error "Docker is not installed. Please install Docker Desktop first."
        Write-Host "Download from: https://www.docker.com/products/docker-desktop/"
        exit 1
    }
    
    try {
        docker info | Out-Null
        Write-Success "Docker is running"
    } catch {
        Write-Error "Docker is not running. Please start Docker Desktop first."
        exit 1
    }
}

# Check if docker-compose is available
function Test-DockerCompose {
    Write-Status "Checking Docker Compose installation..."
    
    if (Get-Command docker-compose -ErrorAction SilentlyContinue) {
        $script:DockerCompose = "docker-compose"
    } elseif (docker compose version 2>$null) {
        $script:DockerCompose = "docker compose"
    } else {
        Write-Error "Docker Compose is not available. Please install Docker Desktop with Compose."
        exit 1
    }
    Write-Success "Docker Compose is available: $script:DockerCompose"
}

# Setup environment file
function Set-Environment {
    Write-Status "Setting up environment file..."
    
    if (-not (Test-Path .env.local)) {
        if (Test-Path .env.local.example) {
            Copy-Item .env.local.example .env.local
            Write-Success "Created .env.local from example"
        } else {
            Write-Error ".env.local.example not found. Please create it first."
            exit 1
        }
    } else {
        Write-Warning ".env.local already exists. Skipping creation."
    }
    
    # Generate APP_KEY if needed
    $content = Get-Content .env.local -Raw
    if ($content -match "your-generated-32-char-key-here") {
        Write-Status "Generating APP_KEY..."
        $appKey = [System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes([System.Guid]::NewGuid().ToString().Replace("-", "").Substring(0, 32)))
        $content = $content -replace "your-generated-32-char-key-here", $appKey
        $content | Set-Content .env.local
        Write-Success "Generated APP_KEY"
    }
    
    # Generate STATIC_CRON_TOKEN if needed
    if ($content -match "your-32-character-cron-token-here") {
        Write-Status "Generating STATIC_CRON_TOKEN..."
        $cronToken = [System.Web.Security.Membership]::GeneratePassword(32, 0)
        $content = $content -replace "your-32-character-cron-token-here", $cronToken
        $content | Set-Content .env.local
        Write-Success "Generated STATIC_CRON_TOKEN"
    }
}

# Create necessary directories
function New-Directories {
    Write-Status "Creating necessary directories..."
    
    New-Item -ItemType Directory -Force -Path "ollama-data" | Out-Null
    New-Item -ItemType Directory -Force -Path "ssl" | Out-Null
    
    Write-Success "Directories created"
}

# Check Supabase network
function Test-SupabaseNetwork {
    Write-Status "Checking Supabase network..."
    
    $networks = docker network ls --format "{{.Name}}"
    if ($networks -notcontains "supabase_network") {
        Write-Warning "Supabase network not found. Creating external network..."
        docker network create supabase_network
        Write-Success "Created supabase_network"
    } else {
        Write-Success "Supabase network exists"
    }
}

# Pull required images
function Get-Images {
    Write-Status "Pulling required Docker images..."
    
    & $script:DockerCompose -f docker-compose.local.yml pull
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Images pulled successfully"
    } else {
        Write-Error "Failed to pull images"
        exit 1
    }
}

# Start services
function Start-Services {
    Write-Status "Starting Firefly III services..."
    
    & $script:DockerCompose -f docker-compose.local.yml up -d
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Services started successfully"
    } else {
        Write-Error "Failed to start services"
        exit 1
    }
}

# Wait for services to be ready
function Wait-ForServices {
    Write-Status "Waiting for services to be ready..."
    
    $maxAttempts = 30
    $attempt = 1
    
    while ($attempt -le $maxAttempts) {
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8080/health" -TimeoutSec 5 -ErrorAction Stop
            if ($response.StatusCode -eq 200) {
                Write-Success "Firefly III is ready!"
                return
            }
        } catch {
            # Continue trying
        }
        
        if ($attempt -eq $maxAttempts) {
            Write-Error "Firefly III failed to start after $maxAttempts attempts"
            Write-Status "Check logs with: $script:DockerCompose -f docker-compose.local.yml logs"
            exit 1
        }
        
        Write-Status "Attempt $attempt/$maxAttempts - waiting for Firefly III..."
        Start-Sleep 10
        $attempt++
    }
}

# Run database migrations
function Invoke-Migrations {
    Write-Status "Running database migrations..."
    
    & $script:DockerCompose -f docker-compose.local.yml exec app php artisan migrate --force
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Database migrations completed"
    } else {
        Write-Warning "Database migrations may have failed. Check logs."
    }
}

# Install Ollama models
function Install-OllamaModels {
    if ($SkipOllama) {
        Write-Status "Skipping Ollama model installation"
        return
    }
    
    Write-Status "Installing Ollama models (this may take a while)..."
    
    # Wait for Ollama to be ready
    $maxAttempts = 10
    $attempt = 1
    
    while ($attempt -le $maxAttempts) {
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:11434/api/version" -TimeoutSec 5 -ErrorAction Stop
            break
        } catch {
            if ($attempt -eq $maxAttempts) {
                Write-Warning "Ollama is not ready. Skipping model installation."
                return
            }
            
            Write-Status "Waiting for Ollama to be ready... (attempt $attempt/$maxAttempts)"
            Start-Sleep 10
            $attempt++
        }
    }
    
    # Install recommended models
    Write-Status "Installing llama2 model..."
    & $script:DockerCompose -f docker-compose.local.yml exec ollama ollama pull llama2:7b
    
    Write-Status "Installing codellama model..."
    & $script:DockerCompose -f docker-compose.local.yml exec ollama ollama pull codellama:7b
    
    Write-Success "Ollama models installed"
}

# Show final information
function Show-FinalInfo {
    Write-Host ""
    Write-Host "🎉 Firefly III Local Development Setup Complete!" -ForegroundColor Green
    Write-Host "=============================================="
    Write-Host ""
    Write-Host "📊 Firefly III: http://localhost:8080"
    Write-Host "🤖 Ollama API: http://localhost:11434"
    Write-Host ""
    Write-Host "📝 Useful commands:"
    Write-Host "  View logs:       $script:DockerCompose -f docker-compose.local.yml logs -f"
    Write-Host "  Stop services:   $script:DockerCompose -f docker-compose.local.yml down"
    Write-Host "  Restart:         $script:DockerCompose -f docker-compose.local.yml restart"
    Write-Host "  Shell access:    $script:DockerCompose -f docker-compose.local.yml exec app bash"
    Write-Host ""
    Write-Host "🔧 Configuration:"
    Write-Host "  Environment:     .env.local"
    Write-Host "  Compose file:    docker-compose.local.yml"
    Write-Host ""
    Write-Success "Happy coding! 🚀"
}

# Main execution
function Main {
    Write-Status "Starting setup process..."
    
    try {
        Test-Docker
        Test-DockerCompose
        Set-Environment
        New-Directories
        Test-SupabaseNetwork
        Get-Images
        Start-Services
        Wait-ForServices
        Invoke-Migrations
        Install-OllamaModels
        Show-FinalInfo
    } catch {
        Write-Error "Setup failed: $($_.Exception.Message)"
        exit 1
    }
}

# Add required assembly for password generation
Add-Type -AssemblyName System.Web

# Run main function
Main
