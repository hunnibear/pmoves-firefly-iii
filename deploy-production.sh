#!/bin/bash

# Firefly III Production Deployment Script
# This script helps deploy Firefly III to production with VPS Supabase

set -e

echo "🚀 Firefly III Production Deployment"
echo "===================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
check_docker() {
    print_status "Checking Docker installation..."
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        print_error "Docker is not running. Please start Docker first."
        exit 1
    fi
    
    print_success "Docker is running"
}

# Check if docker-compose is available
check_docker_compose() {
    print_status "Checking Docker Compose installation..."
    if command -v docker-compose &> /dev/null; then
        DOCKER_COMPOSE="docker-compose"
    elif docker compose version &> /dev/null; then
        DOCKER_COMPOSE="docker compose"
    else
        print_error "Docker Compose is not available. Please install Docker Compose."
        exit 1
    fi
    print_success "Docker Compose is available: $DOCKER_COMPOSE"
}

# Setup production environment file
setup_production_env() {
    print_status "Setting up production environment file..."
    
    if [ ! -f .env.production ]; then
        if [ -f .env.production.example ]; then
            cp .env.production.example .env.production
            print_success "Created .env.production from example"
            print_warning "⚠️  IMPORTANT: Please review and update .env.production with your production values!"
        else
            print_error ".env.production.example not found. Please create it first."
            exit 1
        fi
    else
        print_warning ".env.production already exists. Please ensure it's properly configured."
    fi
    
    # Check critical production settings
    check_production_config
}

# Check production configuration
check_production_config() {
    print_status "Checking production configuration..."
    
    local errors=0
    
    # Check for example values that need to be changed
    if grep -q "SomeRandomStringOf32CharsExactly" .env.production; then
        print_error "APP_KEY still contains example value. Generate a secure key!"
        ((errors++))
    fi
    
    if grep -q "your-domain.com" .env.production; then
        print_error "Domain still contains example value. Update APP_URL and COOKIE_DOMAIN!"
        ((errors++))
    fi
    
    if grep -q "your-secure-32-character-cron-token" .env.production; then
        print_error "STATIC_CRON_TOKEN still contains example value. Generate a secure token!"
        ((errors++))
    fi
    
    if grep -q "mail@example.com" .env.production; then
        print_warning "Email still contains example value. Update SITE_OWNER!"
    fi
    
    if [ $errors -gt 0 ]; then
        print_error "Found $errors critical configuration errors. Please fix before deploying."
        exit 1
    fi
    
    print_success "Production configuration check passed"
}

# Setup SSL certificates
setup_ssl() {
    print_status "Setting up SSL certificates..."
    
    mkdir -p ssl
    
    if [ ! -f ssl/cert.pem ] || [ ! -f ssl/key.pem ]; then
        print_warning "SSL certificates not found. You have several options:"
        echo "1. Use Let's Encrypt (recommended for production)"
        echo "2. Upload your own certificates to ./ssl/ directory"
        echo "3. Generate self-signed certificates (development only)"
        echo ""
        read -p "Choose option (1-3): " ssl_option
        
        case $ssl_option in
            1)
                setup_letsencrypt
                ;;
            2)
                print_status "Please upload your certificates:"
                print_status "  - Certificate: ./ssl/cert.pem"
                print_status "  - Private key: ./ssl/key.pem"
                read -p "Press Enter when certificates are uploaded..."
                ;;
            3)
                generate_self_signed_cert
                ;;
            *)
                print_error "Invalid option selected"
                exit 1
                ;;
        esac
    else
        print_success "SSL certificates found"
    fi
}

