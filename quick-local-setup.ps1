#!/usr/bin/env pwsh
# Quick Local Setup for Firefly III AI Dashboard

Write-Host "🔥 Firefly III AI Dashboard - Quick Local Setup" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan

# Check prerequisites
Write-Host "Checking prerequisites..." -ForegroundColor Yellow

# Check Supabase CLI
try {
    $supabaseVersion = supabase --version
    Write-Host "✅ Supabase CLI: $supabaseVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Supabase CLI not found. Install with:" -ForegroundColor Red
    Write-Host "   scoop install supabase" -ForegroundColor White
    exit 1
}

# Check Docker (for Ollama)
try {
    docker --version | Out-Null
    Write-Host "✅ Docker is available" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Docker not found - Ollama will need manual installation" -ForegroundColor Yellow
}

# Start Supabase locally
Write-Host "`nStarting LOCAL Supabase..." -ForegroundColor Yellow
try {
    # Check if already running
    $status = supabase status 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Supabase is already running" -ForegroundColor Green
    } else {
        Write-Host "Starting Supabase services..." -ForegroundColor Yellow
        supabase start
        Start-Sleep -Seconds 3
        Write-Host "✅ Supabase started successfully" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Error starting Supabase: $_" -ForegroundColor Red
    exit 1
}

# Initialize Supabase project if needed
if (!(Test-Path "supabase")) {
    Write-Host "Initializing Supabase project..." -ForegroundColor Yellow
    supabase init
}

# Create basic database schema
Write-Host "`nSetting up database schema..." -ForegroundColor Yellow
$migrationDir = "supabase/migrations"
if (!(Test-Path $migrationDir)) {
    New-Item -Path $migrationDir -ItemType Directory -Force | Out-Null
}

$migrationFile = "$migrationDir/$(Get-Date -Format 'yyyyMMddHHmmss')_basic_firefly.sql"
$basicSchema = @"
-- Basic Firefly III AI Dashboard Schema
-- Creates essential tables for financial management

-- Accounts table
CREATE TABLE IF NOT EXISTS accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'asset',
    balance DECIMAL(15,2) DEFAULT 0.00,
    currency_code CHAR(3) DEFAULT 'USD',
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Categories table  
CREATE TABLE IF NOT EXISTS categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) DEFAULT '#6366f1',
    icon VARCHAR(50) DEFAULT 'fa-folder',
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Transactions table with AI features
CREATE TABLE IF NOT EXISTS transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    account_id UUID REFERENCES accounts(id) ON DELETE CASCADE,
    category_id UUID REFERENCES categories(id) ON DELETE SET NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency_code CHAR(3) DEFAULT 'USD',
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    type VARCHAR(20) NOT NULL DEFAULT 'expense',
    
    -- AI Features
    ai_category VARCHAR(255),
    ai_confidence_score DECIMAL(3,2),
    is_ai_processed BOOLEAN DEFAULT false,
    
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_date ON transactions(date DESC);
CREATE INDEX IF NOT EXISTS idx_accounts_user_id ON accounts(user_id);
CREATE INDEX IF NOT EXISTS idx_categories_user_id ON categories(user_id);

-- Enable Row Level Security
ALTER TABLE accounts ENABLE ROW LEVEL SECURITY;
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE transactions ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist
DROP POLICY IF EXISTS "Users can manage own accounts" ON accounts;
DROP POLICY IF EXISTS "Users can manage own categories" ON categories;
DROP POLICY IF EXISTS "Users can manage own transactions" ON transactions;

-- Create RLS policies
CREATE POLICY "Users can manage own accounts" ON accounts
    FOR ALL USING (auth.uid() = user_id);

CREATE POLICY "Users can manage own categories" ON categories
    FOR ALL USING (auth.uid() = user_id);

CREATE POLICY "Users can manage own transactions" ON transactions
    FOR ALL USING (auth.uid() = user_id);

-- Insert default categories (these will be available to all users)
INSERT INTO categories (user_id, name, color, icon, description) VALUES
    ('00000000-0000-0000-0000-000000000000', 'Food & Dining', '#ef4444', 'fa-utensils', 'Restaurants and groceries'),
    ('00000000-0000-0000-0000-000000000000', 'Transportation', '#3b82f6', 'fa-car', 'Gas, public transport, maintenance'),
    ('00000000-0000-0000-0000-000000000000', 'Entertainment', '#8b5cf6', 'fa-film', 'Movies, games, hobbies'),
    ('00000000-0000-0000-0000-000000000000', 'Bills & Utilities', '#f59e0b', 'fa-file-invoice-dollar', 'Rent, utilities, services'),
    ('00000000-0000-0000-0000-000000000000', 'Shopping', '#ec4899', 'fa-shopping-bag', 'Clothing, electronics, general'),
    ('00000000-0000-0000-0000-000000000000', 'Healthcare', '#10b981', 'fa-heartbeat', 'Medical expenses'),
    ('00000000-0000-0000-0000-000000000000', 'Income', '#22c55e', 'fa-money-bill-wave', 'Salary, bonuses, other income'),
    ('00000000-0000-0000-0000-000000000000', 'Other', '#6b7280', 'fa-question', 'Miscellaneous expenses')
