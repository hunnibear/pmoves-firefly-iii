-- =====================================================
-- COUPLES TESTING SAMPLE DATA
-- =====================================================
-- This script creates realistic sample data for testing
-- couples functionality in Firefly III
-- =====================================================

-- Step 1: Create Sample Accounts
-- Asset accounts (checking/savings)
INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at) 
SELECT 1, 4, 'Joint Checking Account', 'US1234567890123456789', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Joint Checking Account');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 4, 'Personal Savings - P1', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Personal Savings - P1');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 4, 'Personal Savings - P2', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Personal Savings - P2');

-- Expense accounts (stores/vendors)
INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Grocery Store', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Grocery Store');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Gas Station', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Gas Station');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Restaurant', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Restaurant');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Coffee Shop', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Coffee Shop');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Utilities Company', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Utilities Company');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Rent Payment', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Rent Payment');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 6, 'Online Shopping', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Online Shopping');

-- Revenue accounts (income sources)
INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 5, 'Salary - Person 1', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Salary - Person 1');

INSERT INTO accounts (user_id, account_type_id, name, created_at, updated_at) 
SELECT 1, 5, 'Salary - Person 2', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Salary - Person 2');

-- Step 2: Create Couples Tags
INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'couple-p1', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-p1');

INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'couple-p2', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-p2');

INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'couple-shared', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-shared');

-- Additional category tags
INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'groceries', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'groceries');

INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'transportation', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'transportation');

INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'utilities', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'utilities');

INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'entertainment', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'entertainment');

INSERT INTO tags (tag, created_at, updated_at) 
SELECT 'personal', NOW(), NOW() 
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'personal');

-- Step 3: Create Sample Goals (Piggy Banks)
INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at) 
SELECT 
    (SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1),
    'Emergency Fund', 
    10000.00, 
    2500.00, 
    CURRENT_DATE - INTERVAL '6 months', 
    CURRENT_DATE + INTERVAL '18 months', 
    NOW(), 
    NOW() 
WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'Emergency Fund');

INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at) 
SELECT 
    (SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1),
    'Vacation Fund', 
    5000.00, 
    850.00, 
    CURRENT_DATE - INTERVAL '3 months', 
    CURRENT_DATE + INTERVAL '9 months', 
    NOW(), 
    NOW() 
WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'Vacation Fund');

INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at) 
SELECT 
    (SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1),
    'New Car Fund', 
    8000.00, 
    1200.00, 
    CURRENT_DATE - INTERVAL '2 months', 
    CURRENT_DATE + INTERVAL '14 months', 
    NOW(), 
    NOW() 
WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'New Car Fund');

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================
-- Run these after executing the above to verify data was created

-- SELECT 'Accounts Created:' as info, COUNT(*) as count FROM accounts WHERE user_id = 1;
-- SELECT 'Tags Created:' as info, COUNT(*) as count FROM tags;
-- SELECT 'Goals Created:' as info, COUNT(*) as count FROM piggy_banks;

-- Show all couples tags
-- SELECT id, tag FROM tags WHERE tag LIKE 'couple-%' ORDER BY tag;

-- Show all accounts by type
-- SELECT 
--     CASE account_type_id 
--         WHEN 4 THEN 'Asset Account'
--         WHEN 5 THEN 'Revenue Account' 
--         WHEN 6 THEN 'Expense Account'
--         ELSE 'Other'
--     END as account_type,
--     name 
-- FROM accounts 
-- WHERE user_id = 1 
-- ORDER BY account_type_id, name;

-- Show goals with progress
-- SELECT 
--     name,
--     target_amount,
--     current_amount,
--     ROUND((current_amount / target_amount * 100), 2) as progress_percent,
--     target_date
-- FROM piggy_banks pb
-- JOIN accounts a ON pb.account_id = a.id
-- WHERE a.user_id = 1
-- ORDER BY name;