# Setup Let's Encrypt certificates
setup_letsencrypt() {
    print_status "Setting up Let's Encrypt certificates..."
    
    read -p "Enter your domain name: " domain
    read -p "Enter your email address: " email
    
    # Install certbot if not available
    if ! command -v certbot &> /dev/null; then
        print_status "Installing certbot..."
        if command -v apt-get &> /dev/null; then
            sudo apt-get update && sudo apt-get install -y certbot
        elif command -v yum &> /dev/null; then
            sudo yum install -y certbot
        else
            print_error "Please install certbot manually"
            exit 1
        fi
    fi
    
    # Generate certificates
    print_status "Generating Let's Encrypt certificates..."
    sudo certbot certonly --standalone -d "$domain" --email "$email" --agree-tos --non-interactive
    
    # Copy certificates
    sudo cp "/etc/letsencrypt/live/$domain/fullchain.pem" ssl/cert.pem
    sudo cp "/etc/letsencrypt/live/$domain/privkey.pem" ssl/key.pem
    sudo chown $(whoami):$(whoami) ssl/cert.pem ssl/key.pem
    
    print_success "Let's Encrypt certificates installed"
}

# Generate self-signed certificates (development only)
generate_self_signed_cert() {
    print_warning "Generating self-signed certificates (NOT for production use!)"
    
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout ssl/key.pem \
        -out ssl/cert.pem \
        -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
    
    print_success "Self-signed certificates generated"
}

# Create necessary directories
create_directories() {
    print_status "Creating necessary directories..."
    
    mkdir -p logs
    mkdir -p backups
    
    print_success "Directories created"
}

# Build production images
build_images() {
    print_status "Building production Docker images..."
    
    # Build custom Firefly III image with AI integration
    docker build -f Dockerfile.production -t firefly-iii-ai:production \
        --build-arg BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ') \
        --build-arg VERSION=$(git describe --tags --always 2>/dev/null || echo "latest") \
        --build-arg REVISION=$(git rev-parse HEAD 2>/dev/null || echo "unknown") \
        .
    
    print_success "Production images built successfully"
}

# Pull required images
pull_images() {
    print_status "Pulling required Docker images..."
    
    $DOCKER_COMPOSE -f docker-compose.production.yml pull
    
    print_success "Images pulled successfully"
}

# Test database connection
test_database_connection() {
    print_status "Testing database connection..."
    
    # Extract database connection details from .env.production
    DB_HOST=$(grep "^DB_HOST=" .env.production | cut -d'=' -f2)
    DB_PORT=$(grep "^DB_PORT=" .env.production | cut -d'=' -f2)
    DB_DATABASE=$(grep "^DB_DATABASE=" .env.production | cut -d'=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" .env.production | cut -d'=' -f2)
    
    print_status "Testing connection to $DB_HOST:$DB_PORT/$DB_DATABASE as $DB_USERNAME"
    
    # This is a basic test - in production you might want more sophisticated testing
    if command -v nc &> /dev/null; then
        if nc -z "$DB_HOST" "$DB_PORT"; then
            print_success "Database connection test passed"
        else
            print_error "Cannot connect to database. Please check your Supabase configuration."
            exit 1
        fi
    else
        print_warning "netcat not available. Skipping database connection test."
    fi
}

# Deploy services
deploy_services() {
    print_status "Deploying production services..."
    
    # Stop existing services if running
    $DOCKER_COMPOSE -f docker-compose.production.yml down
    
    # Start services
    $DOCKER_COMPOSE -f docker-compose.production.yml up -d
    
    print_success "Services deployed successfully"
}

# Wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."
    
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -k -s https://localhost/health &> /dev/null; then
            print_success "Firefly III is ready!"
            break
        fi
        
        if [ $attempt -eq $max_attempts ]; then
            print_error "Firefly III failed to start after $max_attempts attempts"
            print_status "Check logs with: $DOCKER_COMPOSE -f docker-compose.production.yml logs"
            exit 1
        fi
        
        print_status "Attempt $attempt/$max_attempts - waiting for Firefly III..."
        sleep 10
        ((attempt++))
    done
}

# Run database migrations
run_migrations() {
    print_status "Running database migrations..."
    
    $DOCKER_COMPOSE -f docker-compose.production.yml exec app php artisan migrate --force
    
    print_success "Database migrations completed"
}

