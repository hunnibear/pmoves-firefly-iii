# Firefly III Production Deployment Script for Windows PowerShell
# This script helps deploy Firefly III to production with VPS Supabase

param(
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
    Write-Host "Firefly III Production Deployment Script for Windows"
    Write-Host ""
    Write-Host "Usage: .\deploy-production.ps1 [OPTIONS]"
    Write-Host ""
    Write-Host "Options:"
    Write-Host "  -Help          Show this help message"
    Write-Host ""
    Write-Host "Prerequisites:"
    Write-Host "  - Docker Desktop installed and running"
    Write-Host "  - .env.production.example file configured"
    Write-Host "  - VPS Supabase instance running and accessible"
    Write-Host "  - Domain name pointing to this server (for SSL)"
    Write-Host "  - PowerShell running as Administrator (for SSL setup)"
    Write-Host ""
    exit 0
}

Write-Host "🚀 Firefly III Production Deployment (Windows)" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Yellow

# Check if running as Administrator
function Test-Administrator {
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    $adminRole = [Security.Principal.WindowsBuiltInRole]::Administrator
    
    if (-not $principal.IsInRole($adminRole)) {
        Write-Warning "Some operations may require Administrator privileges."
        Write-Warning "Consider running PowerShell as Administrator for full functionality."
    }
}

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

# Setup production environment file
function Set-ProductionEnvironment {
    Write-Status "Setting up production environment file..."
    
    if (-not (Test-Path .env.production)) {
        if (Test-Path .env.production.example) {
            Copy-Item .env.production.example .env.production
            Write-Success "Created .env.production from example"
            Write-Warning "⚠️  IMPORTANT: Please review and update .env.production with your production values!"
        } else {
            Write-Error ".env.production.example not found. Please create it first."
            exit 1
        }
    } else {
        Write-Warning ".env.production already exists. Please ensure it's properly configured."
    }
    
    # Check critical production settings
    Test-ProductionConfig
}

# Check production configuration
function Test-ProductionConfig {
    Write-Status "Checking production configuration..."
    
    $content = Get-Content .env.production -Raw
    $errors = 0
    
    # Check for example values that need to be changed
    if ($content -match "SomeRandomStringOf32CharsExactly") {
        Write-Error "APP_KEY still contains example value. Generate a secure key!"
        $errors++
    }
    
    if ($content -match "your-domain\.com") {
        Write-Error "Domain still contains example value. Update APP_URL and COOKIE_DOMAIN!"
        $errors++
    }
    
    if ($content -match "your-secure-32-character-cron-token") {
        Write-Error "STATIC_CRON_TOKEN still contains example value. Generate a secure token!"
        $errors++
    }
    
    if ($content -match "mail@example\.com") {
        Write-Warning "Email still contains example value. Update SITE_OWNER!"
    }
    
    if ($errors -gt 0) {
        Write-Error "Found $errors critical configuration errors. Please fix before deploying."
        exit 1
    }
    
    Write-Success "Production configuration check passed"
}

# Setup SSL certificates
function Set-SSL {
    Write-Status "Setting up SSL certificates..."
    
    New-Item -ItemType Directory -Force -Path ssl | Out-Null
    
    if (-not ((Test-Path ssl\cert.pem) -and (Test-Path ssl\key.pem))) {
        Write-Warning "SSL certificates not found. You have several options:"
        Write-Host "1. Use win-acme for Let's Encrypt (recommended for production)"
        Write-Host "2. Upload your own certificates to .\ssl\ directory"
        Write-Host "3. Generate self-signed certificates (development only)"
        Write-Host ""
        $sslOption = Read-Host "Choose option (1-3)"
        
        switch ($sslOption) {
            "1" { Set-WinAcme }
            "2" { 
                Write-Status "Please upload your certificates:"
                Write-Status "  - Certificate: .\ssl\cert.pem"
                Write-Status "  - Private key: .\ssl\key.pem"
                Read-Host "Press Enter when certificates are uploaded..."
            }
            "3" { New-SelfSignedCert }
            default {
                Write-Error "Invalid option selected"
                exit 1
            }
        }
    } else {
        Write-Success "SSL certificates found"
    }
}

