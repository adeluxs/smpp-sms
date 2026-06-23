# Rollback Script

#!/bin/bash
set -e

echo "Rolling back to previous release..."

# Get previous commit
PREVIOUS=$(git rev-parse HEAD~1)

# Reset to previous
git reset --hard "$PREVIOUS"

# Re-install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations rollback
php artisan migrate:rollback --force

# Restart services
sudo systemctl restart laravel-worker
sudo systemctl restart smpp-engine

echo "Rollback complete"