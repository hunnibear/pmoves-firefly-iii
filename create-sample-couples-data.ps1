# Create Sample Couples Data PowerShell Script
Write-Host "🚀 Creating Sample Couples Data..." -ForegroundColor Green

# Function to execute SQL
function Execute-SQL {
    param($sql)
    docker exec supabase_db_pmoves-firefly-iii psql -U postgres -d postgres -c "$sql"
}

Write-Host "`n📋 Step 1: Creating sample accounts..." -ForegroundColor Yellow

# Create accounts
Execute-SQL "INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at) SELECT 1, 4, 'Joint Checking Account', 'US1234567890123456789', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Joint Checking Account');"

Execute-SQL "INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) SELECT 1, 6, 'Grocery Store', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Grocery Store');"

Execute-SQL "INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) SELECT 1, 6, 'Gas Station', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Gas Station');"

Execute-SQL "INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) SELECT 1, 6, 'Restaurant', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Restaurant');"

Execute-SQL "INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) SELECT 1, 6, 'Utilities Company', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Utilities Company');"

Execute-SQL "INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) SELECT 1, 5, 'Salary Income', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Salary Income');"

Write-Host "✅ Accounts created!" -ForegroundColor Green

Write-Host "`n📋 Step 2: Creating couples tags..." -ForegroundColor Yellow

# Create couples tags
Execute-SQL "INSERT INTO tags (tag, created_at, updated_at) SELECT 'couple-p1', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-p1');"

Execute-SQL "INSERT INTO tags (tag, created_at, updated_at) SELECT 'couple-p2', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-p2');"

Execute-SQL "INSERT INTO tags (tag, created_at, updated_at) SELECT 'couple-shared', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-shared');"

Write-Host "✅ Tags created!" -ForegroundColor Green

Write-Host "`n📋 Step 3: Creating sample transactions..." -ForegroundColor Yellow

