# Firefly III AI Environment - Production Setup

This document describes the complete AI-enabled Firefly III environment with NVIDIA GPU support for Ollama.

## Architecture Overview

The AI environment consists of several interconnected services:

### Core Services
- **Firefly III App** (`app`): Main financial management application with AI capabilities
- **Ollama** (`ollama`): Local LLM inference server with GPU acceleration
- **PostgreSQL** (`firefly_iii_db`): Primary database
- **Redis** (`redis`): Cache and session storage
- **Nginx** (`nginx`): Reverse proxy and web server

### AI Services
- **AI Worker** (`ai-worker`): Background job processor for AI tasks
- **Cron** (`cron`): Scheduled task runner
- **Ollama Init** (`ollama-init`): Model loading and initialization

## GPU Requirements

### NVIDIA GPU Support
- NVIDIA Docker runtime must be installed
- Compatible NVIDIA drivers
- CUDA-capable GPU recommended for optimal performance

### Installation Commands
```bash
# Install NVIDIA Docker runtime
curl -s -L https://nvidia.github.io/nvidia-docker/gpgkey | sudo apt-key add -
distribution=$(. /etc/os-release;echo $ID$VERSION_ID)
curl -s -L https://nvidia.github.io/nvidia-docker/$distribution/nvidia-docker.list | sudo tee /etc/apt/sources.list.d/nvidia-docker.list
sudo apt-get update && sudo apt-get install -y nvidia-docker2
sudo systemctl restart docker
```

## Model Configuration

The environment is pre-configured with optimized Ollama models:

### Receipt Processing Model
- **Model**: `gemma3:270m`
- **Context Length**: 32,768 tokens
- **Max Tokens**: 8,192
- **Temperature**: 0.2 (precise)
- **Use Case**: Receipt text extraction and categorization

### Chat Model
- **Model**: `gemma3:12b`
- **Context Length**: 131,072 tokens
- **Max Tokens**: 4,096
- **Temperature**: 0.7 (conversational)
- **Use Case**: User interactions and general queries

### Analysis Model
- **Model**: `mistral-small3.2:24b`
- **Context Length**: 131,072 tokens
- **Max Tokens**: 8,192
- **Temperature**: 0.3 (analytical)
- **Use Case**: Financial analysis and insights

## Quick Start

### 1. Prerequisites Check
```powershell
# Check GPU availability
nvidia-smi

# Check Docker Compose
docker-compose --version

# Check Docker runtime
docker info | grep -i nvidia
```

### 2. Start Environment
```powershell
# Windows PowerShell
.\start-ai-production.ps1

# Or Linux/macOS
./start-ai-production.sh
```

### 3. Manual Start (Alternative)
```bash
# Start all services
docker-compose -f docker-compose.ai.yml up -d

# Check status
docker-compose -f docker-compose.ai.yml ps

# View logs
docker-compose -f docker-compose.ai.yml logs -f
```

## Service Endpoints

### Public Endpoints
- **Firefly III**: http://localhost:8080
- **Ollama API**: http://localhost:11434

### Internal Endpoints (Docker network)
- **App**: http://app:8080
- **Ollama**: http://ollama:11434
- **Database**: postgresql://firefly_iii_db:5432
- **Redis**: redis://redis:6379

## Environment Configuration

### Production Environment Files
- `.env.docker`: Docker-specific overrides
- `.env`: Base configuration (used as fallback)

### Key Environment Variables
```bash
# AI Configuration
OLLAMA_URL=http://ollama:11434
AI_DEFAULT_PROVIDER=ollama

# Model Settings
AI_RECEIPT_MODEL=gemma3:270m
AI_CHAT_MODEL=gemma3:12b
AI_ANALYSIS_MODEL=mistral-small3.2:24b

# Database
DB_HOST=firefly_iii_db
DB_DATABASE=firefly
DB_USERNAME=firefly
DB_PASSWORD=secret_firefly_password

# Redis
REDIS_HOST=firefly_iii_redis
```

## Testing AI Functionality

### 1. Health Check
```bash
# Check all services
docker-compose -f docker-compose.ai.yml exec app php artisan tinker
# Test Ollama connectivity
curl http://localhost:11434/api/version
```

