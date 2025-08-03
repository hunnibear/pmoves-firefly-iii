# Firefly III Docker Setup

This repository contains Docker configurations for running Firefly III with AI integration capabilities, supporting both local development and production deployment with Supabase.

## 💻 System Requirements

### Windows
- **Windows 10/11** (64-bit) with latest updates
- **Docker Desktop** for Windows (with WSL2 backend recommended)
- **PowerShell 5.1+** or **Windows PowerShell 7+**
- **Git for Windows** (optional, for Git Bash)
- **4GB RAM minimum** (8GB+ recommended for AI features)
- **20GB free disk space** minimum

### Linux/macOS
- **Docker Engine** 20.10+ and **Docker Compose** 2.0+
- **Bash shell** 4.0+
- **4GB RAM minimum** (8GB+ recommended for AI features)
- **20GB free disk space** minimum

### All Platforms
- **Internet connection** for pulling Docker images and AI API access
- **Port availability**: 8080 (local), 80/443 (production)
- **Supabase instance** (local or VPS)

## 📁 File Structure

```
├── docker-compose.local.yml          # Local development with local Supabase
├── docker-compose.production.yml     # Production with VPS Supabase
├── Dockerfile.local                  # Local development image
├── Dockerfile.production            # Production optimized image
├── nginx.conf                       # Nginx configuration for production
├── .env.local.example              # Local environment template
├── .env.production.example         # Production environment template
├── setup-local.sh                  # Local setup script (Linux/macOS)
├── setup-local.ps1                 # Local setup script (Windows PowerShell)
├── deploy-production.sh            # Production deployment script (Linux/macOS)
├── deploy-production.ps1           # Production deployment script (Windows PowerShell)
├── firefly-docker.bat              # Basic Docker commands (Windows Command Prompt)
└── README-Docker.md                # This file
```

## 🚀 Quick Start

### Local Development

1. **Prerequisites**
   - Docker Desktop installed and running
   - Local Supabase instance running
   - Git repository cloned
   - **Windows**: PowerShell or Git Bash (recommended)
   - **Linux/macOS**: Terminal with bash

2. **Setup**

   #### For Linux/macOS:
   ```bash
   # Make setup script executable
   chmod +x setup-local.sh
   
   # Run the setup script
   ./setup-local.sh
   ```

   #### For Windows (PowerShell):
   ```powershell
   # Run the Windows setup script
   .\setup-local.ps1
   ```

   #### For Windows (Git Bash):
   ```bash
   # Run the bash script in Git Bash
   bash setup-local.sh
   ```

3. **Manual Setup (if needed)**

   #### Linux/macOS:
   ```bash
   # Copy environment file
   cp .env.local.example .env.local
   
   # Edit configuration
   nano .env.local
   
   # Start services
   docker-compose -f docker-compose.local.yml up -d
   ```

   #### Windows (PowerShell):
   ```powershell
   # Copy environment file
   Copy-Item .env.local.example .env.local
   
   # Edit configuration
   notepad .env.local
   
   # Start services
   docker-compose -f docker-compose.local.yml up -d
   ```

4. **Access**
   - Firefly III: http://localhost:8080
   - Ollama API: http://localhost:11434

## 📋 Quick Start Guide

### **For Local Development:**

#### Windows (PowerShell):
```powershell
# 1. Copy and configure environment
Copy-Item .env.local.example .env.local
# Edit .env.local with your settings using notepad or VS Code

# 2. Run the automated setup
.\setup-local.ps1

# 3. Access your application
# Firefly III: http://localhost:8080
# Ollama API: http://localhost:11434
```

#### Windows (Command Prompt - Basic):
```cmd
# For users who prefer Command Prompt over PowerShell
# 1. Copy environment file manually
copy .env.local.example .env.local
# Edit .env.local with notepad

# 2. Use the batch script for basic operations
firefly-docker.bat
# Choose option 1 to start local development

# 3. Access your application
# Firefly III: http://localhost:8080
```

