# PowerShell script for setting up and managing Watch Folders in Firefly III
# Enhanced Document Processing System

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("setup", "start", "stop", "status", "test")]
    [string]$Action = "setup",
    
    [Parameter(Mandatory=$false)]
    [string]$WatchPath = "",
    
    [Parameter(Mandatory=$false)]
    [int]$Interval = 30,
    
    [Parameter(Mandatory=$false)]
    [switch]$Once = $false,
    
    [Parameter(Mandatory=$false)]
    [int]$UserId = 1
)

# Script configuration
$ScriptTitle = "🗂️  Firefly III Enhanced Document Processing - Watch Folder Manager"
$ProjectPath = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent

# Colors for output
$ErrorColor = "Red"
$SuccessColor = "Green" 
$InfoColor = "Cyan"
$WarningColor = "Yellow"

function Write-ColoredOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

function Show-Header {
    Clear-Host
    Write-ColoredOutput $ScriptTitle $InfoColor
    Write-ColoredOutput ("=" * $ScriptTitle.Length) $InfoColor
    Write-Host ""
}

function Test-Prerequisites {
    Write-ColoredOutput "🔍 Checking prerequisites..." $InfoColor
    
    # Check if we're in the right directory
    if (-not (Test-Path "artisan")) {
        Write-ColoredOutput "❌ Error: Not in Firefly III project directory" $ErrorColor
        Write-ColoredOutput "Please run this script from the Firefly III root directory" $WarningColor
        exit 1
    }
    
    # Check if PHP is available
    try {
        $phpVersion = php -v 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-ColoredOutput "✅ PHP is available" $SuccessColor
        } else {
            throw "PHP not found"
        }
    } catch {
        Write-ColoredOutput "❌ Error: PHP is not available in PATH" $ErrorColor
        Write-ColoredOutput "Please ensure PHP is installed and in your PATH" $WarningColor
        exit 1
    }
    
    # Check if Laravel is set up
    if (-not (Test-Path ".env")) {
        Write-ColoredOutput "❌ Error: .env file not found" $ErrorColor
        Write-ColoredOutput "Please set up Firefly III first" $WarningColor
        exit 1
    }
    
    Write-ColoredOutput "✅ Prerequisites check passed" $SuccessColor
    Write-Host ""
}

function Setup-WatchFolders {
    Write-ColoredOutput "🔧 Setting up Watch Folder system..." $InfoColor
    Write-Host ""
    
    # Run the setup script
    try {
        php setup-watch-folders.php
        if ($LASTEXITCODE -eq 0) {
            Write-ColoredOutput "✅ Watch folder setup completed successfully" $SuccessColor
        } else {
            Write-ColoredOutput "❌ Setup failed with exit code $LASTEXITCODE" $ErrorColor
            exit 1
        }
    } catch {
        Write-ColoredOutput "❌ Error running setup script: $_" $ErrorColor
        exit 1
    }
    
    Write-Host ""
    Write-ColoredOutput "📁 Default watch folder created at:" $InfoColor
    $watchPath = Join-Path $ProjectPath "storage\app\watch"
    Write-ColoredOutput "   $watchPath" $WarningColor
    
    Write-Host ""
    Write-ColoredOutput "🚀 Next steps:" $InfoColor
    Write-ColoredOutput "1. Drop some documents into the watch folder" $InfoColor
    Write-ColoredOutput "2. Run: .\watch-folders.ps1 -Action start" $InfoColor
    Write-ColoredOutput "3. Or run once: .\watch-folders.ps1 -Action start -Once" $InfoColor
}

function Start-WatchFolders {
    if ($WatchPath -ne "") {
        Write-ColoredOutput "🔍 Starting watch folder monitoring for specific path..." $InfoColor
        Write-ColoredOutput "Path: $WatchPath" $WarningColor
        Write-ColoredOutput "User ID: $UserId" $WarningColor
        
        if ($Once) {
            php artisan watch-folders:run --once --path="$WatchPath" --user=$UserId
        } else {
            Write-ColoredOutput "Monitoring every $Interval seconds. Press Ctrl+C to stop." $InfoColor
            php artisan watch-folders:run --interval=$Interval --path="$WatchPath" --user=$UserId
        }
    } else {
        Write-ColoredOutput "🔍 Starting watch folder monitoring..." $InfoColor
        Write-ColoredOutput "Monitoring configured watch folders..." $WarningColor
        
        if ($Once) {
            Write-ColoredOutput "Running once..." $InfoColor
            php artisan watch-folders:run --once
        } else {
            Write-ColoredOutput "Monitoring every $Interval seconds. Press Ctrl+C to stop." $InfoColor
            php artisan watch-folders:run --interval=$Interval
        }
    }
}

function Stop-WatchFolders {
    Write-ColoredOutput "🛑 Stopping watch folder monitoring..." $InfoColor
    
    # Find and stop any running watch-folders processes
    $processes = Get-Process | Where-Object { $_.ProcessName -eq "php" -and $_.CommandLine -like "*watch-folders:run*" }
    
    if ($processes.Count -eq 0) {
        Write-ColoredOutput "ℹ️  No running watch folder processes found" $WarningColor
    } else {
        foreach ($process in $processes) {
            try {
                Stop-Process -Id $process.Id -Force
                Write-ColoredOutput "✅ Stopped process ID: $($process.Id)" $SuccessColor
            } catch {
                Write-ColoredOutput "❌ Failed to stop process ID: $($process.Id)" $ErrorColor
            }
        }
    }
}

