# Setup Firefly III Data Importer for Couples
Write-Host "🚀 Setting up Firefly III Data Importer for Couples functionality..." -ForegroundColor Green

Write-Host "`n📋 Step 1: Starting enhanced Docker services..." -ForegroundColor Yellow
docker compose -f docker-compose.local.yml up -d

Write-Host "`n⏱️ Waiting for services to start..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

Write-Host "`n📋 Step 2: Checking service status..." -ForegroundColor Yellow
docker compose -f docker-compose.local.yml ps

Write-Host "`n📋 Step 3: Service URLs..." -ForegroundColor Yellow
Write-Host "🌐 Firefly III: http://localhost:8080" -ForegroundColor Cyan
Write-Host "📊 Data Importer: http://localhost:8081" -ForegroundColor Cyan

Write-Host "`n📋 Step 4: Setting up Personal Access Token..." -ForegroundColor Yellow
Write-Host "⚠️  IMPORTANT: You need to generate a Personal Access Token in Firefly III:" -ForegroundColor Red
Write-Host "   1. Open http://localhost:8080" -ForegroundColor White
Write-Host "   2. Go to Options -> Profile" -ForegroundColor White
Write-Host "   3. Click 'OAuth' tab" -ForegroundColor White
Write-Host "   4. Create a 'Personal Access Token'" -ForegroundColor White
Write-Host "   5. Copy the token and update .env.local FIREFLY_TOKEN value" -ForegroundColor White

Write-Host "`n📋 Step 5: Test the integration..." -ForegroundColor Yellow
Write-Host "📄 Sample CSV file: import-data/couples-sample-transactions.csv" -ForegroundColor Cyan
Write-Host "⚙️  Sample config: couples-configs/couples-basic-config.json" -ForegroundColor Cyan

Write-Host "`n📋 Step 6: Import test data..." -ForegroundColor Yellow
Write-Host "Option A - Web Interface:" -ForegroundColor Cyan
Write-Host "   1. Visit http://localhost:8081" -ForegroundColor White
Write-Host "   2. Upload couples-sample-transactions.csv" -ForegroundColor White
Write-Host "   3. Select couples-basic-config.json" -ForegroundColor White
Write-Host "   4. Follow the import wizard" -ForegroundColor White

Write-Host "`nOption B - API (after setting token):" -ForegroundColor Cyan
Write-Host '   curl -X POST "http://localhost:8081/autoupload?secret=couples_import_secret_2025_secure_key" \' -ForegroundColor White
Write-Host '     -H "Authorization: Bearer YOUR_TOKEN_HERE" \' -ForegroundColor White
Write-Host '     -F "importable=@import-data/couples-sample-transactions.csv" \' -ForegroundColor White
Write-Host '     -F "json=@couples-configs/couples-basic-config.json"' -ForegroundColor White

Write-Host "`n📋 Step 7: Verify the import..." -ForegroundColor Yellow
Write-Host "🔍 Check http://localhost:8080/transactions to see imported data" -ForegroundColor Cyan
Write-Host "🏷️  Look for transactions with proper categories (Person 1/2, Shared)" -ForegroundColor Cyan

Write-Host "`n✅ Setup complete!" -ForegroundColor Green
Write-Host "🎯 Next: Generate the Personal Access Token and test the import!" -ForegroundColor Yellow

Write-Host "`n📚 Documentation:" -ForegroundColor Yellow
Write-Host "   📖 COUPLES_IMPORT_README.md - Complete usage guide" -ForegroundColor White
Write-Host "   🔧 COUPLES_IMPORT_INTEGRATION_PLAN.md - Technical details" -ForegroundColor White
Write-Host "   📋 COUPLES_DATA_INTEGRATION_STRATEGY.md - Strategy overview" -ForegroundColor White