# Setup win-acme for Let's Encrypt
function Set-WinAcme {
    Write-Status "Setting up Let's Encrypt certificates with win-acme..."
    
    $domain = Read-Host "Enter your domain name"
    $email = Read-Host "Enter your email address"
    
    # Check if win-acme is available
    if (-not (Test-Path "wacs.exe")) {
        Write-Warning "win-acme not found. Please download it from https://www.win-acme.com/"
        Write-Status "After downloading, extract and place wacs.exe in this directory"
        Read-Host "Press Enter when win-acme is ready..."
    }
    
    if (Test-Path "wacs.exe") {
        Write-Status "Running win-acme for domain: $domain"
        .\wacs.exe --target manual --host $domain --emailaddress $email --accepttos --unattended
        
        # Copy certificates (adjust path as needed based on win-acme output)
        $certPath = "C:\ProgramData\win-acme\certificates\$domain"
        if (Test-Path "$certPath\*-crt.pem") {
            Copy-Item "$certPath\*-crt.pem" ssl\cert.pem
            Copy-Item "$certPath\*-key.pem" ssl\key.pem
            Write-Success "Let's Encrypt certificates installed"
        } else {
            Write-Error "Certificates not found in expected location. Please check win-acme output."
        }
    }
}

# Generate self-signed certificates (development only)
function New-SelfSignedCert {
    Write-Warning "Generating self-signed certificates (NOT for production use!)"
    
    # Check if OpenSSL is available
    if (Get-Command openssl -ErrorAction SilentlyContinue) {
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ssl\key.pem -out ssl\cert.pem -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
        Write-Success "Self-signed certificates generated"
    } else {
        Write-Error "OpenSSL not found. Please install OpenSSL for Windows or use option 2 to upload certificates."
        exit 1
    }
}

# Create necessary directories
function New-Directories {
    Write-Status "Creating necessary directories..."
    
    New-Item -ItemType Directory -Force -Path logs | Out-Null
    New-Item -ItemType Directory -Force -Path backups | Out-Null
    
    Write-Success "Directories created"
}

# Build production images
function Build-Images {
    Write-Status "Building production Docker images..."
    
    $buildDate = Get-Date -Format "yyyy-MM-ddTHH:mm:ssZ"
    $version = "latest"
    $revision = "unknown"
    
    # Try to get git info
    if (Get-Command git -ErrorAction SilentlyContinue) {
        try {
            $version = git describe --tags --always 2>$null
            $revision = git rev-parse HEAD 2>$null
            if (-not $version) { $version = "latest" }
            if (-not $revision) { $revision = "unknown" }
        } catch {
            # Use defaults
        }
    }
    
    docker build -f Dockerfile.production -t firefly-iii-ai:production --build-arg BUILD_DATE=$buildDate --build-arg VERSION=$version --build-arg REVISION=$revision .
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Production images built successfully"
    } else {
        Write-Error "Failed to build production images"
        exit 1
    }
}

# Pull required images
function Get-Images {
    Write-Status "Pulling required Docker images..."
    
    & $script:DockerCompose -f docker-compose.production.yml pull
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Images pulled successfully"
    } else {
        Write-Error "Failed to pull images"
        exit 1
    }
}