# Get account IDs
$checkingId = (Execute-SQL "SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$groceryId = (Execute-SQL "SELECT id FROM accounts WHERE name = 'Grocery Store' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$gasId = (Execute-SQL "SELECT id FROM accounts WHERE name = 'Gas Station' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$restaurantId = (Execute-SQL "SELECT id FROM accounts WHERE name = 'Restaurant' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$utilitiesId = (Execute-SQL "SELECT id FROM accounts WHERE name = 'Utilities Company' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$salaryId = (Execute-SQL "SELECT id FROM accounts WHERE name = 'Salary Income' LIMIT 1;" | Select-String '\d+').Matches[0].Value

# Get tag IDs
$p1TagId = (Execute-SQL "SELECT id FROM tags WHERE tag = 'couple-p1' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$p2TagId = (Execute-SQL "SELECT id FROM tags WHERE tag = 'couple-p2' LIMIT 1;" | Select-String '\d+').Matches[0].Value
$sharedTagId = (Execute-SQL "SELECT id FROM tags WHERE tag = 'couple-shared' LIMIT 1;" | Select-String '\d+').Matches[0].Value

Write-Host "Account IDs: Checking=$checkingId, Grocery=$groceryId, Gas=$gasId" -ForegroundColor Cyan
Write-Host "Tag IDs: P1=$p1TagId, P2=$p2TagId, Shared=$sharedTagId" -ForegroundColor Cyan

# Create Person 1 Transactions
Write-Host "`n💰 Creating Person 1 transactions..." -ForegroundColor Magenta

# Person 1 - Groceries
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Weekly grocery shopping - P1', CURRENT_DATE - INTERVAL '3 days', 1, NOW(), NOW());"

$journalId = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Weekly grocery shopping - P1' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId, 'Weekly grocery shopping - P1', -85.50, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($groceryId, $journalId, 'Weekly grocery shopping - P1', 85.50, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($p1TagId, $journalId);"

# Person 1 - Gas
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Gas fill-up - P1', CURRENT_DATE - INTERVAL '2 days', 1, NOW(), NOW());"

$journalId2 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Gas fill-up - P1' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId2, 'Gas fill-up - P1', -45.00, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($gasId, $journalId2, 'Gas fill-up - P1', 45.00, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($p1TagId, $journalId2);"

# Create Person 2 Transactions
Write-Host "`n💰 Creating Person 2 transactions..." -ForegroundColor Magenta

# Person 2 - Coffee
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Coffee and lunch - P2', CURRENT_DATE - INTERVAL '1 day', 1, NOW(), NOW());"

$journalId3 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Coffee and lunch - P2' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId3, 'Coffee and lunch - P2', -22.75, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($restaurantId, $journalId3, 'Coffee and lunch - P2', 22.75, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($p2TagId, $journalId3);"

# Person 2 - Shopping
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Personal shopping - P2', CURRENT_DATE, 1, NOW(), NOW());"

$journalId4 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Personal shopping - P2' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId4, 'Personal shopping - P2', -125.00, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($groceryId, $journalId4, 'Personal shopping - P2', 125.00, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($p2TagId, $journalId4);"

# Create Shared Transactions
Write-Host "`n💰 Creating shared transactions..." -ForegroundColor Magenta

# Shared - Utilities
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Monthly electricity bill', CURRENT_DATE - INTERVAL '5 days', 1, NOW(), NOW());"

$journalId5 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Monthly electricity bill' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId5, 'Monthly electricity bill', -135.50, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($utilitiesId, $journalId5, 'Monthly electricity bill', 135.50, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($sharedTagId, $journalId5);"

# Shared - Date Night
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Date night dinner', CURRENT_DATE - INTERVAL '4 days', 1, NOW(), NOW());"

$journalId6 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Date night dinner' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId6, 'Date night dinner', -89.25, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($restaurantId, $journalId6, 'Date night dinner', 89.25, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($sharedTagId, $journalId6);"

# Shared - Rent
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Monthly rent payment', CURRENT_DATE - INTERVAL '7 days', 1, NOW(), NOW());"

$journalId7 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Monthly rent payment' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId7, 'Monthly rent payment', -1250.00, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($utilitiesId, $journalId7, 'Monthly rent payment', 1250.00, 1, NOW(), NOW());"

Execute-SQL "INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id) VALUES ($sharedTagId, $journalId7);"

# Create some unassigned transactions
Write-Host "`n💰 Creating unassigned transactions..." -ForegroundColor Magenta

# Unassigned - Amazon
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Amazon purchase', CURRENT_DATE - INTERVAL '1 day', 1, NOW(), NOW());"

$journalId8 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Amazon purchase' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId8, 'Amazon purchase', -67.99, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($groceryId, $journalId8, 'Amazon purchase', 67.99, 1, NOW(), NOW());"

# Unassigned - Pharmacy
Execute-SQL "INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, \`"order\`", created_at, updated_at) VALUES (1, 2, 'Pharmacy pickup', CURRENT_DATE, 1, NOW(), NOW());"

$journalId9 = (Execute-SQL "SELECT id FROM transaction_journals WHERE description = 'Pharmacy pickup' ORDER BY id DESC LIMIT 1;" | Select-String '\d+').Matches[0].Value

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($checkingId, $journalId9, 'Pharmacy pickup', -34.50, 0, NOW(), NOW());"

Execute-SQL "INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at) VALUES ($groceryId, $journalId9, 'Pharmacy pickup', 34.50, 1, NOW(), NOW());"

Write-Host "✅ Transactions created!" -ForegroundColor Green

Write-Host "`n📋 Step 4: Creating sample goals (piggy banks)..." -ForegroundColor Yellow

# Create goals
Execute-SQL "INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at) SELECT $checkingId, 'Emergency Fund', 10000.00, 2500.00, CURRENT_DATE - INTERVAL '6 months', CURRENT_DATE + INTERVAL '18 months', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'Emergency Fund');"

Execute-SQL "INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at) SELECT $checkingId, 'Vacation Fund', 5000.00, 850.00, CURRENT_DATE - INTERVAL '3 months', CURRENT_DATE + INTERVAL '9 months', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'Vacation Fund');"

Execute-SQL "INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at) SELECT $checkingId, 'New Car Fund', 8000.00, 1200.00, CURRENT_DATE - INTERVAL '2 months', CURRENT_DATE + INTERVAL '14 months', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'New Car Fund');"

Write-Host "✅ Goals created!" -ForegroundColor Green

Write-Host "`n📊 Step 5: Generating summary report..." -ForegroundColor Yellow

# Generate summary
Write-Host "`n🎉 Sample Data Creation Complete!" -ForegroundColor Green
Write-Host "📋 Summary Report:" -ForegroundColor Cyan

Execute-SQL "SELECT 'Accounts:' as item, COUNT(*) as count FROM accounts WHERE user_id = 1;"
Execute-SQL "SELECT 'Tags:' as item, COUNT(*) as count FROM tags WHERE tag LIKE 'couple-%';"
Execute-SQL "SELECT 'Transaction Journals:' as item, COUNT(*) as count FROM transaction_journals WHERE user_id = 1;"
Execute-SQL "SELECT 'Goals:' as item, COUNT(*) as count FROM piggy_banks pb JOIN accounts a ON pb.account_id = a.id WHERE a.user_id = 1;"

Write-Host "`n💰 Transaction Breakdown:" -ForegroundColor Cyan
Execute-SQL "SELECT CASE WHEN t.tag = 'couple-p1' THEN 'Person 1' WHEN t.tag = 'couple-p2' THEN 'Person 2' WHEN t.tag = 'couple-shared' THEN 'Shared' END as category, COUNT(ttj.transaction_journal_id) as count FROM tags t LEFT JOIN tag_transaction_journal ttj ON t.id = ttj.tag_id WHERE t.tag LIKE 'couple-%' GROUP BY t.tag ORDER BY t.tag;"

Write-Host "`n🚀 Ready to test couples functionality!" -ForegroundColor Green
Write-Host "Visit http://localhost:8080/couples to see the sample data in action!" -ForegroundColor Yellow