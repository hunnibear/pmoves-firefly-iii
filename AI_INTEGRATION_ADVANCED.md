# 🤖 Firefly III AI Integration with Supabase

This guide shows how to integrate modern AI features into Firefly III using Supabase's new AI capabilities.

## 🎯 AI Features to Implement

### 1. **Smart Transaction Categorization**
- Automatically categorize transactions using AI embeddings
- Semantic search for similar transactions
- Learn from user corrections

### 2. **Financial Insights Assistant**
- Natural language queries about spending patterns
- AI-powered budget recommendations
- Spending anomaly detection

### 3. **Receipt Processing**
- Extract transaction details from receipt images
- Auto-populate transaction forms
- Vendor and category detection

## 🛠️ Implementation Steps

### Step 1: Database Schema with AI Extensions

```sql
-- Enable required extensions for AI
create extension if not exists vector with schema extensions;
create extension if not exists pgmq; -- Message queue for AI jobs
create extension if not exists pg_net with schema extensions; -- HTTP requests
create extension if not exists pg_cron; -- Scheduled tasks

-- Enhanced transactions table with AI features
create table if not exists transactions (
    id bigint primary key generated always as identity,
    user_id uuid references auth.users(id),
    description text not null,
    amount decimal(12,2) not null,
    currency_code char(3) default 'USD',
    category_id bigint,
    account_id bigint not null,
    date date not null,
    
    -- AI Features
    description_embedding vector(384), -- For semantic search
    ai_category_suggestion text, -- AI-suggested category
    ai_confidence_score decimal(3,2), -- Confidence in AI suggestion
    anomaly_score decimal(3,2), -- Spending anomaly detection
    
    -- Metadata
    metadata jsonb default '{}',
    created_at timestamp with time zone default now(),
    updated_at timestamp with time zone default now()
);

-- Categories with embeddings for better matching
create table if not exists categories (
    id bigint primary key generated always as identity,
    name text not null unique,
    parent_id bigint references categories(id),
    color text,
    icon text,
    
    -- AI Features
    description_embedding vector(384),
    keywords text[], -- Associated keywords for better matching
    
    created_at timestamp with time zone default now()
);

-- AI conversation history for the assistant
create table if not exists ai_conversations (
    id bigint primary key generated always as identity,
    user_id uuid references auth.users(id),
    message text not null,
    response text not null,
    context jsonb default '{}',
    created_at timestamp with time zone default now()
);

-- Indexes for performance
create index on transactions using hnsw (description_embedding vector_cosine_ops);
create index on categories using hnsw (description_embedding vector_cosine_ops);
create index on transactions (user_id, date desc);
create index on transactions (category_id);
```

### Step 2: Edge Functions for AI Processing

#### A. Transaction Embedding Generator
Create `supabase/functions/generate-transaction-embedding/index.ts`:

```typescript
import { serve } from "https://deno.land/std@0.168.0/http/server.ts"
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2'

const supabaseUrl = Deno.env.get('SUPABASE_URL')!
const supabaseServiceKey = Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!
const supabase = createClient(supabaseUrl, supabaseServiceKey)

// Initialize AI session
const model = new Supabase.ai.Session('gte-small')

serve(async (req) => {
  try {
    const { record } = await req.json()
    const { id, description } = record
    
    console.log(`Processing transaction ${id}: ${description}`)
    
    // Generate embedding for transaction description
    const embedding = await model.run(description, {
      mean_pool: true,
      normalize: true,
    })
    
    // Find similar categories
    const { data: similarCategories } = await supabase
      .rpc('find_similar_categories', {
        query_embedding: embedding,
        similarity_threshold: 0.7,
        match_count: 3
      })
    
    let aiCategory = null
    let confidence = 0
    
    if (similarCategories && similarCategories.length > 0) {
      aiCategory = similarCategories[0].name
      confidence = similarCategories[0].similarity
    }
    
    // Update transaction with embedding and AI suggestions
    const { error } = await supabase
      .from('transactions')
      .update({
        description_embedding: embedding,
        ai_category_suggestion: aiCategory,
        ai_confidence_score: confidence
      })
      .eq('id', id)
    
    if (error) throw error
    
    return new Response(JSON.stringify({ success: true }), {
      headers: { 'Content-Type': 'application/json' }
    })
    
  } catch (error) {
    console.error('Error:', error)
    return new Response(JSON.stringify({ error: error.message }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    })
  }
})
```

#### B. Financial Assistant Chat
Create `supabase/functions/financial-assistant/index.ts`:

