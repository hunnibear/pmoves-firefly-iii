-- Comprehensive Firefly III + Couples + AI Database Schema
-- This script creates the full database structure for Firefly III with couples and AI features

-- Create the required roles and users for Supabase
CREATE ROLE IF NOT EXISTS anon                          NOLOGIN NOINHERIT;
CREATE ROLE IF NOT EXISTS authenticated                 NOLOGIN NOINHERIT;
CREATE ROLE IF NOT EXISTS service_role                  NOLOGIN NOINHERIT;
CREATE ROLE IF NOT EXISTS supabase_auth_admin           NOINHERIT CREATEROLE LOGIN;
CREATE ROLE IF NOT EXISTS supabase_storage_admin        NOINHERIT CREATEROLE LOGIN;
CREATE ROLE IF NOT EXISTS dashboard_user                NOINHERIT CREATEROLE LOGIN;
CREATE ROLE IF NOT EXISTS authenticator                 NOINHERIT LOGIN;

-- Set passwords for login roles
ALTER USER supabase_auth_admin PASSWORD 'your-super-secret-and-long-postgres-password';
ALTER USER supabase_storage_admin PASSWORD 'your-super-secret-and-long-postgres-password';
ALTER USER dashboard_user PASSWORD 'your-super-secret-and-long-postgres-password';
ALTER USER authenticator PASSWORD 'your-super-secret-and-long-postgres-password';

-- Grant necessary privileges
GRANT anon, authenticated, service_role TO authenticator;
GRANT anon, authenticated, service_role TO supabase_auth_admin;
GRANT anon, authenticated, service_role TO supabase_storage_admin;
GRANT anon, authenticated, service_role TO dashboard_user;

-- Allow these roles to create schemas and tables
GRANT CREATE ON DATABASE postgres TO anon;
GRANT CREATE ON DATABASE postgres TO authenticated;
GRANT CREATE ON DATABASE postgres TO service_role;
GRANT CREATE ON DATABASE postgres TO supabase_auth_admin;
GRANT CREATE ON DATABASE postgres TO supabase_storage_admin;
GRANT CREATE ON DATABASE postgres TO dashboard_user;
GRANT CREATE ON DATABASE postgres TO authenticator;

-- Grant usage on public schema
GRANT USAGE ON SCHEMA public TO anon;
GRANT USAGE ON SCHEMA public TO authenticated;
GRANT USAGE ON SCHEMA public TO service_role;

-- Grant all privileges on public schema
GRANT ALL ON SCHEMA public TO anon;
GRANT ALL ON SCHEMA public TO authenticated;
GRANT ALL ON SCHEMA public TO service_role;
GRANT ALL ON SCHEMA public TO supabase_auth_admin;
GRANT ALL ON SCHEMA public TO supabase_storage_admin;
GRANT ALL ON SCHEMA public TO dashboard_user;
GRANT ALL ON SCHEMA public TO authenticator;

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "pgjwt";

-- Create Firefly III core tables

-- Users table (Firefly III core)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    reset VARCHAR(32),
    blocked BOOLEAN DEFAULT FALSE,
    blocked_code VARCHAR(25),
    objectguid VARCHAR(36),
    domain VARCHAR(255),
    mfa_secret VARCHAR(50),
    two_factor_secret VARCHAR(16),
    two_factor_recovery_codes TEXT,
    user_group_id INTEGER
);

