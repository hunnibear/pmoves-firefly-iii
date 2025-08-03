# Modern Supabase Local Development Setup

## ✅ Recommended Approach: Use Supabase CLI

### 1. Install Supabase CLI
```powershell
# ❌ npm install -g supabase (not supported globally)

# ✅ Option 1: Via Scoop (Recommended for Windows)
# First install Scoop if you don't have it:
# Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
# irm get.scoop.sh | iex

scoop bucket add supabase https://github.com/supabase/scoop-bucket.git
scoop install supabase

# ✅ Option 2: Via npm as dev dependency (for project)
npm i supabase --save-dev
# Then use: npx supabase <command>

# ✅ Option 3: Via Chocolatey (if you have choco)
choco install supabase

# ✅ Option 4: Direct Download (Manual)
# Download latest release from: https://github.com/supabase/cli/releases/latest
# Extract to a folder in your PATH

# ✅ Option 5: Via Go (if you have Go installed)
go install github.com/supabase/cli@latest
# Then create symlink or add to PATH
```

### 1a. Quick Scoop Installation (Recommended)
If you don't have Scoop installed yet:
```powershell
# Install Scoop package manager first
Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
irm get.scoop.sh | iex

# Then install Supabase CLI
scoop bucket add supabase https://github.com/supabase/scoop-bucket.git
scoop install supabase

# Verify installation
supabase --version
```

### 2. Initialize Supabase Project
```bash
# Navigate to your project
cd c:\Users\russe\Documents\GitHub\pmoves-firefly-iii

# Initialize Supabase
supabase init

# Start local development stack
supabase start
```

### 3. CLI Provides Complete Stack
When you run `supabase start`, you get:
- ✅ PostgreSQL Database (with pgvector)
- ✅ Auth Service (GoTrue)
- ✅ REST API (PostgREST)
- ✅ Realtime
- ✅ Storage
- ✅ Edge Functions Runtime
- ✅ Supabase Studio (Dashboard)
- ✅ Kong API Gateway
- ✅ pgAdmin (Database Admin)

### 4. Default Local URLs
```
Supabase Studio: http://localhost:54323
API URL: http://localhost:54321
GraphQL URL: http://localhost:54321/graphql/v1
S3 Storage URL: http://localhost:54321/storage/v1/s3
DB URL: postgresql://postgres:postgres@localhost:54322/postgres
Inbucket URL: http://localhost:54324
```

### 5. Environment Variables for Firefly III
Update your `.env.local`:
```env
# Supabase Local Development (CLI)
SUPABASE_URL=http://localhost:54321
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZS1kZW1vIiwicm9sZSI6ImFub24iLCJleHAiOjE5ODM4MTI5OTZ9.CRXP1A7WOeoJeXxjNni43kdQwgnWNReilDMblYTn_I0
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZS1kZW1vIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImV4cCI6MTk4MzgxMjk5Nn0.EGIM96RAZx35lJzdJsyH-qQwv8Hdp7fsn3W0YpN81IU

# Database Connection (for Firefly III)
DB_HOST=localhost
DB_PORT=54322
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

## 🤖 Modern AI Integration

### 1. Enable Vector Extensions
```sql
-- Enable in Supabase Studio or via migration
create extension if not exists vector with schema extensions;
create extension if not exists pgmq; -- For AI job queues
create extension if not exists pg_net with schema extensions; -- For HTTP requests
create extension if not exists pg_cron; -- For scheduled tasks
```

### 2. Create AI-Ready Tables
```sql
-- Example: Financial transactions with AI embeddings
create table transactions (
  id bigint primary key generated always as identity,
  description text not null,
  amount decimal(10,2),
  category text,
  embedding vector(384), -- For semantic search
  created_at timestamp with time zone default now()
);

-- Create index for fast vector search
create index on transactions using hnsw (embedding vector_cosine_ops);
```

### 3. Auto-Embedding with Triggers
```sql
-- Trigger function to auto-generate embeddings
create or replace function generate_transaction_embedding()
returns trigger
language plpgsql
security definer
as $$
begin
  -- Queue embedding generation job
  perform pgmq.send(
    queue_name => 'embedding_jobs',
    msg => jsonb_build_object(
      'id', NEW.id,
      'table', 'transactions',
      'content', NEW.description
    )
  );
  return NEW;
end;
$$;

-- Create trigger
create trigger on_transaction_insert
  after insert on transactions
  for each row
  execute function generate_transaction_embedding();
```

## 🎯 Benefits of CLI vs Custom Docker

| Feature | CLI Approach | Custom Docker |
|---------|-------------|---------------|
| Setup Time | ⚡ 2 minutes | 🐌 30+ minutes |
| Updates | ✅ `supabase start` | ❌ Manual rebuild |
| AI Features | ✅ Built-in | ❌ Manual setup |
| Auth Setup | ✅ Pre-configured | ❌ Complex setup |
| Debugging | ✅ Integrated logs | ❌ Multiple containers |
| Documentation | ✅ Official support | ❌ Community only |

## 🚀 Next Steps

1. **Stop current Docker setup**:
   ```bash
   docker-compose -f docker-compose.supabase.yml down -v
   ```

2. **Install Supabase CLI** (choose one method above)

3. **Initialize and start**:
   ```bash
   supabase init
   supabase start
   ```

4. **Update Firefly III config** to use new ports (54321, 54322)

5. **Access Supabase Studio** at http://localhost:54323