#### Linux/macOS (Bash):
```bash
# 1. Copy and configure environment
cp .env.local.example .env.local
# Edit .env.local with your settings

# 2. Run the automated setup
chmod +x setup-local.sh
./setup-local.sh

# 3. Access your application
# Firefly III: http://localhost:8080
# Ollama API: http://localhost:11434
```

### **For Production Deployment:**

#### Windows (PowerShell as Administrator):
```powershell
# 1. Copy and configure environment
Copy-Item .env.production.example .env.production
# Edit .env.production with your production settings

# 2. Run the automated deployment
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\deploy-production.ps1

# 3. Access via your domain (after SSL setup)
```

#### Linux/macOS (Bash):
```bash
# 1. Copy and configure environment
cp .env.production.example .env.production
# Edit .env.production with your production settings

# 2. Run the automated deployment
chmod +x deploy-production.sh
./deploy-production.sh

# 3. Access via your domain (after SSL setup)
```

### Production Deployment

1. **Prerequisites**
   - Docker Desktop (Windows) or Docker Engine (Linux/macOS) installed
   - VPS Supabase instance configured
   - Domain name pointing to your server
   - SSL certificates (Let's Encrypt recommended)
   - **Windows**: PowerShell (Administrator) or WSL2
   - **Linux/macOS**: Terminal with sudo access

2. **Setup**

   #### For Linux/macOS:
   ```bash
   # Make deployment script executable
   chmod +x deploy-production.sh
   
   # Run the deployment script
   ./deploy-production.sh
   ```

   #### For Windows (PowerShell as Administrator):
   ```powershell
   # Set execution policy (if needed)
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
   
   # Run the Windows deployment script
   .\deploy-production.ps1
   ```

   #### For Windows (WSL2):
   ```bash
   # Run in Windows Subsystem for Linux
   chmod +x deploy-production.sh
   ./deploy-production.sh
   ```

3. **Manual Setup (if needed)**

   #### Linux/macOS:
   ```bash
   # Copy and configure environment
   cp .env.production.example .env.production
   nano .env.production
   
   # Setup SSL certificates
   mkdir -p ssl
   # Add your cert.pem and key.pem files
   
   # Deploy
   docker-compose -f docker-compose.production.yml up -d
   ```

   #### Windows (PowerShell):
   ```powershell
   # Copy and configure environment
   Copy-Item .env.production.example .env.production
   notepad .env.production
   
   # Setup SSL certificates
   New-Item -ItemType Directory -Force -Path ssl
   # Add your cert.pem and key.pem files to ssl folder
   
   # Deploy
   docker-compose -f docker-compose.production.yml up -d
   ```

## 🔧 Configuration

### Environment Variables

#### Core Application
- `APP_ENV`: Environment (local/production)
- `APP_DEBUG`: Debug mode (true/false)
- `APP_KEY`: 32-character encryption key
- `APP_URL`: Application URL
- `SITE_OWNER`: Your email address

#### Database (Supabase)
- `DB_CONNECTION`: pgsql
- `DB_HOST`: Supabase database host
- `DB_PORT`: Database port (5432)
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password

#### Supabase API
- `SUPABASE_URL`: Supabase project URL
- `SUPABASE_SERVICE_KEY`: Service role key
- `SUPABASE_ANON_KEY`: Anonymous key

#### AI Integration
- `GROQ_API_KEY`: Groq API key
- `OPENAI_API_KEY`: OpenAI API key
- `ANTHROPIC_API_KEY`: Anthropic API key
- `GEMINI_API_KEY`: Google Gemini API key
- `OLLAMA_BASE_URL`: Ollama server URL

### Local vs Production Differences

| Setting | Local | Production |
|---------|-------|------------|
| `APP_ENV` | local | production |
| `APP_DEBUG` | true | false |
| `APP_URL` | http://localhost:8080 | https://your-domain.com |
| `SUPABASE_URL` | http://localhost:54321 | https://your-supabase.com |
| `COOKIE_SECURE` | false | true |
| `SSL` | Not required | Required |
| `Logging` | Debug level | Warning level |

## 🐳 Docker Services

### Local Development Services

- **app**: Main Firefly III application
- **redis**: Cache and session storage
- **worker**: Background job processing
- **ollama**: Local LLM server
- **cron**: Scheduled task runner

### Production Services

- **app**: Main Firefly III application (production config)
- **redis**: Cache and session storage (password protected)
- **worker**: Background job processing (scaled)
- **cron**: Scheduled task runner
- **nginx**: Reverse proxy with SSL termination

## 📊 Monitoring and Logs

### View Logs

#### Linux/macOS:
```bash
# Local development
docker-compose -f docker-compose.local.yml logs -f [service_name]

# Production
docker-compose -f docker-compose.production.yml logs -f [service_name]
```

#### Windows (PowerShell):
```powershell
# Local development
docker-compose -f docker-compose.local.yml logs -f [service_name]

# Production
docker-compose -f docker-compose.production.yml logs -f [service_name]
```

### Service Status

#### Linux/macOS/Windows:
```bash
# Check running services
docker-compose -f docker-compose.local.yml ps

# Check resource usage
docker stats
```

### Health Checks
All services include health checks. Check status:
```bash
docker-compose -f docker-compose.production.yml ps
```

## 🔐 Security

### Production Security Features

1. **SSL/TLS**: Nginx with SSL termination
2. **Rate Limiting**: API and login endpoints protected
3. **Security Headers**: HSTS, CSP, frame options
4. **Database**: SSL connections required
5. **Redis**: Password authentication
6. **Containers**: Non-root user execution

### SSL Certificate Setup

#### Let's Encrypt (Recommended)

**Linux/macOS:**
```bash
# Automatic setup via deployment script
./deploy-production.sh

# Manual setup
certbot certonly --standalone -d your-domain.com
cp /etc/letsencrypt/live/your-domain.com/fullchain.pem ssl/cert.pem
cp /etc/letsencrypt/live/your-domain.com/privkey.pem ssl/key.pem
```

**Windows (PowerShell as Administrator):**
```powershell
# Manual setup - use win-acme or similar tool
# Download win-acme from https://www.win-acme.com/
.\wacs.exe --target manual --host your-domain.com

# Copy certificates to ssl folder
Copy-Item "C:\ProgramData\win-acme\certificates\your-domain.com\*.crt" ssl\cert.pem
Copy-Item "C:\ProgramData\win-acme\certificates\your-domain.com\*.key" ssl\key.pem
```

#### Custom Certificates

**Linux/macOS:**
```bash
# Place certificates in ssl directory
cp your-certificate.crt ssl/cert.pem
cp your-private-key.key ssl/key.pem
```

**Windows (PowerShell):**
```powershell
# Place certificates in ssl directory
Copy-Item your-certificate.crt ssl\cert.pem
Copy-Item your-private-key.key ssl\key.pem
```

## 💾 Backup and Recovery

### Automated Backup Script
The deployment script creates `backup-firefly.sh`:

```bash
# Run backup
./backup-firefly.sh

# Backups are stored in ./backups/
# Includes: database export, uploads, configuration
```

### Manual Backup

**Linux/macOS:**
```bash
# Database export
docker-compose exec app php artisan firefly-iii:export-data backup.json

# Copy uploads
docker cp container_name:/var/www/html/storage/upload ./uploads-backup

# Configuration backup
cp .env.production env-backup
```

**Windows (PowerShell):**
```powershell
# Database export
docker-compose exec app php artisan firefly-iii:export-data backup.json

# Copy uploads
docker cp container_name:/var/www/html/storage/upload ./uploads-backup

# Configuration backup
Copy-Item .env.production env-backup
```

## 🚨 Troubleshooting

### Common Issues

#### Database Connection Issues

**Linux/macOS:**
```bash
# Check Supabase connectivity
nc -z your-supabase-host 5432

# Check credentials
docker-compose exec app php artisan tinker
# Test: DB::connection()->getPdo();
```

**Windows (PowerShell):**
```powershell
# Check Supabase connectivity
Test-NetConnection -ComputerName your-supabase-host -Port 5432

# Check credentials
docker-compose exec app php artisan tinker
# Test: DB::connection()->getPdo();
```

#### SSL Certificate Issues

**Linux/macOS:**
```bash
# Check certificate validity
openssl x509 -in ssl/cert.pem -text -noout

# Test SSL connection
curl -I https://your-domain.com
```

**Windows (PowerShell):**
```powershell
# Check certificate validity (requires OpenSSL for Windows)
openssl x509 -in ssl/cert.pem -text -noout

# Test SSL connection
Invoke-WebRequest -Uri https://your-domain.com -Method HEAD
```

#### Permission Issues

**Linux/macOS:**
```bash
# Fix file permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
```

**Windows (Docker Desktop handles permissions automatically):**
```powershell
# Usually not needed on Windows Docker Desktop
# If issues persist, restart Docker Desktop
```

#### Memory Issues

**All Platforms:**
```bash
# Check container resources
docker stats

# Adjust memory limits in docker-compose files
```

### Log Analysis

**All Platforms:**
```bash
# Application errors
docker-compose logs app | grep ERROR

# Nginx access logs
docker-compose logs nginx

# Queue worker issues
docker-compose logs worker
```

**Windows (PowerShell alternative):**
```powershell
# Application errors
docker-compose logs app | Select-String "ERROR"

# Nginx access logs
docker-compose logs nginx

# Queue worker issues
docker-compose logs worker
```

## 🔄 Updates and Maintenance

### Update Application

**All Platforms:**
```bash
# Pull latest images
docker-compose pull

# Restart services
docker-compose down && docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate
```

### Clear Caches

**Linux/macOS/Windows:**
```bash
# Clear application cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Scale Workers (Production)

**All Platforms:**
```bash
# Scale queue workers
docker-compose up -d --scale worker=3
```

### Generate Secure Keys (Windows)

**PowerShell:**
```powershell
# Generate APP_KEY (32 characters)
$appKey = [System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes([System.Guid]::NewGuid().ToString().Replace("-", "").Substring(0, 32)))
Write-Host "APP_KEY=$appKey"

# Generate STATIC_CRON_TOKEN (32 characters)
Add-Type -AssemblyName System.Web
$cronToken = [System.Web.Security.Membership]::GeneratePassword(32, 0)
Write-Host "STATIC_CRON_TOKEN=$cronToken"
```

## 📝 Development

### Adding AI Features

1. **Install Python packages** in Dockerfile
2. **Add environment variables** for new APIs
3. **Create Laravel services** for AI integration
4. **Queue jobs** for background AI processing
5. **API endpoints** for frontend integration

### Custom Configuration

1. **Extend Docker images** via Dockerfile
2. **Add environment variables** in .env files
3. **Mount volumes** for persistent data
4. **Configure networks** for service communication

## 📚 References

- [Firefly III Documentation](https://docs.firefly-iii.org/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Supabase Documentation](https://supabase.com/docs)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [Let's Encrypt](https://letsencrypt.org/getting-started/)

## 🆘 Support

### Getting Help

1. **Check logs** first using the commands above
2. **Review configuration** files for typos
3. **Test network connectivity** to external services
4. **Verify permissions** on files and directories

### Windows-Specific Issues

#### PowerShell Execution Policy
```powershell
# If you get execution policy errors
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Check current policy
Get-ExecutionPolicy
```

#### Docker Desktop Issues
```powershell
# Restart Docker Desktop if containers won't start
# From PowerShell as Administrator:
Restart-Service com.docker.service

# Or restart Docker Desktop from the system tray
```

#### Path Issues
```powershell
# Use full paths if relative paths don't work
docker-compose -f (Resolve-Path docker-compose.local.yml) up -d

# Check current directory
Get-Location
```

#### File Permissions
```powershell
# Windows Docker Desktop handles most permissions automatically
# If you have issues, try restarting Docker Desktop
```

### Resources

- Firefly III GitHub Issues
- Docker Community Forums
- Supabase Discord
- Stack Overflow (tag: firefly-iii, docker-compose)
