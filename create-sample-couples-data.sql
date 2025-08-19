-- Sample Data Script for Couples Testing
-- This script creates realistic couples budget data for testing

-- First, let's check our current user
SELECT id, email FROM users LIMIT 1;

-- Create some sample accounts if they don't exist
INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 4, 'Joint Checking Account', 'US1234567890123456789', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Joint Checking Account');

INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 6, 'Grocery Store', NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Grocery Store');

INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 6, 'Gas Station', NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Gas Station');

INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 6, 'Restaurant', NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Restaurant');

INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 6, 'Utilities Company', NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Utilities Company');

INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 5, 'Salary Income', NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Salary Income');

-- Create some categories if they don't exist
INSERT INTO categories (user_id, name, created_at, updated_at)
SELECT 1, 'Groceries', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Groceries');

INSERT INTO categories (user_id, name, created_at, updated_at)
SELECT 1, 'Transportation', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Transportation');

INSERT INTO categories (user_id, name, created_at, updated_at)
SELECT 1, 'Dining Out', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Dining Out');

INSERT INTO categories (user_id, name, created_at, updated_at)
SELECT 1, 'Utilities', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Utilities');

INSERT INTO categories (user_id, name, created_at, updated_at)
SELECT 1, 'Entertainment', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Entertainment');

-- Create couples-specific tags
INSERT INTO tags (tag, created_at, updated_at)
SELECT 'couple-p1', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-p1');

INSERT INTO tags (tag, created_at, updated_at)
SELECT 'couple-p2', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-p2');

INSERT INTO tags (tag, created_at, updated_at)
SELECT 'couple-shared', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM tags WHERE tag = 'couple-shared');

-- Get account IDs
\set checking_account_id `SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1`
\set grocery_account_id `SELECT id FROM accounts WHERE name = 'Grocery Store' LIMIT 1`
\set gas_account_id `SELECT id FROM accounts WHERE name = 'Gas Station' LIMIT 1`
\set restaurant_account_id `SELECT id FROM accounts WHERE name = 'Restaurant' LIMIT 1`
\set utilities_account_id `SELECT id FROM accounts WHERE name = 'Utilities Company' LIMIT 1`
\set salary_account_id `SELECT id FROM accounts WHERE name = 'Salary Income' LIMIT 1`

-- Get category IDs
\set groceries_cat_id `SELECT id FROM categories WHERE name = 'Groceries' LIMIT 1`
\set transport_cat_id `SELECT id FROM categories WHERE name = 'Transportation' LIMIT 1`
\set dining_cat_id `SELECT id FROM categories WHERE name = 'Dining Out' LIMIT 1`
\set utilities_cat_id `SELECT id FROM categories WHERE name = 'Utilities' LIMIT 1`
\set entertainment_cat_id `SELECT id FROM categories WHERE name = 'Entertainment' LIMIT 1`

-- Get tag IDs
\set p1_tag_id `SELECT id FROM tags WHERE tag = 'couple-p1' LIMIT 1`
\set p2_tag_id `SELECT id FROM tags WHERE tag = 'couple-p2' LIMIT 1`
\set shared_tag_id `SELECT id FROM tags WHERE tag = 'couple-shared' LIMIT 1`

-- Create sample transaction journals with transactions for Person 1 (couple-p1)
-- Person 1 Groceries
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Weekly grocery shopping', CURRENT_DATE - INTERVAL '3 days', 1, NOW(), NOW());

\set journal_id `SELECT id FROM transaction_journals WHERE description = 'Weekly grocery shopping' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id, 'Weekly grocery shopping', -85.50, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Grocery Store' LIMIT 1), :journal_id, 'Weekly grocery shopping', 85.50, 1, NOW(), NOW());

-- Tag it as Person 1
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-p1' LIMIT 1), :journal_id);

-- Person 1 Gas
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Gas fill-up', CURRENT_DATE - INTERVAL '2 days', 1, NOW(), NOW());

\set journal_id2 `SELECT id FROM transaction_journals WHERE description = 'Gas fill-up' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id2, 'Gas fill-up', -45.00, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Gas Station' LIMIT 1), :journal_id2, 'Gas fill-up', 45.00, 1, NOW(), NOW());

