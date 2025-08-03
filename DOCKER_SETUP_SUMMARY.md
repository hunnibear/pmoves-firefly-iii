# Firefly III Docker Configuration Summary

This document explains your current Docker setup for Firefly III with both local and production Supabase configurations.

## Current Configuration Files

### Environment Files
1. **`.env.local`** - Local development with Supabase Docker containers
2. **`.env.production`** - Production with your self-hosted Supabase instance  
3. **`fireflysetup.md`** - Your production configuration reference

### Docker Compose Files
1. **`docker-compose.yml`** - Main Firefly III application
2. **`docker-compose.supabase.yml`** - Local Supabase development stack

### Setup Scripts
1. **`setup-local.sh`** - Bash script for Linux/Mac
2. **`setup-local.ps1`** - PowerShell script for Windows

## Configuration Differences

### Local Development (`.env.local`)
```bash
# Uses local Docker containers
SUPABASE_URL=http://localhost:54321
DB_HOST=localhost
DB_PORT=54322
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Standard Supabase local development ports
# - Studio: http://localhost:8000
# - API: http://localhost:54321  
# - Database: localhost:54322
```

### Production (`.env.production`)
```bash
# Uses your self-hosted Supabase
SUPABASE_URL=https://supabasepmoves.cataclysmstudios.net
DB_HOST=supabasepmoves.cataclysmstudios.net
DB_PORT=5432
DB_USERNAME=pmovesadmin
DB_PASSWORD=fHjG159LeQTbE1Uyh3He4frtFFILzbO8

# Your production Supabase instance
```

## Key Fixes Made

### 1. **Corrected Supabase Ports**
- **Before**: `SUPABASE_URL=http://localhost:8000` (incorrect)
- **After**: `SUPABASE_URL=http://localhost:54321` (correct for local)

### 2. **Fixed Database Connection**
- **Before**: Mixed service names and localhost
- **After**: Consistent localhost with correct ports for local development

### 3. **Separated Configurations**
- Local development uses standard Supabase local ports
- Production uses your self-hosted instance

## How to Use

### For Local Development
```powershell
# Windows
.\setup-local.ps1 start

# Or manually
docker-compose -f docker-compose.supabase.yml --env-file .env.supabase up -d
docker-compose --profile local --env-file .env.local up -d
```

### For Production Deployment
```bash
# Use production environment
docker-compose --env-file .env.production up -d
```

## Service URLs

### Local Development
- **Firefly III**: http://localhost:8080
- **Supabase Studio**: http://localhost:8000  
- **Supabase API**: http://localhost:54321
- **Database Direct**: localhost:54322
- **Ollama**: http://localhost:11434
- **Text Generation**: http://localhost:8081
- **Email Testing**: http://localhost:54324

### Production
- **Firefly III**: https://your-domain.com
- **Supabase**: https://supabasepmoves.cataclysmstudios.net

## AI Integration Ready

Your setup includes:
1. **Ollama** for local LLM inference
2. **Hugging Face TGI** for transformer models
3. **Multiple AI API keys** configured
4. **Queue workers** for background AI processing
5. **Redis** for caching and sessions

## Next Steps

1. **Start Local Development**:
   ```powershell
   .\setup-local.ps1 start
   ```

2. **Initialize Database**:
   ```powershell
   .\setup-local.ps1 setup
   ```

3. **Download AI Models**:
   ```powershell
   .\setup-local.ps1 ollama
   ```

4. **Begin AI Feature Development** as outlined in `AI_INTEGRATION_DOCS.md`

## Troubleshooting

### Common Issues
1. **Port Conflicts**: Make sure ports 8000, 8080, 54321, 54322, 11434 are available
2. **Docker Networks**: Run `docker network create supabase_network` if needed
3. **Environment Files**: Ensure `.env.local` exists and has correct values

### Logs
```bash
# Check service logs
docker-compose logs -f firefly-app
docker-compose -f docker-compose.supabase.yml logs -f db
```

## Security Notes

- **Local Development**: Uses default Supabase credentials (insecure, development only)
- **Production**: Uses your actual credentials (keep secure)
- **API Keys**: Multiple AI provider keys configured
- **Database**: PostgreSQL with proper SSL in production

Your setup is now properly configured for both local development with Docker Supabase and production deployment with your self-hosted Supabase instance!