function Show-Status {
    Write-ColoredOutput "📊 Watch Folder System Status" $InfoColor
    Write-ColoredOutput "================================" $InfoColor
    Write-Host ""
    
    # Check if Docker containers are running
    try {
        $dockerStatus = docker ps --format "table {{.Names}}\t{{.Status}}" 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-ColoredOutput "🐳 Docker Containers:" $InfoColor
            Write-Host $dockerStatus
            Write-Host ""
        }
    } catch {
        Write-ColoredOutput "⚠️  Docker not available or no containers running" $WarningColor
        Write-Host ""
    }
    
    # Check API status
    try {
        Write-ColoredOutput "🌐 Testing API endpoints..." $InfoColor
        
        $apiTests = @(
            @{ Name = "Firefly III Core"; Url = "http://localhost:8080/health" },
            @{ Name = "Data Importer"; Url = "http://localhost:8081/health" },
            @{ Name = "Ollama AI Server"; Url = "http://localhost:11434/api/tags" }
        )
        
        foreach ($test in $apiTests) {
            try {
                $response = Invoke-WebRequest -Uri $test.Url -Method Get -TimeoutSec 5 -UseBasicParsing
                if ($response.StatusCode -eq 200) {
                    Write-ColoredOutput "✅ $($test.Name): Running" $SuccessColor
                } else {
                    Write-ColoredOutput "⚠️  $($test.Name): Unexpected status $($response.StatusCode)" $WarningColor
                }
            } catch {
                Write-ColoredOutput "❌ $($test.Name): Not responding" $ErrorColor
            }
        }
    } catch {
        Write-ColoredOutput "❌ Error testing API endpoints: $_" $ErrorColor
    }
    
    Write-Host ""
    
    # Show watch folder configuration
    try {
        Write-ColoredOutput "📁 Watch Folder Configuration:" $InfoColor
        php artisan watch-folders:run --once 2>&1 | Select-String -Pattern "Watch Folder|Path:|User ID:|Auto Create|Use Vision|Move After"
    } catch {
        Write-ColoredOutput "❌ Error getting watch folder configuration: $_" $ErrorColor
    }
    
    Write-Host ""
    
    # Check queue status
    try {
        Write-ColoredOutput "🔄 Queue Status:" $InfoColor
        $queueConfig = php artisan tinker --execute="echo config('queue.default');" 2>$null
        Write-ColoredOutput "Default Queue Driver: $queueConfig" $InfoColor
    } catch {
        Write-ColoredOutput "⚠️  Could not determine queue status" $WarningColor
    }
}

function Test-WatchFolder {
    if ($WatchPath -eq "") {
        $WatchPath = Join-Path $ProjectPath "storage\app\watch"
    }
    
    Write-ColoredOutput "🧪 Testing watch folder path: $WatchPath" $InfoColor
    Write-Host ""
    
    # Test path accessibility
    if (Test-Path $WatchPath) {
        Write-ColoredOutput "✅ Path exists and is accessible" $SuccessColor
        
        # Count files
        $supportedExtensions = @("*.jpg", "*.jpeg", "*.png", "*.pdf", "*.csv", "*.xlsx", "*.xls", "*.txt")
        $files = @()
        
        foreach ($ext in $supportedExtensions) {
            $files += Get-ChildItem -Path $WatchPath -Filter $ext -Recurse -File
        }
        
        Write-ColoredOutput "📄 Found $($files.Count) supported files" $InfoColor
        
        if ($files.Count -gt 0) {
            Write-ColoredOutput "Sample files:" $InfoColor
            $files | Select-Object -First 5 | ForEach-Object {
                $size = [math]::Round($_.Length / 1KB, 2)
                Write-ColoredOutput "  - $($_.Name) ($size KB)" $WarningColor
            }
        }
        
        # Test processing
        Write-Host ""
        Write-ColoredOutput "🔄 Running test processing..." $InfoColor
        php artisan watch-folders:run --once --path="$WatchPath" --user=$UserId
        
    } else {
        Write-ColoredOutput "❌ Path does not exist: $WatchPath" $ErrorColor
        Write-ColoredOutput "Creating directory..." $InfoColor
        
        try {
            New-Item -ItemType Directory -Path $WatchPath -Force | Out-Null
            Write-ColoredOutput "✅ Directory created successfully" $SuccessColor
        } catch {
            Write-ColoredOutput "❌ Failed to create directory: $_" $ErrorColor
        }
    }
}

function Show-Usage {
    Write-ColoredOutput "📖 Usage:" $InfoColor
    Write-ColoredOutput "================================" $InfoColor
    Write-Host ""
    Write-ColoredOutput "Setup and initialize:" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action setup" $WarningColor
    Write-Host ""
    Write-ColoredOutput "Start monitoring (continuous):" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action start" $WarningColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action start -Interval 60" $WarningColor
    Write-Host ""
    Write-ColoredOutput "Run once:" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action start -Once" $WarningColor
    Write-Host ""
    Write-ColoredOutput "Monitor specific folder:" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action start -WatchPath 'C:\MyDocuments' -UserId 1" $WarningColor
    Write-Host ""
    Write-ColoredOutput "Test configuration:" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action test" $WarningColor
    Write-Host ""
    Write-ColoredOutput "Check status:" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action status" $WarningColor
    Write-Host ""
    Write-ColoredOutput "Stop monitoring:" $InfoColor
    Write-ColoredOutput "  .\watch-folders.ps1 -Action stop" $WarningColor
}

# Main script execution
Show-Header

switch ($Action.ToLower()) {
    "setup" {
        Test-Prerequisites
        Setup-WatchFolders
    }
    "start" {
        Test-Prerequisites
        Start-WatchFolders
    }
    "stop" {
        Stop-WatchFolders
    }
    "status" {
        Show-Status
    }
    "test" {
        Test-Prerequisites
        Test-WatchFolder
    }
    default {
        Show-Usage
    }
}

Write-Host ""
Write-ColoredOutput "🎯 Watch Folder Manager completed!" $SuccessColor