-- Account types table
CREATE TABLE IF NOT EXISTS account_types (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    type VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default account types
INSERT INTO account_types (type) VALUES 
    ('Default account'), ('Cash account'), ('Asset account'), ('Expense account'), 
    ('Revenue account'), ('Initial balance account'), ('Beneficiary account'), 
    ('Import account'), ('Reconciliation account'), ('Loan'), ('Debt'), ('Mortgage')
ON CONFLICT (type) DO NOTHING;

-- Accounts table (Firefly III core)
CREATE TABLE IF NOT EXISTS accounts (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    account_type_id INTEGER NOT NULL REFERENCES account_types(id),
    name VARCHAR(1024) NOT NULL,
    virtual_balance DECIMAL(32,12),
    iban VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    encrypted BOOLEAN DEFAULT FALSE,
    order_column INTEGER DEFAULT 0
);

-- Transaction types table
CREATE TABLE IF NOT EXISTS transaction_types (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    type VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default transaction types
INSERT INTO transaction_types (type) VALUES 
    ('Withdrawal'), ('Deposit'), ('Transfer'), ('Opening balance'), ('Reconciliation')
ON CONFLICT (type) DO NOTHING;

-- Transaction journals table (Firefly III core)
CREATE TABLE IF NOT EXISTS transaction_journals (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    transaction_type_id INTEGER NOT NULL REFERENCES transaction_types(id),
    bill_id INTEGER,
    transaction_currency_id INTEGER,
    description VARCHAR(1024) NOT NULL,
    date DATE NOT NULL,
    interest_date DATE,
    book_date DATE,
    process_date DATE,
    order_column INTEGER DEFAULT 0,
    tag_count INTEGER DEFAULT 0,
    encrypted BOOLEAN DEFAULT FALSE,
    completed BOOLEAN DEFAULT TRUE
);

-- Transactions table (Firefly III core)
CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    account_id INTEGER NOT NULL REFERENCES accounts(id) ON DELETE CASCADE,
    transaction_journal_id INTEGER NOT NULL REFERENCES transaction_journals(id) ON DELETE CASCADE,
    description VARCHAR(1024),
    amount DECIMAL(32,12) NOT NULL,
    foreign_amount DECIMAL(32,12),
    foreign_currency_id INTEGER,
    transaction_currency_id INTEGER,
    identifier INTEGER DEFAULT 0
);

-- Tags table (Essential for couples functionality)
CREATE TABLE IF NOT EXISTS tags (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    tag VARCHAR(1024) NOT NULL,
    description TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    zoom_level INTEGER,
    date DATE
);

-- Tag-transaction journal pivot table
CREATE TABLE IF NOT EXISTS tag_transaction_journal (
    id SERIAL PRIMARY KEY,
    tag_id INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
    transaction_journal_id INTEGER NOT NULL REFERENCES transaction_journals(id) ON DELETE CASCADE,
    UNIQUE(tag_id, transaction_journal_id)
);

-- Categories table (For transaction categorization)
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(1024) NOT NULL,
    encrypted BOOLEAN DEFAULT FALSE
);

-- Category-transaction journal pivot table
CREATE TABLE IF NOT EXISTS category_transaction_journal (
    id SERIAL PRIMARY KEY,
    category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    transaction_journal_id INTEGER NOT NULL REFERENCES transaction_journals(id) ON DELETE CASCADE,
    UNIQUE(category_id, transaction_journal_id)
);

-- Piggy banks table (For goals functionality)
CREATE TABLE IF NOT EXISTS piggy_banks (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    account_id INTEGER NOT NULL REFERENCES accounts(id) ON DELETE CASCADE,
    name VARCHAR(1024) NOT NULL,
    target_amount DECIMAL(32,12),
    start_date DATE,
    target_date DATE,
    order_column INTEGER DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    encrypted BOOLEAN DEFAULT FALSE,
    object_group_id INTEGER
);

-- Piggy bank events table
CREATE TABLE IF NOT EXISTS piggy_bank_events (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    piggy_bank_id INTEGER NOT NULL REFERENCES piggy_banks(id) ON DELETE CASCADE,
    transaction_journal_id INTEGER REFERENCES transaction_journals(id) ON DELETE SET NULL,
    amount DECIMAL(32,12) NOT NULL
);

-- Currencies table
CREATE TABLE IF NOT EXISTS transaction_currencies (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    code VARCHAR(51) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    symbol VARCHAR(51) NOT NULL,
    decimal_places INTEGER DEFAULT 2,
    enabled BOOLEAN DEFAULT TRUE
);