# Test database connection
function Test-DatabaseConnection {
    Write-Status "Testing database connection..."
    
    $content = Get-Content .env.production -Raw
    $dbHost = ($content | Select-String "^DB_HOST=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    $dbPort = ($content | Select-String "^DB_PORT=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    $dbDatabase = ($content | Select-String "^DB_DATABASE=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    $dbUsername = ($content | Select-String "^DB_USERNAME=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    
    Write-Status "Testing connection to $dbHost`:$dbPort/$dbDatabase as $dbUsername"
    
    # Test connection using Test-NetConnection
    try {
        $result = Test-NetConnection -ComputerName $dbHost -Port $dbPort -WarningAction SilentlyContinue
        if ($result.TcpTestSucceeded) {
            Write-Success "Database connection test passed"
        } else {
            Write-Error "Cannot connect to database. Please check your Supabase configuration."
            exit 1
        }
    } catch {
        Write-Warning "Network test failed. Please ensure database is accessible."
    }
}

# Deploy services
function Deploy-Services {
    Write-Status "Deploying production services..."
    
    # Stop existing services if running
    & $script:DockerCompose -f docker-compose.production.yml down
    
    # Start services
    & $script:DockerCompose -f docker-compose.production.yml up -d
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Services deployed successfully"
    } else {
        Write-Error "Failed to deploy services"
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
            $response = Invoke-WebRequest -Uri "https://localhost/health" -SkipCertificateCheck -TimeoutSec 5 -ErrorAction Stop
            if ($response.StatusCode -eq 200) {
                Write-Success "Firefly III is ready!"
                return
            }
        } catch {
            # Continue trying
        }
        
        if ($attempt -eq $maxAttempts) {
            Write-Error "Firefly III failed to start after $maxAttempts attempts"
            Write-Status "Check logs with: $script:DockerCompose -f docker-compose.production.yml logs"
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
    
    & $script:DockerCompose -f docker-compose.production.yml exec app php artisan migrate --force
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Database migrations completed"
    } else {
        Write-Warning "Database migrations may have failed. Check logs."
    }
}

# Create backup script
function New-BackupScript {
    Write-Status "Creating backup script..."
    
    $backupScript = @'
# Firefly III Backup Script for Windows PowerShell

$Date = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupDir = ".\backups"
$ComposeFile = "docker-compose.production.yml"

Write-Host "Starting Firefly III backup - $Date"

# Create backup directory
New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null

# Backup database
Write-Host "Backing up database..."
docker-compose -f $ComposeFile exec -T app php artisan firefly-iii:export-data "$BackupDir\firefly-export-$Date.json"

# Backup uploads
Write-Host "Backing up uploads..."
$containerId = docker-compose -f $ComposeFile ps -q app
docker cp "${containerId}:/var/www/html/storage/upload" "$BackupDir\uploads-$Date"

# Backup configuration
Write-Host "Backing up configuration..."
Copy-Item .env.production "$BackupDir\env-$Date"

# Create zip archive
Write-Host "Creating backup archive..."
$archivePath = "$BackupDir\firefly-backup-$Date.zip"
Compress-Archive -Path "$BackupDir\firefly-export-$Date.json", "$BackupDir\uploads-$Date", "$BackupDir\env-$Date" -DestinationPath $archivePath -Force

# Cleanup temporary files
Remove-Item "$BackupDir\uploads-$Date" -Recurse -Force
Remove-Item "$BackupDir\firefly-export-$Date.json"
Remove-Item "$BackupDir\env-$Date"

Write-Host "Backup completed: $archivePath"

# Keep only last 7 backups
Get-ChildItem "$BackupDir\firefly-backup-*.zip" | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-7) } | Remove-Item
'@
    
    $backupScript | Set-Content backup-firefly.ps1
    Write-Success "Backup script created: .\backup-firefly.ps1"
}

# Show final information
function Show-FinalInfo {
    $content = Get-Content .env.production -Raw
    $appUrl = ($content | Select-String "^APP_URL=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    
    Write-Host ""
    Write-Host "🎉 Firefly III Production Deployment Complete!" -ForegroundColor Green
    Write-Host "============================================="
    Write-Host ""
    Write-Host "🌐 Access your application at: $appUrl"
    Write-Host ""
    Write-Host "📝 Important commands:"
    Write-Host "  View logs:       $script:DockerCompose -f docker-compose.production.yml logs -f"
    Write-Host "  Stop services:   $script:DockerCompose -f docker-compose.production.yml down"
    Write-Host "  Restart:         $script:DockerCompose -f docker-compose.production.yml restart"
    Write-Host "  Backup:          .\backup-firefly.ps1"
    Write-Host "  Shell access:    $script:DockerCompose -f docker-compose.production.yml exec app bash"
    Write-Host ""
    Write-Host "🔧 Configuration files:"
    Write-Host "  Environment:     .env.production"
    Write-Host "  Compose:         docker-compose.production.yml"
    Write-Host "  Nginx:           nginx.conf"
    Write-Host "  SSL certs:       .\ssl\"
    Write-Host ""
    Write-Host "⚠️  Post-deployment checklist:"
    Write-Host "  □ Test application functionality"
    Write-Host "  □ Verify SSL certificate"
    Write-Host "  □ Setup automated backups"
    Write-Host "  □ Configure monitoring alerts"
    Write-Host "  □ Update DNS records"
    Write-Host "  □ Setup firewall rules"
    Write-Host ""
    Write-Success "Production deployment successful! 🚀"
}

# Main execution
function Main {
    Write-Status "Starting production deployment process..."
    
    try {
        Test-Administrator
        Test-Docker
        Test-DockerCompose
        Set-ProductionEnvironment
        Set-SSL
        New-Directories
        Test-DatabaseConnection
        Build-Images
        Get-Images
        Deploy-Services
        Wait-ForServices
        Invoke-Migrations
        New-BackupScript
        Show-FinalInfo
    } catch {
        Write-Error "Deployment failed: $($_.Exception.Message)"
        exit 1
    }
}

# Run main function
Main
