# Production Deployment Script

#!/bin/bash
set -e

echo "Deploying SMPP Platform..."

cd /opt/smpp-platform

# Pull latest code
git pull origin main

# Install PHP dependencies
cd laravel
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache

# Update Python deps
cd ../smpp-engine
./venv/bin/pip install -e . --no-dev

# Restart services
sudo systemctl restart laravel-worker
sudo systemctl restart smpp-engine

echo "Deployment complete"