# Setup monitoring and logging
setup_monitoring() {
    print_status "Setting up monitoring and logging..."
    
    # Setup log rotation
    cat > /etc/logrotate.d/firefly-iii << EOF
/var/lib/docker/containers/*/*.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 0644 root root
    postrotate
        docker kill -s USR1 \$(docker ps -q) 2>/dev/null || true
    endscript
}
EOF
    
    print_success "Monitoring and logging configured"
}

# Create backup script
create_backup_script() {
    print_status "Creating backup script..."
    
    cat > backup-firefly.sh << 'EOF'
#!/bin/bash
# Firefly III Backup Script

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="./backups"
COMPOSE_FILE="docker-compose.production.yml"

echo "Starting Firefly III backup - $DATE"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup database
echo "Backing up database..."
docker-compose -f "$COMPOSE_FILE" exec -T app php artisan firefly-iii:export-data "$BACKUP_DIR/firefly-export-$DATE.json"

# Backup uploads
echo "Backing up uploads..."
docker cp $(docker-compose -f "$COMPOSE_FILE" ps -q app):/var/www/html/storage/upload "$BACKUP_DIR/uploads-$DATE"

# Backup configuration
echo "Backing up configuration..."
cp .env.production "$BACKUP_DIR/env-$DATE"

# Create tarball
echo "Creating backup archive..."
tar -czf "$BACKUP_DIR/firefly-backup-$DATE.tar.gz" -C "$BACKUP_DIR" \
    "firefly-export-$DATE.json" \
    "uploads-$DATE" \
    "env-$DATE"

# Cleanup temporary files
rm -rf "$BACKUP_DIR/uploads-$DATE"
rm "$BACKUP_DIR/firefly-export-$DATE.json"
rm "$BACKUP_DIR/env-$DATE"

echo "Backup completed: $BACKUP_DIR/firefly-backup-$DATE.tar.gz"

# Keep only last 7 backups
find "$BACKUP_DIR" -name "firefly-backup-*.tar.gz" -type f -mtime +7 -delete
EOF
    
    chmod +x backup-firefly.sh
    print_success "Backup script created: ./backup-firefly.sh"
}

# Show final information
show_final_info() {
    echo ""
    echo "🎉 Firefly III Production Deployment Complete!"
    echo "============================================="
    echo ""
    echo "🌐 Access your application at: $(grep "^APP_URL=" .env.production | cut -d'=' -f2)"
    echo ""
    echo "📝 Important commands:"
    echo "  View logs:       $DOCKER_COMPOSE -f docker-compose.production.yml logs -f"
    echo "  Stop services:   $DOCKER_COMPOSE -f docker-compose.production.yml down"
    echo "  Restart:         $DOCKER_COMPOSE -f docker-compose.production.yml restart"
    echo "  Backup:          ./backup-firefly.sh"
    echo "  Shell access:    $DOCKER_COMPOSE -f docker-compose.production.yml exec app bash"
    echo ""
    echo "🔧 Configuration files:"
    echo "  Environment:     .env.production"
    echo "  Compose:         docker-compose.production.yml"
    echo "  Nginx:           nginx.conf"
    echo "  SSL certs:       ./ssl/"
    echo ""
    echo "⚠️  Post-deployment checklist:"
    echo "  □ Test application functionality"
    echo "  □ Verify SSL certificate"
    echo "  □ Setup automated backups"
    echo "  □ Configure monitoring alerts"
    echo "  □ Update DNS records"
    echo "  □ Setup firewall rules"
    echo ""
    print_success "Production deployment successful! 🚀"
}

# Main execution
main() {
    echo "Starting production deployment process..."
    
    check_docker
    check_docker_compose
    setup_production_env
    setup_ssl
    create_directories
    test_database_connection
    build_images
    pull_images
    deploy_services
    wait_for_services
    run_migrations
    setup_monitoring
    create_backup_script
    
    show_final_info
}

# Handle script arguments
if [[ "$1" == "--help" ]] || [[ "$1" == "-h" ]]; then
    echo "Firefly III Production Deployment Script"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --help, -h       Show this help message"
    echo ""
    echo "Prerequisites:"
    echo "  - Docker and Docker Compose installed"
    echo "  - .env.production.example file configured"
    echo "  - VPS Supabase instance running and accessible"
    echo "  - Domain name pointing to this server (for SSL)"
    echo ""
    exit 0
fi

# Run main function
main "$@"