### 2. Upload Receipt Test
```bash
# Test receipt processing endpoint
curl -X POST http://localhost:8080/api/couples/upload-receipt \
  -F "receipt=@test-receipt.jpg" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 3. Python AI Environment Test
```bash
# Test Python environment
docker-compose -f docker-compose.ai.yml exec app /opt/ai-env/bin/python /var/www/html/ai-scripts/test_langextract.py
```

## Volume Management

### Persistent Volumes
- `firefly_iii_upload`: User uploads and receipts
- `firefly_iii_db`: Database files
- `ollama_data`: Model storage and cache
- `ai_temp`: Temporary AI processing files
- `langextract_data`: LangExtract processing data

### Backup Commands
```bash
# Backup database
docker-compose -f docker-compose.ai.yml exec firefly_iii_db pg_dump -U firefly firefly > backup.sql

# Backup uploads
docker cp $(docker-compose -f docker-compose.ai.yml ps -q app):/var/www/html/storage/upload ./backup-uploads
```

## Monitoring and Logs

### View Service Logs
```bash
# All services
docker-compose -f docker-compose.ai.yml logs -f

# Specific service
docker-compose -f docker-compose.ai.yml logs -f ollama
docker-compose -f docker-compose.ai.yml logs -f app
docker-compose -f docker-compose.ai.yml logs -f ai-worker
```

### Monitor GPU Usage
```bash
# Real-time GPU monitoring
nvidia-smi -l 1

# GPU utilization by container
docker stats
```

### Check Model Loading
```bash
# List loaded models
curl http://localhost:11434/api/tags

# Check model status
docker-compose -f docker-compose.ai.yml logs ollama-init
```

## Troubleshooting

### Common Issues

#### 1. GPU Not Recognized
```bash
# Check NVIDIA runtime
docker info | grep nvidia

# Test GPU access
docker run --rm --gpus all nvidia/cuda:11.0-base nvidia-smi
```

#### 2. Models Not Loading
```bash
# Check Ollama service
docker-compose -f docker-compose.ai.yml logs ollama

# Manually pull models
docker-compose -f docker-compose.ai.yml exec ollama ollama pull gemma3:270m
```

#### 3. AI Processing Fails
```bash
# Check AI worker logs
docker-compose -f docker-compose.ai.yml logs ai-worker

# Test Python environment
docker-compose -f docker-compose.ai.yml exec app /opt/ai-env/bin/python --version
```

#### 4. Performance Issues
```bash
# Check resource usage
docker stats

# Monitor GPU usage
nvidia-smi

# Check disk space
df -h
docker system df
```

### Performance Optimization

#### GPU Memory Management
```bash
# Set Ollama GPU memory limit (in docker-compose.ai.yml)
OLLAMA_GPU_MEM_FRACTION=0.8
```

#### Model Optimization
- Use smaller models for testing
- Implement model unloading for memory management
- Cache frequently used model responses

## Maintenance

### Update Models
```bash
# Pull latest model versions
docker-compose -f docker-compose.ai.yml exec ollama ollama pull gemma3:270m
docker-compose -f docker-compose.ai.yml exec ollama ollama pull gemma3:12b
docker-compose -f docker-compose.ai.yml exec ollama ollama pull mistral-small3.2:24b
```

### Clean Up
```bash
# Remove unused Docker resources
docker system prune -f

# Remove unused volumes
docker volume prune -f

# Restart services
docker-compose -f docker-compose.ai.yml restart
```

### Scaling
```bash
# Scale AI workers
docker-compose -f docker-compose.ai.yml up -d --scale ai-worker=4
```

## Security Considerations

### Network Security
- All services communicate on internal Docker networks
- Only necessary ports are exposed
- Database and Redis are not publicly accessible

### API Security
- Use strong API tokens
- Implement rate limiting
- Monitor for unusual activity

### Data Protection
- Regular backups of database and uploads
- Secure storage of sensitive files
- Encryption at rest for production deployments

## Development vs Production

### Development Mode
```bash
# Use lighter models for development
AI_RECEIPT_MODEL=gemma3:270m  # Smaller model
AI_CHAT_MODEL=gemma3:270m     # Same small model for testing
```

### Production Mode
```bash
# Use optimized models for production
AI_RECEIPT_MODEL=gemma3:270m       # Fast for receipts
AI_CHAT_MODEL=gemma3:12b           # Better for conversations
AI_ANALYSIS_MODEL=mistral-small3.2:24b  # Most capable for analysis
```

This production environment provides a robust, scalable AI-enabled Firefly III deployment with GPU acceleration for optimal performance.