ON CONFLICT (id) DO NOTHING;

-- Insert sample accounts for demonstration
INSERT INTO accounts (user_id, name, type, balance, currency_code) VALUES
    ('00000000-0000-0000-0000-000000000000', 'Checking Account', 'asset', 2500.00, 'USD'),
    ('00000000-0000-0000-0000-000000000000', 'Savings Account', 'asset', 15000.00, 'USD'),
    ('00000000-0000-0000-0000-000000000000', 'Credit Card', 'liability', -1200.00, 'USD')
ON CONFLICT (id) DO NOTHING;
"@

Set-Content -Path $migrationFile -Value $basicSchema
Write-Host "✅ Migration file created: $migrationFile" -ForegroundColor Green

# Apply migration
try {
    Write-Host "Applying database migration..." -ForegroundColor Yellow
    supabase db push --local
    Write-Host "✅ Database schema applied successfully" -ForegroundColor Green
} catch {
    Write-Host "❌ Error applying migration: $_" -ForegroundColor Red
    Write-Host "💡 You can apply it manually via Supabase Studio" -ForegroundColor Cyan
}

# Setup Ollama (optional)
Write-Host "`nSetting up Ollama (local AI)..." -ForegroundColor Yellow
try {
    # Check if Ollama container exists
    $ollamaExists = docker ps -a --filter "name=ollama" --format "{{.Names}}" 2>$null
    
    if ($ollamaExists) {
        Write-Host "Starting existing Ollama container..." -ForegroundColor Yellow
        docker start ollama 2>$null
    } else {
        Write-Host "Creating new Ollama container..." -ForegroundColor Yellow
        docker run -d -p 11434:11434 --name ollama ollama/ollama
    }
    
    Start-Sleep -Seconds 5
    
    # Test if Ollama is responding
    try {
        Invoke-WebRequest -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 5 | Out-Null
        Write-Host "✅ Ollama is running" -ForegroundColor Green
        
        # Pull a small model for testing
        Write-Host "Installing Llama 3.2 model (this may take a few minutes)..." -ForegroundColor Yellow
        docker exec ollama ollama pull llama3.2:1b 2>$null
        Write-Host "✅ Llama 3.2 model ready" -ForegroundColor Green
    } catch {
        Write-Host "⚠️  Ollama container started but not responding yet" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Could not start Ollama automatically" -ForegroundColor Yellow
    Write-Host "💡 You can start it manually with:" -ForegroundColor Cyan
    Write-Host "   docker run -d -p 11434:11434 --name ollama ollama/ollama" -ForegroundColor White
}

# Create environment file
Write-Host "`nCreating environment configuration..." -ForegroundColor Yellow
$envContent = @"
# Firefly III AI Dashboard - Local Configuration
SUPABASE_URL=http://localhost:54321
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZS1kZW1vIiwicm9sZSI6ImFub24iLCJleHAiOjE5ODM4MTI5OTZ9.CRXP1A7WOeoJeXxjNni43kdQwgnWNReilDMblYTn_I0

# AI Services (add your API keys if you have them)
OLLAMA_URL=http://localhost:11434
OPENAI_API_KEY=
GROQ_API_KEY=

# Dashboard
APP_TITLE=Firefly III AI Dashboard (Local)
"@

Set-Content -Path ".env.local" -Value $envContent
Write-Host "✅ Environment file created: .env.local" -ForegroundColor Green

# Show final status
Write-Host "`n📊 Local Dashboard Status:" -ForegroundColor Cyan
Write-Host "==========================" -ForegroundColor Cyan

try {
    $supabaseStatus = supabase status 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Supabase Services:" -ForegroundColor Green
        Write-Host "   🌐 Studio: http://localhost:54323" -ForegroundColor Gray
        Write-Host "   🚀 API: http://localhost:54321" -ForegroundColor Gray
        Write-Host "   🗄️  Database: localhost:54322" -ForegroundColor Gray
        Write-Host "   📈 Analytics: http://localhost:54327" -ForegroundColor Gray
    }
} catch {
    Write-Host "⚠️  Supabase status check failed" -ForegroundColor Yellow
}

# Check Ollama
try {
    Invoke-WebRequest -Uri "http://localhost:11434/api/tags" -Method GET -TimeoutSec 3 | Out-Null
    Write-Host "✅ Ollama AI: http://localhost:11434" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Ollama AI: Not responding" -ForegroundColor Yellow
}

Write-Host "`n🎯 Ready to Use:" -ForegroundColor Cyan
Write-Host "=================" -ForegroundColor Cyan
Write-Host "1. Open: firefly-ai-dashboard.html" -ForegroundColor White
Write-Host "2. Visit: http://localhost:54323 (Supabase Studio)" -ForegroundColor White  
Write-Host "3. All data stays LOCAL on your machine" -ForegroundColor Green
Write-Host "4. Add your AI API keys in Settings if desired" -ForegroundColor White

Write-Host "`n🔥 Local Firefly III AI Dashboard is ready!" -ForegroundColor Green

# Offer to open
$openDashboard = Read-Host "`nOpen the dashboard now? (y/N)"
if ($openDashboard -eq 'y' -or $openDashboard -eq 'Y') {
    Start-Process "firefly-ai-dashboard.html"
}
