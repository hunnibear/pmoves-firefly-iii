# Firefly III Database Setup Guide

## Overview

This guide documents the critical database setup requirements for Firefly III integration, based on lessons learned during the couples functionality migration.

## Critical Database Schema Requirements

### 1. Soft Delete Columns

Firefly III uses Laravel's soft delete functionality extensively. The following tables MUST have `deleted_at TIMESTAMP NULL` columns:

```sql
-- Core tables requiring soft deletes
ALTER TABLE accounts ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE transaction_journals ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE transaction_currencies ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE transaction_types ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE tags ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE budgets ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE piggy_banks ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE rules ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE rule_groups ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;

-- Additional tables that may need soft deletes
ALTER TABLE attachments ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE budget_limits ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE link_types ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE notes ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE object_groups ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE piggy_bank_events ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE piggy_bank_repetitions ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE recurrences ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE rule_actions ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE rule_triggers ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
ALTER TABLE webhooks ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;
```

### 2. PostgreSQL Reserved Keywords

When using PostgreSQL, certain column names are reserved keywords and must be quoted:

```sql
-- The "order" column in transaction_journals must be quoted
ALTER TABLE transaction_journals ADD COLUMN IF NOT EXISTS "order" INTEGER DEFAULT 0;
```

### 3. Essential Missing Columns

Common columns that are often missed in custom schemas:

```sql
-- Transaction journals order column (CRITICAL)
ALTER TABLE transaction_journals ADD COLUMN IF NOT EXISTS "order" INTEGER DEFAULT 0;

-- User group relationships (handled by user creation process)
-- These are typically created by Laravel events, not manual SQL
```

## User Group System

### Critical Understanding

**The user group system is NOT part of normal Firefly III Docker setup.**

User groups are automatically created when users register through the web interface via Laravel events:

1. User registers → `RegisteredUser` event fired
2. Event handler creates user group (named after email)
3. Assigns "owner" role to user
4. Creates group membership relationship

### Manual User Group Creation (Development Only)

If you need to manually create users for development:

```sql
-- Create user group
INSERT INTO user_groups (id, title, created_at, updated_at) 
VALUES (1, 'user@example.com', NOW(), NOW());

-- Get owner role ID
SELECT id FROM user_roles WHERE title = 'owner';

-- Create group membership (use role ID from above)
INSERT INTO group_memberships (user_id, user_group_id, user_role_id, created_at, updated_at)
VALUES (1, 1, [ROLE_ID], NOW(), NOW());

-- Update user with group ID
UPDATE users SET user_group_id = 1 WHERE id = 1;
```

## Database Migration Best Practices

### 1. Use Official Firefly III Migrations

Always start with the official Firefly III migrations:

```bash
docker exec firefly_iii_core php artisan migrate
docker exec firefly_iii_core php artisan db:seed
```

### 2. Add Custom Tables After Core Setup

Only add custom tables (couples, AI features) after the core Firefly III schema is complete.

### 3. Follow Laravel Conventions

- Use `created_at` and `updated_at` timestamps
- Follow foreign key naming conventions
- Use soft deletes where appropriate

## Common Error Prevention

### Error: "User has no user group"

**Cause**: User was created manually without triggering Laravel events.

**Solution**: Create proper user group relationships or use proper user registration.

### Error: "Column [table].[column] does not exist"

**Cause**: Missing columns required by Firefly III, often `deleted_at` or `order`.

**Solution**: Add missing columns following the schema requirements above.

### Error: PostgreSQL syntax errors with "order"

**Cause**: `order` is a reserved keyword in PostgreSQL.

**Solution**: Quote the column name: `"order"`.

## Verification Commands

Test your database setup:

```sql
-- Check for missing deleted_at columns
SELECT table_name, column_name 
FROM information_schema.columns 
WHERE table_schema = 'public' 
AND column_name = 'deleted_at';

-- Verify user group setup
SELECT u.email, ug.title as group_name, ur.title as role_name 
FROM users u 
JOIN group_memberships gm ON u.id = gm.user_id 
JOIN user_groups ug ON gm.user_group_id = ug.id 
JOIN user_roles ur ON gm.user_role_id = ur.id;

-- Check transaction_journals has order column
SELECT column_name 
FROM information_schema.columns 
WHERE table_name = 'transaction_journals' 
AND column_name = 'order';
```

## Success Criteria

A properly configured database should:

1. ✅ Allow Firefly III to start without errors
2. ✅ Support user authentication and group membership
3. ✅ Handle soft deletes on all required tables
4. ✅ Support transaction journal operations with ordering
5. ✅ Enable couples and AI features without schema conflicts

## References

- [Firefly III Official Documentation](https://docs.firefly-iii.org/)
- [Laravel Soft Deletes Documentation](https://laravel.com/docs/eloquent#soft-deleting)
- [PostgreSQL Reserved Keywords](https://www.postgresql.org/docs/current/sql-keywords-appendix.html)
