#!/bin/bash
set -e

echo "=== Firefly III AI Container Starting ==="

# Test AI environment
echo "Testing AI environment..."
/opt/ai-env/bin/python /var/www/html/ai-scripts/test_langextract.py

# Test Ollama connectivity (with retries)
echo "Waiting for Ollama to be ready..."
for i in {1..30}; do
    if curl -s http://ollama:11434/api/version > /dev/null; then
        echo "✓ Ollama is ready!"
        break
    fi
    echo "Waiting for Ollama... (attempt $i/30)"
    sleep 2
done

# Ensure proper permissions
chown -R www-data:www-data /var/www/html/storage

echo "=== AI Environment Ready ==="

# Execute the original entrypoint
exec /usr/local/bin/entrypoint.sh "$@"