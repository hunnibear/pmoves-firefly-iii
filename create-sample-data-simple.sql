-- Simple Sample Data Creation Script for Couples Testing
-- Run this in PostgreSQL to create realistic test data

-- Create sample accounts
INSERT INTO accounts (user_id, account_type_id, name, iban, created_at, updated_at)
SELECT 1, 4, 'Joint Checking Account', 'US1234567890123456789', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Joint Checking Account');

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
SELECT 1, 5, 'Salary Income', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE name = 'Salary Income');

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