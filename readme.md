# P-Moves Budget App - Docker Setup with Local Supabase

A couples budget planning application built on top of Firefly III personal finance management software, using Docker and local Supabase for complete containerization.

## Project Overview

This project extends Firefly III with a custom "Couples Budget Planner" that includes:
- **Individual & Shared Expense Tracking**: Separate columns for each partner and shared expenses
- **Real-time Collaboration**: Partners can work on the same budget simultaneously
- **Custom API Endpoints**: 
  - `GET /api/v1/couples/state` - Load budget state
  - `POST /api/v1/couples/transactions` - Create transactions
  - `PUT /api/v1/couples/transactions/{id}` - Update transactions
  - `DELETE /api/v1/couples/transactions/{id}` - Delete transactions
  - `GET /api/v1/couples/users` - List partner users
  - `POST /api/v1/couples/partner` - Save partner selection
- **Integration with Firefly III**: Uses existing user system, accounts, and transaction structure

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx Proxy   │    │  Firefly III    │    │   PostgreSQL    │
│   (Port 80)     │◄──►│   Application   │◄──►│   (Supabase)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                       ┌─────────────────┐
                       │   Cron Service  │
                       │  (Scheduled     │
                       │   Tasks)        │
                       └─────────────────┘
```

## Prerequisites

### System Requirements
- **Docker**: Version 20.0+ with Docker Compose V2
- **Node.js**: Version 16+ (for Supabase CLI)
- **Operating System**: Windows 10+, macOS 10.15+, or Linux
- **Memory**: Minimum 4GB RAM recommended
- **Storage**: At least 5GB free space

### Required Software Installation

#### 1. Install Docker Desktop
- **Windows**: Download from [Docker Desktop for Windows](https://docs.docker.com/desktop/windows/install/)
- **macOS**: Download from [Docker Desktop for Mac](https://docs.docker.com/desktop/mac/install/)
- **Linux**: Follow [Docker Engine installation guide](https://docs.docker.com/engine/install/)

#### 2. Install Node.js and Supabase CLI

```powershell
# Install Node.js from https://nodejs.org/ or using Chocolatey
choco install nodejs

# Install Supabase CLI as dev dependency (recommended method)
npm i supabase --save-dev

# Verify installations
docker --version
node --version
npx supabase --version
```

## Setup Instructions

### Step 1: Clone and Prepare the Repository

```powershell
# Clone the repository
git clone https://github.com/hunnibear/pmoves-budgapp.git
cd pmoves-budgapp

# Verify project structure
ls -la
```

### Step 2: Initialize Supabase

```powershell
# Initialize Supabase in the project directory
npx supabase init

# This creates a supabase/ directory with:
# - config.toml (Supabase configuration)
# - seed.sql (Initial data)
# - migrations/ (Database schema changes)
```

### Step 3: Configure Environment Variables

The project uses a `.env` file for configuration. Key settings for Supabase integration:

```env
# Application Settings
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:SomeRandomStringOf32CharsExactly==
SITE_OWNER=mail@example.com

# Timezone Configuration
TZ=Europe/Amsterdam

# Database Configuration (Supabase)
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=54322
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Security
STATIC_CRON_TOKEN=YourExactly32CharacterTokenHere12
```

#### Generate Secure Keys

```powershell
# Generate APP_KEY (Laravel)
# Use online generator or PowerShell:
$key = -join ((65..90) + (97..122) + (48..57) | Get-Random -Count 32 | % {[char]$_})
echo "APP_KEY=base64:$([Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($key)))"

# Generate STATIC_CRON_TOKEN (exactly 32 characters)
$cronToken = -join ((65..90) + (97..122) + (48..57) | Get-Random -Count 32 | % {[char]$_})
echo "STATIC_CRON_TOKEN=$cronToken"
```

### Step 4: Start Supabase

```powershell
# Start local Supabase stack
npx supabase start