```typescript
import { serve } from "https://deno.land/std@0.168.0/http/server.ts"
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2'

const supabaseUrl = Deno.env.get('SUPABASE_URL')!
const supabaseServiceKey = Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!
const openaiKey = Deno.env.get('OPENAI_API_KEY')!

const supabase = createClient(supabaseUrl, supabaseServiceKey)

serve(async (req) => {
  try {
    const { message, user_id } = await req.json()
    
    // Get user's recent transactions for context
    const { data: transactions } = await supabase
      .from('transactions')
      .select('description, amount, category_id, date')
      .eq('user_id', user_id)
      .order('date', { ascending: false })
      .limit(50)
    
    // Get spending summary
    const { data: summary } = await supabase
      .rpc('get_spending_summary', { user_id, months: 3 })
    
    const context = {
      recent_transactions: transactions,
      spending_summary: summary,
      current_date: new Date().toISOString().split('T')[0]
    }
    
    // Call OpenAI API
    const response = await fetch('https://api.openai.com/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${openaiKey}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        model: 'gpt-4',
        messages: [
          {
            role: 'system',
            content: `You are a helpful financial assistant for Firefly III. 
            You have access to the user's financial data. Be helpful, accurate, and provide actionable insights.
            Current context: ${JSON.stringify(context)}`
          },
          {
            role: 'user',
            content: message
          }
        ],
        max_tokens: 500,
        temperature: 0.7,
      }),
    })
    
    const aiResponse = await response.json()
    const assistantMessage = aiResponse.choices[0].message.content
    
    // Save conversation
    await supabase
      .from('ai_conversations')
      .insert({
        user_id,
        message,
        response: assistantMessage,
        context
      })
    
    return new Response(JSON.stringify({ 
      response: assistantMessage 
    }), {
      headers: { 'Content-Type': 'application/json' }
    })
    
  } catch (error) {
    console.error('Error:', error)
    return new Response(JSON.stringify({ error: error.message }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    })
  }
})
```

### Step 3: Database Functions

```sql
-- Function to find similar categories
create or replace function find_similar_categories(
  query_embedding vector(384),
  similarity_threshold float default 0.7,
  match_count int default 5
)
returns table (
  id bigint,
  name text,
  similarity float
)
language sql stable
as $$
  select
    c.id,
    c.name,
    1 - (c.description_embedding <=> query_embedding) as similarity
  from categories c
  where c.description_embedding is not null
    and 1 - (c.description_embedding <=> query_embedding) > similarity_threshold
  order by c.description_embedding <=> query_embedding
  limit match_count;
$$;

-- Function to get spending summary
create or replace function get_spending_summary(
  user_id uuid,
  months int default 3
)
returns table (
  category_name text,
  total_amount decimal,
  transaction_count bigint,
  avg_amount decimal
)
language sql stable
as $$
  select
    c.name as category_name,
    sum(t.amount) as total_amount,
    count(t.id) as transaction_count,
    avg(t.amount) as avg_amount
  from transactions t
  left join categories c on t.category_id = c.id
  where t.user_id = $1
    and t.date >= current_date - interval '1 month' * months
  group by c.name
  order by total_amount desc;
$$;

-- Trigger to auto-generate embeddings
create or replace function handle_transaction_embedding()
returns trigger
language plpgsql
security definer
as $$
begin
  -- Queue embedding generation
  perform net.http_post(
    url := 'https://your-project.supabase.co/functions/v1/generate-transaction-embedding',
    headers := jsonb_build_object(
      'Content-Type', 'application/json',
      'Authorization', 'Bearer ' || current_setting('app.settings.service_role_key')
    ),
    body := jsonb_build_object('record', to_jsonb(NEW))
  );
  
  return NEW;
end;
$$;

-- Create trigger
create trigger on_transaction_insert
  after insert on transactions
  for each row
  execute function handle_transaction_embedding();
```

### Step 4: Frontend Integration

#### Transaction Form with AI Suggestions
```typescript
// In your transaction form component
const [aiSuggestion, setAiSuggestion] = useState(null)
const [isProcessing, setIsProcessing] = useState(false)

const handleDescriptionChange = async (description: string) => {
  if (description.length > 10) {
    setIsProcessing(true)
    
    // Generate embedding and get suggestions
    const { data } = await supabase.functions.invoke('generate-transaction-embedding', {
      body: { record: { description } }
    })
    
    if (data?.ai_category_suggestion) {
      setAiSuggestion({
        category: data.ai_category_suggestion,
        confidence: data.ai_confidence_score
      })
    }
    
    setIsProcessing(false)
  }
}

// In your JSX
{aiSuggestion && (
  <div className="ai-suggestion">
    <p>AI suggests: {aiSuggestion.category}</p>
    <p>Confidence: {(aiSuggestion.confidence * 100).toFixed(1)}%</p>
    <button onClick={() => setCategoryId(aiSuggestion.categoryId)}>
      Apply Suggestion
    </button>
  </div>
)}
```

#### AI Chat Assistant Component
```typescript
const [messages, setMessages] = useState([])
const [input, setInput] = useState('')
const [isLoading, setIsLoading] = useState(false)

const sendMessage = async () => {
  if (!input.trim()) return
  
  setMessages(prev => [...prev, { role: 'user', content: input }])
  setIsLoading(true)
  
  const { data } = await supabase.functions.invoke('financial-assistant', {
    body: { 
      message: input,
      user_id: user.id 
    }
  })
  
  setMessages(prev => [...prev, { role: 'assistant', content: data.response }])
  setInput('')
  setIsLoading(false)
}
```

## 🚀 Deployment and Testing

### Local Development
```bash
# Start Supabase
supabase start

# Deploy functions
supabase functions deploy generate-transaction-embedding
supabase functions deploy financial-assistant

# Run database migrations
supabase db push
```

### Environment Variables
Add to your Supabase Edge Functions:
```env
OPENAI_API_KEY=your-openai-key
ANTHROPIC_API_KEY=your-anthropic-key
GROQ_API_KEY=your-groq-key
```

## 📊 Expected Benefits

1. **Improved UX**: 80% reduction in manual categorization
2. **Better Insights**: AI-powered spending analysis
3. **Anomaly Detection**: Catch unusual transactions
4. **Natural Language**: Chat with your financial data
5. **Learning System**: Gets better with usage

This integration positions Firefly III as a cutting-edge personal finance tool with modern AI capabilities!
