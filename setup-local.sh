#!/bin/bash

# Firefly III Local Development Setup Script
# This script helps you get started with local development using Docker

set -e

echo "🔥 Firefly III Local Development Setup"
echo "======================================"

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

# Setup environment file
setup_env() {
    print_status "Setting up environment file..."
    
    if [ ! -f .env.local ]; then
        if [ -f .env.local.example ]; then
            cp .env.local.example .env.local
            print_success "Created .env.local from example"
        else
            print_error ".env.local.example not found. Please create it first."
            exit 1
        fi
    else
        print_warning ".env.local already exists. Skipping creation."
    fi
    
    # Generate APP_KEY if needed
    if grep -q "your-generated-32-char-key-here" .env.local; then
        print_status "Generating APP_KEY..."
        APP_KEY=$(openssl rand -base64 32)
        sed -i "s/your-generated-32-char-key-here/$APP_KEY/g" .env.local
        print_success "Generated APP_KEY"
    fi
    
    # Generate STATIC_CRON_TOKEN if needed
    if grep -q "your-32-character-cron-token-here" .env.local; then
        print_status "Generating STATIC_CRON_TOKEN..."
        CRON_TOKEN=$(openssl rand -hex 16)
        sed -i "s/your-32-character-cron-token-here/$CRON_TOKEN/g" .env.local
        print_success "Generated STATIC_CRON_TOKEN"
    fi
}

# Create necessary directories
create_directories() {
    print_status "Creating necessary directories..."
    
    mkdir -p ollama-data
    mkdir -p ssl
    
    print_success "Directories created"
}

# Check Supabase network
check_supabase_network() {
    print_status "Checking Supabase network..."
    
    if ! docker network ls | grep -q "supabase_network"; then
        print_warning "Supabase network not found. Creating external network..."
        docker network create supabase_network
        print_success "Created supabase_network"
    else
        print_success "Supabase network exists"
    fi
}

# Pull required images
pull_images() {
    print_status "Pulling required Docker images..."
    
    $DOCKER_COMPOSE -f docker-compose.local.yml pull
    
    print_success "Images pulled successfully"
}

# Start services
start_services() {
    print_status "Starting Firefly III services..."
    
    $DOCKER_COMPOSE -f docker-compose.local.yml up -d
    
    print_success "Services started successfully"
}

# Wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."
    
    # Wait for app to be ready
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s http://localhost:8080/health &> /dev/null; then
            print_success "Firefly III is ready!"
            break
        fi
        
        if [ $attempt -eq $max_attempts ]; then
            print_error "Firefly III failed to start after $max_attempts attempts"
            print_status "Check logs with: $DOCKER_COMPOSE -f docker-compose.local.yml logs"
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
    
    $DOCKER_COMPOSE -f docker-compose.local.yml exec app php artisan migrate --force
    
    print_success "Database migrations completed"
}

# Install Ollama models
install_ollama_models() {
    print_status "Installing Ollama models (this may take a while)..."
    
    # Wait for Ollama to be ready
    local max_attempts=10
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s http://localhost:11434/api/version &> /dev/null; then
            break
        fi
        
        if [ $attempt -eq $max_attempts ]; then
            print_warning "Ollama is not ready. Skipping model installation."
            return
        fi
        
        print_status "Waiting for Ollama to be ready... (attempt $attempt/$max_attempts)"
        sleep 10
        ((attempt++))
    done
    
    # Install recommended models
    print_status "Installing llama2 model..."
    $DOCKER_COMPOSE -f docker-compose.local.yml exec ollama ollama pull llama2:7b
    
    print_status "Installing codellama model..."
    $DOCKER_COMPOSE -f docker-compose.local.yml exec ollama ollama pull codellama:7b
    
    print_success "Ollama models installed"
}

# Show final information
show_final_info() {
    echo ""
    echo "🎉 Firefly III Local Development Setup Complete!"
    echo "=============================================="
    echo ""
    echo "📊 Firefly III: http://localhost:8080"
    echo "🤖 Ollama API: http://localhost:11434"
    echo ""
    echo "📝 Useful commands:"
    echo "  View logs:       $DOCKER_COMPOSE -f docker-compose.local.yml logs -f"
    echo "  Stop services:   $DOCKER_COMPOSE -f docker-compose.local.yml down"
    echo "  Restart:         $DOCKER_COMPOSE -f docker-compose.local.yml restart"
    echo "  Shell access:    $DOCKER_COMPOSE -f docker-compose.local.yml exec app bash"
    echo ""
    echo "🔧 Configuration:"
    echo "  Environment:     .env.local"
    echo "  Compose file:    docker-compose.local.yml"
    echo ""
    print_success "Happy coding! 🚀"
}

# Main execution
main() {
    echo "Starting setup process..."
    
    check_docker
    check_docker_compose
    setup_env
    create_directories
    check_supabase_network
    pull_images
    start_services
    wait_for_services
    run_migrations
    
    # Optional: Install Ollama models (can be skipped with --skip-ollama)
    if [[ "$1" != "--skip-ollama" ]]; then
        install_ollama_models
    fi
    
    show_final_info
}

# Handle script arguments
if [[ "$1" == "--help" ]] || [[ "$1" == "-h" ]]; then
    echo "Firefly III Local Development Setup Script"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --skip-ollama    Skip Ollama model installation"
    echo "  --help, -h       Show this help message"
    echo ""
    exit 0
fi

# Run main function
main "$@"