# This will output connection details:
# API URL: http://localhost:54321
# DB URL: postgresql://postgres:postgres@localhost:54322/postgres
# Studio URL: http://localhost:54323
```

**Important**: Keep this terminal window open or note the output. Supabase must be running before starting Docker containers.

### Step 5: Verify Docker Compose Configuration

Ensure your `docker-compose.yml` is configured correctly:

```yaml
services:
  app:
    image: fireflyiii/core:latest
    hostname: app
    container_name: firefly_iii_core
    restart: always
    volumes:
      - firefly_iii_upload:/var/www/html/storage/upload
    env_file: .env
    networks:
      - firefly_iii
    ports:
      - "80:8080"
    depends_on: []

  cron:
    image: alpine
    restart: always
    container_name: firefly_iii_cron
    env_file: .env
    command: sh -c "
      apk add tzdata && \
      ln -fs /usr/share/zoneinfo/$$TZ /etc/localtime && \
      echo \"0 3 * * * wget -qO- http://app:8080/api/v1/cron/$$STATIC_CRON_TOKEN;echo\"
      | crontab - && \
      crond -f -L /dev/stdout"
    networks:
      - firefly_iii
    depends_on:
      - app

volumes:
  firefly_iii_upload:

networks:
  firefly_iii:
    driver: bridge
```

### Step 6: Start the Application

```powershell
# Start all Docker services
docker-compose up -d

# Verify containers are running
docker-compose ps

# View application logs
docker-compose logs -f app
```

### Step 7: Initial Application Setup

1. **Access the application**: http://localhost
2. **Complete Firefly III setup wizard**:
   - Create admin user account
   - Configure basic settings
   - Set up initial accounts (checking, savings, etc.)

3. **Access Couples Budget Planner**: http://localhost/couples
4. **Configure partner**:
   - Create a second user account in Firefly III
   - In the Couples Budget Planner, go to Settings tab
   - Select your partner from the dropdown

## Features and Usage

### Couples Budget Planner Features

#### 1. **Three-Column Budget Layout**
- **Unassigned**: New expenses before categorization
- **Person 1**: Individual expenses for the primary user
- **Person 2**: Individual expenses for the partner
- **Shared**: Joint expenses with configurable split ratios

#### 2. **Drag-and-Drop Interface**
- Move expenses between categories by dragging
- Automatic tag assignment (`couple-p1`, `couple-p2`, `couple-shared`)
- Real-time budget recalculation

#### 3. **Expense Management**
- Add expenses with description and amount
- Edit existing expenses inline
- Delete expenses with confirmation
- Preset amount buttons ($25, $50, $100, $250)

#### 4. **Goals Integration**
- Linked to Firefly III's Piggy Bank system
- Add/remove savings goals
- Track progress toward financial objectives

#### 5. **Partner Collaboration**
- Select partner from existing Firefly III users
- Separate income and expense tracking
- Shared expense splitting (50/50, income-based, or custom)

### API Endpoints

The application extends Firefly III with custom API endpoints:

```
GET    /api/v1/couples/state        # Load current budget state
POST   /api/v1/couples/transactions # Create new transaction
PUT    /api/v1/couples/transactions/{id} # Update transaction
DELETE /api/v1/couples/transactions/{id} # Delete transaction
PUT    /api/v1/couples/transactions/{id}/tag # Update transaction category
GET    /api/v1/couples/users        # List available partner users
POST   /api/v1/couples/partner      # Save partner selection
POST   /api/v1/couples/goals        # Create savings goal
DELETE /api/v1/couples/goals/{id}   # Delete savings goal
```

## Database Schema Integration

### Custom Tags
- `couple-p1`: Person 1's individual expenses
- `couple-p2`: Person 2's individual expenses  
- `couple-shared`: Shared expenses

### Firefly III Integration
- **Accounts**: Uses existing asset accounts for income calculation
- **Categories**: Standard Firefly III expense categories
- **Transactions**: Creates proper double-entry transactions
- **Piggy Banks**: Goals are stored as piggy banks
- **User Groups**: Partner selection respects user group boundaries

## Troubleshooting

### Common Issues

#### 1. **Supabase Connection Failed**

**Symptoms**: Database connection errors, app won't start

**Solutions**:
```powershell
# Check Supabase status
npx supabase status

# Restart Supabase if needed
npx supabase stop
npx supabase start

# Verify connection details match .env file
```

#### 2. **Port 80 Already in Use**

**Symptoms**: Docker fails to bind to port 80

**Solutions**:
```powershell
# Check what's using port 80
netstat -ano | findstr :80

# Option 1: Stop conflicting service (IIS, Apache, etc.)
# Option 2: Change port in docker-compose.yml
ports:
  - "8080:8080"  # Use port 8080 instead