-- Tag it as Person 1
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-p1' LIMIT 1), :journal_id2);

-- Create sample transactions for Person 2 (couple-p2)
-- Person 2 Coffee
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Coffee and lunch', CURRENT_DATE - INTERVAL '1 day', 1, NOW(), NOW());

\set journal_id3 `SELECT id FROM transaction_journals WHERE description = 'Coffee and lunch' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id3, 'Coffee and lunch', -22.75, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Restaurant' LIMIT 1), :journal_id3, 'Coffee and lunch', 22.75, 1, NOW(), NOW());

-- Tag it as Person 2
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-p2' LIMIT 1), :journal_id3);

-- Person 2 Personal Shopping
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Personal shopping - clothes', CURRENT_DATE, 1, NOW(), NOW());

\set journal_id4 `SELECT id FROM transaction_journals WHERE description = 'Personal shopping - clothes' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id4, 'Personal shopping - clothes', -125.00, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Grocery Store' LIMIT 1), :journal_id4, 'Personal shopping - clothes', 125.00, 1, NOW(), NOW());

-- Tag it as Person 2
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-p2' LIMIT 1), :journal_id4);

-- Create sample shared expenses (couple-shared)
-- Shared Utilities
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Monthly electricity bill', CURRENT_DATE - INTERVAL '5 days', 1, NOW(), NOW());

\set journal_id5 `SELECT id FROM transaction_journals WHERE description = 'Monthly electricity bill' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id5, 'Monthly electricity bill', -135.50, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Utilities Company' LIMIT 1), :journal_id5, 'Monthly electricity bill', 135.50, 1, NOW(), NOW());

-- Tag it as Shared
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-shared' LIMIT 1), :journal_id5);

-- Shared Date Night
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Date night dinner', CURRENT_DATE - INTERVAL '4 days', 1, NOW(), NOW());

\set journal_id6 `SELECT id FROM transaction_journals WHERE description = 'Date night dinner' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id6, 'Date night dinner', -89.25, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Restaurant' LIMIT 1), :journal_id6, 'Date night dinner', 89.25, 1, NOW(), NOW());

-- Tag it as Shared
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-shared' LIMIT 1), :journal_id6);

-- Shared Rent/Mortgage
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Monthly rent payment', CURRENT_DATE - INTERVAL '7 days', 1, NOW(), NOW());

\set journal_id7 `SELECT id FROM transaction_journals WHERE description = 'Monthly rent payment' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id7, 'Monthly rent payment', -1250.00, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Utilities Company' LIMIT 1), :journal_id7, 'Monthly rent payment', 1250.00, 1, NOW(), NOW());

-- Tag it as Shared
INSERT INTO tag_transaction_journal (tag_id, transaction_journal_id)
VALUES ((SELECT id FROM tags WHERE tag = 'couple-shared' LIMIT 1), :journal_id7);

-- Create some income transactions
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 1, 'Monthly salary - Person 1', CURRENT_DATE - INTERVAL '10 days', 1, NOW(), NOW());

\set journal_id8 `SELECT id FROM transaction_journals WHERE description = 'Monthly salary - Person 1' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Salary Income' LIMIT 1), :journal_id8, 'Monthly salary - Person 1', -4500.00, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id8, 'Monthly salary - Person 1', 4500.00, 1, NOW(), NOW());

-- Person 2 salary
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 1, 'Monthly salary - Person 2', CURRENT_DATE - INTERVAL '10 days', 1, NOW(), NOW());

