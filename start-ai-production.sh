#!/bin/bash
# Production Docker Startup Script for Firefly III with AI
# This script sets up and starts the complete AI-enabled environment

set -e

echo "=== Firefly III AI Production Startup ==="

# Check for NVIDIA runtime
if command -v nvidia-smi &> /dev/null; then
    echo "✓ NVIDIA GPU detected:"
    nvidia-smi --query-gpu=name,memory.total --format=csv,noheader
else
    echo "⚠ Warning: No NVIDIA GPU detected. AI processing will use CPU only."
fi

# Check Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: docker-compose not found. Please install Docker Compose."
    exit 1
fi

# Create necessary directories
mkdir -p storage/logs
mkdir -p storage/ai-temp
mkdir -p storage/langextract
mkdir -p storage/obsidian
mkdir -p storage/output

# Set proper permissions
chmod 755 storage/logs
chmod 755 storage/ai-temp
chmod 755 storage/langextract

echo "✓ Directories created and permissions set"

# Start the environment
echo "Starting Firefly III AI environment..."
docker-compose -f docker-compose.ai.yml up -d

echo "✓ Services starting..."

# Wait for core services
echo "Waiting for database to be ready..."
sleep 10

# Check service health
echo "Checking service status..."
docker-compose -f docker-compose.ai.yml ps

echo ""
echo "=== Startup Complete ==="
echo "Access Firefly III at: http://localhost:8080"
echo "Ollama API available at: http://localhost:11434"
echo ""
echo "To check logs:"
echo "  docker-compose -f docker-compose.ai.yml logs -f"
echo ""
echo "To stop all services:"
echo "  docker-compose -f docker-compose.ai.yml down"
echo ""
echo "To test AI functionality:"
echo "  curl -X POST http://localhost:8080/api/couples/upload-receipt -F \"receipt=@test.jpg\" -H \"Authorization: Bearer YOUR_TOKEN\""