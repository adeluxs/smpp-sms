# SMPP Platform

Production-ready SMPP SMS platform with Laravel control plane and Python SMPP engine.

## One-Time Server Setup (AlmaLinux/RHEL)

**Required system packages (install once before deployment):**

```bash
# 1. Install packages
sudo dnf install -y \
    epel-release \
    nginx \
    python311 \
    python311-pip \
    python311-devel \
    php82-php-fpm \
    php82-php-cli \
    php82-php-pgsql \
    php82-php-redis \
    php82-php-mbstring \
    postgresql15-server \
    postgresql15-contrib \
    redis \
    rabbitmq-server \
    certbot \
    git

# 2. Enable services
sudo postgresql-setup --initdb
sudo systemctl enable --now postgresql redis rabbitmq-server

# 3. Configure RabbitMQ
sudo rabbitmqctl add_user smpp smpp_password
sudo rabbitmqctl add_vhost /smpp
sudo rabbitmqctl set_permissions -p /smpp smpp ".*" ".*" ".*"
sudo rabbitmqctl enable_feature_flags

# 4. Configure Redis (optional password)
# Edit /etc/redis.conf: requirepass your_redis_password

# 5. Configure PostgreSQL
sudo -u postgres psql -c "ALTER USER smpp PASSWORD 'smpp_password';"
sudo -u postgres psql -c "CREATE DATABASE smpp_platform OWNER smpp;"
```

**System packages explained:**
- `python311` - Python runtime for SMPP engine
- `php82-php-fpm` - PHP processor for Laravel
- `postgresql15-server` - Database
- `redis` - Cache and rate limiting
- `rabbitmq-server` - Message queue
- `certbot` - SSL certificates
- `nginx` - Web server proxy

## Quick Start (Local Development with Docker)

```bash
# Start all services
docker-compose up -d

# Access points
Web Admin:   http://localhost
API:         http://localhost/api/v1
RabbitMQ UI: http://localhost:15672 (smpp/smpp_password)
Metrics:     http://localhost:9090
Grafana:     http://localhost:3000 (admin/admin)
SMPP:        localhost:2775
```

## Quick Start (Local without Docker)

Requires: PHP 8.2+, Python 3.11+, PostgreSQL, Redis, RabbitMQ

```bash
# 1. Install PHP dependencies
cd laravel
composer install

# 2. Setup environment
cp .env.example .env
# Edit .env with your database credentials

# 3. Install Python dependencies
cd ../smpp-engine
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install -e .

# 4. Run migrations
cd ../laravel
php artisan migrate

# 5. Start services
# Terminal 1: Laravel
php artisan serve --port=8000

# Terminal 2: Queue workers
php artisan queue:work

# Terminal 3: SMPP Engine
cd ../smpp-engine
python -m smpp_engine

# Terminal 4: Mock provider (for testing)
cd ../smpp-mock
python -m smpp_mock --port 2776
```

## Production Deployment

### Production (.env)
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sms.yourdomain.com
```

## Architecture

```
Laravel (Control Plane)       Python SMPP Engine
├── Admin UI                  ├── SMPP Server (2775)
├── REST API                          ├── Session Management
├── Queue Workers             ├── Message Handler
└── Database                ├── Provider Connector
                          ├── DLR Processor
                          └── Metrics (8000)
```

## Database Schema

- `tenants` - Multi-tenant organization
- `smpp_clients` - SMPP credentials and limits
- `providers` - Upstream SMS providers (SMPP or HTTP)
- `routes` - Routing rules and priorities
- `wallets` - Prepaid/postpaid billing
- `messages` - All SMS messages
- `delivery_receipts` - DLR tracking
- `audit_logs` - System audit trail

## API Endpoints

### Public
- GET `/health` - Health check

### Client API (requires Bearer token)
- POST `/api/v1/send` - Send SMS
- GET `/api/v1/messages/{id}` - Get message status
- GET `/api/v1/reports/messages` - Message list
- GET `/api/v1/reports/summary` - Status summary

### Admin (requires web login)
- GET `/admin/clients` - SMPP client management
- GET `/admin/providers` - Provider configuration
- GET `/admin/routes` - Routing configuration

## Testing

```bash
# Unit tests
cd smpp-engine && pytest tests/test_unit.py -v

# E2E test
./scripts/test-e2e.sh

# Laravel tests
cd ../laravel && php artisan test
```

## Git-Based Deployment (Production)

**First deployment only (after one-time server setup):**
```bash
# 1. Create deploy user
sudo useradd -r -m -d /opt/smpp-platform smpp

# 2. Clone repo
sudo -u smpp -H bash <<'EOF'
cd /opt
git clone git@github.com:your-org/smpp-platform.git smpp-platform
EOF

# 3. Install dependencies
cd /opt/smpp-platform/laravel
composer install --no-dev --optimize-autoloader

# 4. Setup Python
cd /opt/smpp-platform/smpp-engine
python3 -m venv venv
./venv/bin/pip install -e . --no-dev

# 5. Copy systemd services
sudo cp smpp-engine/smpp-engine.service /etc/systemd/system/
sudo cp laravel/laravel-worker.service /etc/systemd/system/
sudo systemctl daemon-reload

# 6. Configure environment
sudo -u smpp cp .env.production /opt/smpp-platform/laravel/.env
sudo chmod 600 /opt/smpp-platform/laravel/.env

# 7. Run migrations
cd /opt/smpp-platform/laravel
sudo -u smpp php artisan migrate --force
```

**GitHub Actions (`.github/workflows/deploy.yml`):**
Tests → SSH → `/opt/smpp-platform/scripts/deploy.sh`

During initial setup, copy deploy script to correct location:

## Backup/Restore (Production)

```bash
# Manual backup
./scripts/backup.sh

# Restore
./scripts/restore.sh backup_20240101.sql.gz
```