-- Insert default currencies
INSERT INTO transaction_currencies (code, name, symbol, decimal_places) VALUES 
    ('USD', 'US Dollar', '$', 2),
    ('EUR', 'Euro', '€', 2),
    ('GBP', 'British Pound', '£', 2),
    ('CAD', 'Canadian Dollar', 'C$', 2),
    ('AUD', 'Australian Dollar', 'A$', 2)
ON CONFLICT (code) DO NOTHING;

-- Preferences table (for user settings)
CREATE TABLE IF NOT EXISTS preferences (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(1024) NOT NULL,
    data TEXT
);

-- ==========================================
-- COUPLES-SPECIFIC ENHANCEMENTS
-- ==========================================

-- Couples profiles table (for partner management)
CREATE TABLE IF NOT EXISTS couples_profiles (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    partner_name VARCHAR(255) NOT NULL DEFAULT 'Partner',
    partner_income DECIMAL(15,2) DEFAULT 0.00,
    contribution_method VARCHAR(20) DEFAULT 'equal', -- equal, income_based, custom
    custom_person1_percentage DECIMAL(5,2) DEFAULT 50.00,
    custom_person2_percentage DECIMAL(5,2) DEFAULT 50.00,
    budget_period VARCHAR(20) DEFAULT 'monthly', -- monthly, weekly, yearly
    currency_code VARCHAR(3) DEFAULT 'USD',
    active BOOLEAN DEFAULT TRUE,
    UNIQUE(user_id)
);

-- Couples budget settings table
CREATE TABLE IF NOT EXISTS couples_budget_settings (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    person1_name VARCHAR(255) DEFAULT 'Person 1',
    person2_name VARCHAR(255) DEFAULT 'Person 2',
    shared_expenses_name VARCHAR(255) DEFAULT 'Shared',
    show_financial_health BOOLEAN DEFAULT TRUE,
    show_insights BOOLEAN DEFAULT TRUE,
    auto_categorize BOOLEAN DEFAULT FALSE,
    UNIQUE(user_id)
);

-- ==========================================
-- AI INTEGRATION TABLES
-- ==========================================

-- AI transaction analysis table
CREATE TABLE IF NOT EXISTS ai_transaction_analysis (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    transaction_id INTEGER NOT NULL REFERENCES transactions(id) ON DELETE CASCADE,
    ai_category VARCHAR(255),
    ai_confidence_score DECIMAL(3,2),
    ai_tags TEXT[], -- PostgreSQL array for multiple AI-suggested tags
    ai_insights TEXT,
    processed_by VARCHAR(50), -- 'ollama', 'openai', 'groq'
    processed_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    is_verified BOOLEAN DEFAULT FALSE,
    UNIQUE(transaction_id)
);

-- AI anomaly detection table
CREATE TABLE IF NOT EXISTS ai_anomalies (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    transaction_id INTEGER REFERENCES transactions(id) ON DELETE CASCADE,
    anomaly_type VARCHAR(50), -- 'unusual_amount', 'unusual_category', 'duplicate', 'pattern_break'
    severity VARCHAR(20) DEFAULT 'medium', -- 'low', 'medium', 'high'
    description TEXT,
    ai_confidence DECIMAL(3,2),
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'reviewed', 'dismissed'
    reviewed_at TIMESTAMP WITH TIME ZONE,
    reviewed_by INTEGER REFERENCES users(id)
);

-- AI chat sessions table
CREATE TABLE IF NOT EXISTS ai_chat_sessions (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_id VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    ai_model VARCHAR(50), -- 'ollama', 'openai', 'groq'
    total_messages INTEGER DEFAULT 0,
    last_message_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    status VARCHAR(20) DEFAULT 'active' -- 'active', 'archived', 'deleted'
);

