-- Firefly III AI Dashboard Schema
-- Apply this via Supabase Studio at http://localhost:54323

-- Create accounts table
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

-- Create categories table
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

-- Create transactions table
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
    ai_category VARCHAR(255),
    ai_confidence_score DECIMAL(3,2),
    is_ai_processed BOOLEAN DEFAULT false,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_date ON transactions(date DESC);
CREATE INDEX IF NOT EXISTS idx_accounts_user_id ON accounts(user_id);
CREATE INDEX IF NOT EXISTS idx_categories_user_id ON categories(user_id);

-- Enable Row Level Security
ALTER TABLE accounts ENABLE ROW LEVEL SECURITY;
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE transactions ENABLE ROW LEVEL SECURITY;

-- Create RLS policies (will fail if they exist - that's OK)
DO $
BEGIN
    -- Accounts policies
    BEGIN
        CREATE POLICY "Users can manage own accounts" ON accounts FOR ALL USING (auth.uid() = user_id);
    EXCEPTION WHEN duplicate_object THEN
        NULL;
    END;
    
    -- Categories policies
    BEGIN
        CREATE POLICY "Users can manage own categories" ON categories FOR ALL USING (auth.uid() = user_id);
    EXCEPTION WHEN duplicate_object THEN
        NULL;
    END;
    
    -- Transactions policies
    BEGIN
        CREATE POLICY "Users can manage own transactions" ON transactions FOR ALL USING (auth.uid() = user_id);
    EXCEPTION WHEN duplicate_object THEN
        NULL;
    END;
END
$;

-- Insert default categories
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

-- Insert sample accounts
INSERT INTO accounts (user_id, name, type, balance, currency_code) VALUES
    ('00000000-0000-0000-0000-000000000000', 'Checking Account', 'asset', 2500.00, 'USD'),
    ('00000000-0000-0000-0000-000000000000', 'Savings Account', 'asset', 15000.00, 'USD'),
    ('00000000-0000-0000-0000-000000000000', 'Credit Card', 'liability', -1200.00, 'USD')
ON CONFLICT (id) DO NOTHING;