\set journal_id9 `SELECT id FROM transaction_journals WHERE description = 'Monthly salary - Person 2' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Salary Income' LIMIT 1), :journal_id9, 'Monthly salary - Person 2', -3800.00, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id9, 'Monthly salary - Person 2', 3800.00, 1, NOW(), NOW());

-- Create some sample goals (piggy banks)
INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at)
SELECT (SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), 'Emergency Fund', 10000.00, 2500.00, CURRENT_DATE - INTERVAL '6 months', CURRENT_DATE + INTERVAL '18 months', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'Emergency Fund');

INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at)
SELECT (SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), 'Vacation Fund', 5000.00, 850.00, CURRENT_DATE - INTERVAL '3 months', CURRENT_DATE + INTERVAL '9 months', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'Vacation Fund');

INSERT INTO piggy_banks (account_id, name, target_amount, current_amount, start_date, target_date, created_at, updated_at)
SELECT (SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), 'New Car Down Payment', 8000.00, 1200.00, CURRENT_DATE - INTERVAL '2 months', CURRENT_DATE + INTERVAL '14 months', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM piggy_banks WHERE name = 'New Car Down Payment');

-- Some unassigned transactions (no couple tags)
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Amazon purchase', CURRENT_DATE - INTERVAL '1 day', 1, NOW(), NOW());

\set journal_id10 `SELECT id FROM transaction_journals WHERE description = 'Amazon purchase' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id10, 'Amazon purchase', -67.99, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Grocery Store' LIMIT 1), :journal_id10, 'Amazon purchase', 67.99, 1, NOW(), NOW());

-- Another unassigned transaction
INSERT INTO transaction_journals (user_id, transaction_type_id, description, date, "order", created_at, updated_at)
VALUES (1, 2, 'Pharmacy pickup', CURRENT_DATE, 1, NOW(), NOW());

\set journal_id11 `SELECT id FROM transaction_journals WHERE description = 'Pharmacy pickup' ORDER BY id DESC LIMIT 1`

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Joint Checking Account' LIMIT 1), :journal_id11, 'Pharmacy pickup', -34.50, 0, NOW(), NOW());

INSERT INTO transactions (account_id, transaction_journal_id, description, amount, identifier, created_at, updated_at)
VALUES ((SELECT id FROM accounts WHERE name = 'Grocery Store' LIMIT 1), :journal_id11, 'Pharmacy pickup', 34.50, 1, NOW(), NOW());

-- Display summary of created data
SELECT 'Sample Data Creation Summary:' as status;
SELECT 'Accounts created:' as item, COUNT(*) as count FROM accounts WHERE user_id = 1;
SELECT 'Categories created:' as item, COUNT(*) as count FROM categories WHERE user_id = 1;
SELECT 'Tags created:' as item, COUNT(*) as count FROM tags WHERE tag LIKE 'couple-%';
SELECT 'Transaction journals:' as item, COUNT(*) as count FROM transaction_journals WHERE user_id = 1;
SELECT 'Transactions:' as item, COUNT(*) as count FROM transactions t JOIN transaction_journals tj ON t.transaction_journal_id = tj.id WHERE tj.user_id = 1;
SELECT 'Piggy banks (goals):' as item, COUNT(*) as count FROM piggy_banks pb JOIN accounts a ON pb.account_id = a.id WHERE a.user_id = 1;

-- Show transaction breakdown by couples tags
SELECT 
    CASE 
        WHEN t.tag = 'couple-p1' THEN 'Person 1 Expenses'
        WHEN t.tag = 'couple-p2' THEN 'Person 2 Expenses'
        WHEN t.tag = 'couple-shared' THEN 'Shared Expenses'
        ELSE 'Unassigned'
    END as category,
    COUNT(ttj.transaction_journal_id) as transaction_count,
    SUM(ABS(tr.amount)) as total_amount
FROM tags t
LEFT JOIN tag_transaction_journal ttj ON t.id = ttj.tag_id
LEFT JOIN transaction_journals tj ON ttj.transaction_journal_id = tj.id
LEFT JOIN transactions tr ON tj.id = tr.transaction_journal_id AND tr.amount < 0
WHERE t.tag LIKE 'couple-%'
GROUP BY t.tag
UNION
SELECT 
    'Unassigned' as category,
    COUNT(*) as transaction_count,
    SUM(ABS(amount)) as total_amount
FROM transactions tr
JOIN transaction_journals tj ON tr.transaction_journal_id = tj.id
WHERE tj.transaction_type_id = 2 -- withdrawals only
AND tr.amount < 0
AND NOT EXISTS (
    SELECT 1 FROM tag_transaction_journal ttj 
    JOIN tags t ON ttj.tag_id = t.id 
    WHERE ttj.transaction_journal_id = tj.id 
    AND t.tag LIKE 'couple-%'
)
ORDER BY category;