-- Firefly III AI Dashboard Database Schema for Supabase
-- This schema creates the necessary tables and functions for the AI-powered financial dashboard

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS vector;

-- Create accounts table
CREATE TABLE IF NOT EXISTS accounts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'asset', -- asset, liability, expense, revenue
    balance DECIMAL(15,2) DEFAULT 0.00,
    currency_code CHAR(3) DEFAULT 'USD',
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create categories table with AI support
CREATE TABLE IF NOT EXISTS categories (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    parent_id UUID REFERENCES categories(id) ON DELETE SET NULL,
    color VARCHAR(7) DEFAULT '#6366f1', -- Hex color code
    icon VARCHAR(50) DEFAULT 'fa-folder',
    description TEXT,
    -- AI Features
    description_embedding VECTOR(384), -- For semantic search
    keywords TEXT[], -- Associated keywords for better matching
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create transactions table with AI features
CREATE TABLE IF NOT EXISTS transactions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    account_id UUID REFERENCES accounts(id) ON DELETE CASCADE,
    category_id UUID REFERENCES categories(id) ON DELETE SET NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency_code CHAR(3) DEFAULT 'USD',
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    type VARCHAR(20) NOT NULL DEFAULT 'expense', -- income, expense, transfer
    
    -- AI Features
    description_embedding VECTOR(384), -- For semantic search
    ai_category VARCHAR(255), -- AI-suggested category
    ai_confidence_score DECIMAL(3,2), -- Confidence in AI suggestion (0.00-1.00)
    anomaly_score DECIMAL(3,2), -- Spending anomaly detection (0.00-1.00)
    is_ai_processed BOOLEAN DEFAULT false,
    
    -- Metadata
    notes TEXT,
    tags TEXT[],
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create budgets table
CREATE TABLE IF NOT EXISTS budgets (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    category_id UUID REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    period VARCHAR(20) DEFAULT 'monthly', -- weekly, monthly, yearly
    start_date DATE NOT NULL,
    end_date DATE,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create AI insights table
CREATE TABLE IF NOT EXISTS ai_insights (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    insight_type VARCHAR(50) NOT NULL, -- spending_pattern, budget_recommendation, anomaly_detection
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    confidence_score DECIMAL(3,2),
    is_read BOOLEAN DEFAULT false,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create AI conversations table for chat history
CREATE TABLE IF NOT EXISTS ai_conversations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    model_used VARCHAR(50),
    context JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create financial goals table
CREATE TABLE IF NOT EXISTS financial_goals (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) DEFAULT 0.00,
    target_date DATE,
    category VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active', -- active, completed, paused
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_date ON transactions(date DESC);
CREATE INDEX IF NOT EXISTS idx_transactions_category_id ON transactions(category_id);
CREATE INDEX IF NOT EXISTS idx_transactions_account_id ON transactions(account_id);
CREATE INDEX IF NOT EXISTS idx_accounts_user_id ON accounts(user_id);
CREATE INDEX IF NOT EXISTS idx_categories_user_id ON categories(user_id);
CREATE INDEX IF NOT EXISTS idx_budgets_user_id ON budgets(user_id);
CREATE INDEX IF NOT EXISTS idx_ai_insights_user_id ON ai_insights(user_id);

-- Create vector indexes for AI features (if using pgvector)
CREATE INDEX IF NOT EXISTS idx_transactions_embedding ON transactions USING hnsw (description_embedding vector_cosine_ops);
CREATE INDEX IF NOT EXISTS idx_categories_embedding ON categories USING hnsw (description_embedding vector_cosine_ops);

-- Updated at trigger function
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply updated_at triggers to relevant tables
CREATE TRIGGER update_accounts_updated_at BEFORE UPDATE ON accounts
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_categories_updated_at BEFORE UPDATE ON categories
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_transactions_updated_at BEFORE UPDATE ON transactions
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_budgets_updated_at BEFORE UPDATE ON budgets
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_financial_goals_updated_at BEFORE UPDATE ON financial_goals
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Function to find similar categories using vector similarity
CREATE OR REPLACE FUNCTION find_similar_categories(
    query_embedding VECTOR(384),
    similarity_threshold FLOAT DEFAULT 0.7,
    match_count INT DEFAULT 5,
    target_user_id UUID DEFAULT NULL
)
RETURNS TABLE (
    id UUID,
    name TEXT,
    similarity FLOAT
)
LANGUAGE SQL STABLE
AS $$
    SELECT
        c.id,
        c.name,
        1 - (c.description_embedding <=> query_embedding) AS similarity
    FROM categories c
    WHERE c.description_embedding IS NOT NULL
        AND (target_user_id IS NULL OR c.user_id = target_user_id)
        AND 1 - (c.description_embedding <=> query_embedding) > similarity_threshold
    ORDER BY c.description_embedding <=> query_embedding
    LIMIT match_count;
$$;

-- Function to get spending summary for dashboard
CREATE OR REPLACE FUNCTION get_spending_summary(
    target_user_id UUID,
    months INT DEFAULT 3
)
RETURNS TABLE (
    category_name TEXT,
    total_amount DECIMAL,
    transaction_count BIGINT,
    avg_amount DECIMAL,
    percentage DECIMAL
)
LANGUAGE SQL STABLE
AS $$
    WITH spending_data AS (
        SELECT
            COALESCE(c.name, 'Uncategorized') AS category_name,
            SUM(ABS(t.amount)) AS total_amount,
            COUNT(t.id) AS transaction_count,
            AVG(ABS(t.amount)) AS avg_amount
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = target_user_id
            AND t.date >= CURRENT_DATE - INTERVAL '1 month' * months
            AND t.amount < 0 -- Only expenses
        GROUP BY c.name
    ),
    total_spending AS (
        SELECT SUM(total_amount) AS total FROM spending_data
    )
    SELECT
        sd.category_name,
        sd.total_amount,
        sd.transaction_count,
        sd.avg_amount,
        ROUND((sd.total_amount / ts.total * 100), 2) AS percentage
    FROM spending_data sd
    CROSS JOIN total_spending ts
    ORDER BY sd.total_amount DESC;
$$;

-- Function to detect spending anomalies
CREATE OR REPLACE FUNCTION detect_spending_anomalies(
    target_user_id UUID,
    days_back INT DEFAULT 30,
    threshold_multiplier DECIMAL DEFAULT 2.0
)
RETURNS TABLE (
    transaction_id UUID,
    description TEXT,
    amount DECIMAL,
    date DATE,
    anomaly_score DECIMAL,
    reason TEXT
)
LANGUAGE SQL STABLE
AS $$
    WITH category_stats AS (
        SELECT
            category_id,
            AVG(ABS(amount)) AS avg_amount,
            STDDEV(ABS(amount)) AS stddev_amount
        FROM transactions
        WHERE user_id = target_user_id
            AND date >= CURRENT_DATE - INTERVAL '1 day' * days_back * 3 -- Use 3x period for baseline
            AND amount < 0 -- Only expenses
        GROUP BY category_id
        HAVING COUNT(*) >= 3 -- Need at least 3 transactions for meaningful stats
    )
    SELECT
        t.id,
        t.description,
        t.amount,
        t.date,
        ROUND((ABS(t.amount) - cs.avg_amount) / NULLIF(cs.stddev_amount, 0), 2) AS anomaly_score,
        CASE
            WHEN ABS(t.amount) > cs.avg_amount + (cs.stddev_amount * threshold_multiplier)
            THEN 'Amount significantly higher than usual for this category'
            ELSE 'Normal spending pattern'
        END AS reason
    FROM transactions t
    JOIN category_stats cs ON t.category_id = cs.category_id
    WHERE t.user_id = target_user_id
        AND t.date >= CURRENT_DATE - INTERVAL '1 day' * days_back
        AND t.amount < 0 -- Only expenses
        AND ABS(t.amount) > cs.avg_amount + (cs.stddev_amount * threshold_multiplier)
    ORDER BY anomaly_score DESC;
$$;

-- Function to get account balances and trends
CREATE OR REPLACE FUNCTION get_account_summary(target_user_id UUID)
RETURNS TABLE (
    account_id UUID,
    account_name TEXT,
    current_balance DECIMAL,
    balance_change_30d DECIMAL,
    transaction_count_30d BIGINT
)
LANGUAGE SQL STABLE
AS $$
    SELECT
        a.id,
        a.name,
        a.balance,
        COALESCE(balance_changes.change_30d, 0) AS balance_change_30d,
        COALESCE(transaction_counts.count_30d, 0) AS transaction_count_30d
    FROM accounts a
    LEFT JOIN (
        SELECT
            account_id,
            SUM(amount) AS change_30d
        FROM transactions
        WHERE user_id = target_user_id
            AND date >= CURRENT_DATE - INTERVAL '30 days'
        GROUP BY account_id
    ) balance_changes ON a.id = balance_changes.account_id
    LEFT JOIN (
        SELECT
            account_id,
            COUNT(*) AS count_30d
        FROM transactions
        WHERE user_id = target_user_id
            AND date >= CURRENT_DATE - INTERVAL '30 days'
        GROUP BY account_id
    ) transaction_counts ON a.id = transaction_counts.account_id
    WHERE a.user_id = target_user_id
        AND a.active = true
    ORDER BY a.name;
$$;

-- Row Level Security (RLS) policies
ALTER TABLE accounts ENABLE ROW LEVEL SECURITY;
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE transactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE budgets ENABLE ROW LEVEL SECURITY;
ALTER TABLE ai_insights ENABLE ROW LEVEL SECURITY;
ALTER TABLE ai_conversations ENABLE ROW LEVEL SECURITY;
ALTER TABLE financial_goals ENABLE ROW LEVEL SECURITY;

-- RLS policies for accounts
CREATE POLICY "Users can view own accounts" ON accounts
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own accounts" ON accounts
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own accounts" ON accounts
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own accounts" ON accounts
    FOR DELETE USING (auth.uid() = user_id);

-- RLS policies for categories
CREATE POLICY "Users can view own categories" ON categories
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own categories" ON categories
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own categories" ON categories
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own categories" ON categories
    FOR DELETE USING (auth.uid() = user_id);

-- RLS policies for transactions
CREATE POLICY "Users can view own transactions" ON transactions
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own transactions" ON transactions
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own transactions" ON transactions
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own transactions" ON transactions
    FOR DELETE USING (auth.uid() = user_id);

-- RLS policies for budgets
CREATE POLICY "Users can view own budgets" ON budgets
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own budgets" ON budgets
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own budgets" ON budgets
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own budgets" ON budgets
    FOR DELETE USING (auth.uid() = user_id);

-- RLS policies for AI insights
CREATE POLICY "Users can view own ai_insights" ON ai_insights
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own ai_insights" ON ai_insights
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own ai_insights" ON ai_insights
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own ai_insights" ON ai_insights
    FOR DELETE USING (auth.uid() = user_id);

-- RLS policies for AI conversations
CREATE POLICY "Users can view own ai_conversations" ON ai_conversations
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own ai_conversations" ON ai_conversations
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own ai_conversations" ON ai_conversations
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own ai_conversations" ON ai_conversations
    FOR DELETE USING (auth.uid() = user_id);

-- RLS policies for financial goals
CREATE POLICY "Users can view own financial_goals" ON financial_goals
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own financial_goals" ON financial_goals
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own financial_goals" ON financial_goals
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own financial_goals" ON financial_goals
    FOR DELETE USING (auth.uid() = user_id);

-- Insert default categories for new users
INSERT INTO categories (user_id, name, color, icon, description) VALUES
    ('00000000-0000-0000-0000-000000000000', 'Food & Dining', '#ef4444', 'fa-utensils', 'Restaurants, groceries, and food expenses'),
    ('00000000-0000-0000-0000-000000000000', 'Transportation', '#3b82f6', 'fa-car', 'Gas, public transport, car maintenance'),
    ('00000000-0000-0000-0000-000000000000', 'Entertainment', '#8b5cf6', 'fa-film', 'Movies, games, hobbies, and fun activities'),
    ('00000000-0000-0000-0000-000000000000', 'Bills & Utilities', '#f59e0b', 'fa-file-invoice-dollar', 'Electricity, internet, phone, rent'),
    ('00000000-0000-0000-0000-000000000000', 'Shopping', '#ec4899', 'fa-shopping-bag', 'Clothing, electronics, general shopping'),
    ('00000000-0000-0000-0000-000000000000', 'Healthcare', '#10b981', 'fa-heartbeat', 'Medical expenses, pharmacy, health insurance'),
    ('00000000-0000-0000-0000-000000000000', 'Income', '#22c55e', 'fa-money-bill-wave', 'Salary, bonuses, investments, other income'),
    ('00000000-0000-0000-0000-000000000000', 'Other', '#6b7280', 'fa-question', 'Miscellaneous expenses');

-- Insert sample accounts for demo
INSERT INTO accounts (user_id, name, type, balance, currency_code) VALUES
    ('00000000-0000-0000-0000-000000000000', 'Checking Account', 'asset', 2500.00, 'USD'),
    ('00000000-0000-0000-0000-000000000000', 'Savings Account', 'asset', 15000.00, 'USD'),
    ('00000000-0000-0000-0000-000000000000', 'Credit Card', 'liability', -1200.00, 'USD');

-- Function to create tables if they don't exist (for the dashboard to call)
CREATE OR REPLACE FUNCTION create_accounts_table_if_not_exists()
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
BEGIN
    -- This function is a placeholder for the JavaScript to call
    -- The tables are already created above
    RETURN TRUE;
END;
$$;

CREATE OR REPLACE FUNCTION create_transactions_table_if_not_exists()
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
BEGIN
    -- This function is a placeholder for the JavaScript to call
    -- The tables are already created above
    RETURN TRUE;
END;
$$;

CREATE OR REPLACE FUNCTION create_categories_table_if_not_exists()
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
BEGIN
    -- This function is a placeholder for the JavaScript to call
    -- The tables are already created above
    RETURN TRUE;
END;
$$;

-- Grant necessary permissions
GRANT USAGE ON SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL TABLES IN SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL FUNCTIONS IN SCHEMA public TO anon, authenticated;

-- Add comments for documentation
COMMENT ON TABLE accounts IS 'Financial accounts (checking, savings, credit cards, etc.)';
COMMENT ON TABLE categories IS 'Transaction categories with AI embedding support';
COMMENT ON TABLE transactions IS 'Financial transactions with AI categorization features';
COMMENT ON TABLE budgets IS 'Budget planning and tracking';
COMMENT ON TABLE ai_insights IS 'AI-generated financial insights and recommendations';
COMMENT ON TABLE ai_conversations IS 'Chat history with AI financial assistant';
COMMENT ON TABLE financial_goals IS 'User-defined financial goals and progress tracking';

COMMENT ON FUNCTION find_similar_categories IS 'Find categories similar to a given embedding vector using cosine similarity';
COMMENT ON FUNCTION get_spending_summary IS 'Get spending breakdown by category for dashboard';
COMMENT ON FUNCTION detect_spending_anomalies IS 'Detect unusual spending patterns using statistical analysis';
COMMENT ON FUNCTION get_account_summary IS 'Get account balances and recent activity summary';