```

#### 3. **APP_KEY Not Set Error**

**Symptoms**: Laravel application key error

**Solutions**:
```powershell
# Generate and set proper APP_KEY in .env file
# Ensure it's base64 encoded and exactly 32 characters
```

#### 4. **Cron Service Not Working**

**Symptoms**: Scheduled tasks not running

**Solutions**:
```powershell
# Verify STATIC_CRON_TOKEN is exactly 32 characters
# Check cron container logs
docker-compose logs cron

# Ensure timezone is properly set
```

#### 5. **Couples Planner Not Loading**

**Symptoms**: /couples route returns 404 or errors

**Solutions**:
```powershell
# Verify Firefly III integration is complete
# Check that custom routes and controllers are in place
# Review app logs for PHP errors
docker-compose logs app | grep -i error
```

### Useful Commands

```powershell
# Container Management
docker-compose ps                    # List running containers
docker-compose down                  # Stop all services
docker-compose up -d                 # Start in background
docker-compose restart app           # Restart specific service
docker-compose logs -f app          # Follow logs

# Supabase Management
npx supabase status                      # Check Supabase services
npx supabase dashboard                   # Open web dashboard
npx supabase stop                        # Stop Supabase
npx supabase start                       # Start Supabase

# Database Access
npx supabase db reset --local            # Reset database
npx supabase db dump --local > backup.sql # Backup database
```

## Data Management

### Backup Procedures

#### 1. **Database Backup**
```powershell
# Create database dump
npx supabase db dump --local > firefly-backup-$(Get-Date -Format "yyyy-MM-dd").sql

# Backup with compression
npx supabase db dump --local | gzip > firefly-backup-$(Get-Date -Format "yyyy-MM-dd").sql.gz
```

#### 2. **File Upload Backup**
```powershell
# Backup uploaded files volume
docker run --rm -v firefly_iii_upload:/data -v ${PWD}:/backup alpine tar czf /backup/uploads-$(Get-Date -Format "yyyy-MM-dd").tar.gz /data
```

### Restore Procedures

#### 1. **Database Restore**
```powershell
# Stop application first
docker-compose down

# Reset and restore database
npx supabase db reset --local
cat firefly-backup-2025-08-18.sql | psql "postgresql://postgres:postgres@localhost:54322/postgres"

# Restart application
docker-compose up -d
```

#### 2. **File Upload Restore**
```powershell
# Restore uploaded files
docker run --rm -v firefly_iii_upload:/data -v ${PWD}:/backup alpine tar xzf /backup/uploads-2025-08-18.tar.gz -C /
```

## Production Deployment Considerations

### Security Hardening

1. **Environment Variables**:
   - Generate strong, unique APP_KEY and STATIC_CRON_TOKEN
   - Use secure database passwords
   - Set APP_DEBUG=false in production

2. **HTTPS Configuration**:
   - Use reverse proxy (nginx, Traefik) for SSL termination
   - Obtain SSL certificates (Let's Encrypt recommended)

3. **Database Security**:
   - Use managed PostgreSQL service for production
   - Enable connection encryption
   - Implement proper backup and monitoring

4. **Container Security**:
   - Run containers as non-root users
   - Use Docker secrets for sensitive data
   - Regularly update base images

### Performance Optimization

1. **Resource Limits**:
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          memory: 1G
          cpus: '0.5'
```

2. **Caching**:
   - Enable Redis for session storage
   - Configure application-level caching
   - Use CDN for static assets

## Development Setup

### Local Development with Hot Reload

For development work on the Couples Budget Planner:

```yaml
# docker-compose.override.yml
services:
  app:
    volumes:
      - ./firefly-iii:/var/www/html
    environment:
      APP_DEBUG: true
      APP_ENV: local
```

### Custom Code Locations

- **Controllers**: `firefly-iii/app/Http/Controllers/CouplesController.php`
- **API Controllers**: `firefly-iii/app/Api/V1/Controllers/Couples/`
- **Views**: `firefly-iii/resources/views/couples/`
- **Routes**: `firefly-iii/routes/web.php` and `firefly-iii/routes/api.php`
- **Frontend**: `firefly-iii/resources/views/couples/index.twig`

## Support and Resources

- **Firefly III Documentation**: https://docs.firefly-iii.org/
- **Supabase Documentation**: https://supabase.com/docs
- **Docker Documentation**: https://docs.docker.com/
- **Project Repository**: https://github.com/hunnibear/pmoves-budgapp

## License

This project builds upon Firefly III, which is licensed under the AGPL-3.0 License. The Couples Budget Planner extensions maintain the same license.