-- AI chat messages table
CREATE TABLE IF NOT EXISTS ai_chat_messages (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    session_id INTEGER NOT NULL REFERENCES ai_chat_sessions(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL, -- 'user', 'assistant', 'system'
    content TEXT NOT NULL,
    message_order INTEGER NOT NULL,
    tokens_used INTEGER,
    processing_time_ms INTEGER
);

-- ==========================================
-- INDEXES FOR PERFORMANCE
-- ==========================================

-- Core Firefly III indexes
CREATE INDEX IF NOT EXISTS idx_accounts_user_id ON accounts(user_id);
CREATE INDEX IF NOT EXISTS idx_transaction_journals_user_id ON transaction_journals(user_id);
CREATE INDEX IF NOT EXISTS idx_transaction_journals_date ON transaction_journals(date DESC);
CREATE INDEX IF NOT EXISTS idx_transactions_account_id ON transactions(account_id);
CREATE INDEX IF NOT EXISTS idx_transactions_journal_id ON transactions(transaction_journal_id);
CREATE INDEX IF NOT EXISTS idx_tags_user_id ON tags(user_id);
CREATE INDEX IF NOT EXISTS idx_tags_tag ON tags(tag);
CREATE INDEX IF NOT EXISTS idx_categories_user_id ON categories(user_id);
CREATE INDEX IF NOT EXISTS idx_piggy_banks_account_id ON piggy_banks(account_id);

-- Couples-specific indexes
CREATE INDEX IF NOT EXISTS idx_couples_profiles_user_id ON couples_profiles(user_id);
CREATE INDEX IF NOT EXISTS idx_couples_budget_settings_user_id ON couples_budget_settings(user_id);

-- AI-specific indexes
CREATE INDEX IF NOT EXISTS idx_ai_transaction_analysis_transaction_id ON ai_transaction_analysis(transaction_id);
CREATE INDEX IF NOT EXISTS idx_ai_anomalies_user_id ON ai_anomalies(user_id);
CREATE INDEX IF NOT EXISTS idx_ai_anomalies_status ON ai_anomalies(status);
CREATE INDEX IF NOT EXISTS idx_ai_chat_sessions_user_id ON ai_chat_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_ai_chat_messages_session_id ON ai_chat_messages(session_id);

-- ==========================================
-- DEFAULT DATA
-- ==========================================

-- Insert default couples tags
INSERT INTO tags (id, user_id, tag, description) VALUES 
    (1, 1, 'couple-p1', 'Person 1 expenses'),
    (2, 1, 'couple-p2', 'Person 2 expenses'),
    (3, 1, 'couple-shared', 'Shared expenses')
ON CONFLICT (id) DO NOTHING;

-- Insert default categories for couples
INSERT INTO categories (id, user_id, name) VALUES 
    (1, 1, 'Food & Dining'),
    (2, 1, 'Transportation'),
    (3, 1, 'Entertainment'),
    (4, 1, 'Bills & Utilities'),
    (5, 1, 'Shopping'),
    (6, 1, 'Healthcare'),
    (7, 1, 'Income'),
    (8, 1, 'Other')
ON CONFLICT (id) DO NOTHING;

-- Create default user with proper password hash
INSERT INTO users (id, email, password, created_at, updated_at) VALUES 
    (1, 'cataclysmstudios@gmail.com', '$2y$12$u55oABmSDAuyqgqA4hOlFuVbb68aNaMj2Ap3QWwTx90XXJtMi/QEK', NOW(), NOW())
ON CONFLICT (email) DO UPDATE SET 
    password = EXCLUDED.password,
    updated_at = NOW();

-- Create default couples profile
INSERT INTO couples_profiles (user_id, partner_name, partner_income, contribution_method) VALUES 
    (1, 'Partner', 4000.00, 'equal')
ON CONFLICT (user_id) DO UPDATE SET 
    partner_name = EXCLUDED.partner_name,
    partner_income = EXCLUDED.partner_income,
    updated_at = NOW();

-- Create default couples budget settings
INSERT INTO couples_budget_settings (user_id, person1_name, person2_name) VALUES 
    (1, 'Person 1', 'Person 2')
ON CONFLICT (user_id) DO UPDATE SET 
    person1_name = EXCLUDED.person1_name,
    person2_name = EXCLUDED.person2_name,
    updated_at = NOW();

-- Create default asset accounts
INSERT INTO accounts (id, user_id, account_type_id, name, virtual_balance) VALUES 
    (1, 1, 3, 'Checking Account', 2500.00),
    (2, 1, 3, 'Savings Account', 15000.00)
ON CONFLICT (id) DO UPDATE SET 
    name = EXCLUDED.name,
    virtual_balance = EXCLUDED.virtual_balance,
    updated_at = NOW();

-- Create default expense accounts
INSERT INTO accounts (id, user_id, account_type_id, name) VALUES 
    (3, 1, 4, 'Cash Expenses'),
    (4, 1, 4, 'Credit Card Expenses')
ON CONFLICT (id) DO UPDATE SET 
    name = EXCLUDED.name,
    updated_at = NOW();

-- Create default revenue account
INSERT INTO accounts (id, user_id, account_type_id, name) VALUES 
    (5, 1, 5, 'Salary')
ON CONFLICT (id) DO UPDATE SET 
    name = EXCLUDED.name,
    updated_at = NOW();

-- ==========================================
-- GRANT PERMISSIONS ON ALL TABLES
-- ==========================================

-- Grant permissions on all tables to required roles
DO $permissions$
DECLARE
    table_name TEXT;
BEGIN
    FOR table_name IN 
        SELECT tablename FROM pg_tables WHERE schemaname = 'public'
    LOOP
        EXECUTE format('GRANT ALL ON TABLE public.%I TO anon', table_name);
        EXECUTE format('GRANT ALL ON TABLE public.%I TO authenticated', table_name);
        EXECUTE format('GRANT ALL ON TABLE public.%I TO service_role', table_name);
        EXECUTE format('GRANT ALL ON TABLE public.%I TO supabase_auth_admin', table_name);
        EXECUTE format('GRANT ALL ON TABLE public.%I TO supabase_storage_admin', table_name);
        EXECUTE format('GRANT ALL ON TABLE public.%I TO dashboard_user', table_name);
        EXECUTE format('GRANT ALL ON TABLE public.%I TO authenticator', table_name);
    END LOOP;
END
$permissions$;

-- Grant permissions on all sequences
DO $sequence_permissions$
DECLARE
    sequence_name TEXT;
BEGIN
    FOR sequence_name IN 
        SELECT sequencename FROM pg_sequences WHERE schemaname = 'public'
    LOOP
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO anon', sequence_name);
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO authenticated', sequence_name);
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO service_role', sequence_name);
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO supabase_auth_admin', sequence_name);
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO supabase_storage_admin', sequence_name);
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO dashboard_user', sequence_name);
        EXECUTE format('GRANT ALL ON SEQUENCE public.%I TO authenticator', sequence_name);
    END LOOP;
END
$sequence_permissions$;

-- Set default privileges for future objects
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO anon;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO authenticated;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO service_role;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO supabase_auth_admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO supabase_storage_admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO dashboard_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO authenticator;

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO anon;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO authenticated;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO service_role;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO supabase_auth_admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO supabase_storage_admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO dashboard_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO authenticator;

-- Create the auth schema for Supabase Auth
CREATE SCHEMA IF NOT EXISTS auth;

-- Grant usage on auth schema
GRANT USAGE ON SCHEMA auth TO anon;
GRANT USAGE ON SCHEMA auth TO authenticated;
GRANT USAGE ON SCHEMA auth TO service_role;
GRANT USAGE ON SCHEMA auth TO dashboard_user;

-- Grant all privileges on auth schema to auth admin
GRANT ALL ON SCHEMA auth TO supabase_auth_admin;

-- Create storage schema for Supabase Storage
CREATE SCHEMA IF NOT EXISTS storage;

-- Grant usage on storage schema  
GRANT USAGE ON SCHEMA storage TO anon;
GRANT USAGE ON SCHEMA storage TO authenticated;
GRANT USAGE ON SCHEMA storage TO service_role;
GRANT USAGE ON SCHEMA storage TO dashboard_user;

-- Grant all privileges on storage schema to storage admin
GRANT ALL ON SCHEMA storage TO supabase_storage_admin;

-- Completion message
SELECT 'Firefly III + Couples + AI database schema setup complete!' as status;
