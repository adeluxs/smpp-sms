# SMPP Platform

Production-ready SMPP SMS platform with Laravel control plane and Python SMPP engine.

## One-Time Server Setup (AlmaLinux/RHEL)

**Required system packages (install once before deployment):**

```bash
# For AlmaLinux 9:
sudo dnf install -y \
    epel-release \
    nginx \
    python311 python311-pip python311-devel \
    php-fpm php-cli php-pgsql php-redis php-mbstring \
    postgresql14-server postgresql14-contrib \
    redis \
    rabbitmq-server \
    certbot \
    git

# For PostgreSQL 14:
sudo dnf module disable postgresql && \
    dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-9-x86_64/pgdg-redhat-repo-latest.noarch.rpm && \
    dnf module disable postgresql && \
    dnf install -y postgresql14-server postgresql14-contrib

# Enable services
sudo systemctl enable --now postgresql redis rabbitmq-server nginx
```

**System packages explained:**
- `python311` - Python runtime for SMPP engine
- `php-fpm` - PHP processor for Laravel (any PHP 8.2+)
- `postgresql14-server` - Database (v14 or v15)
- `redis` - Cache and rate limiting
- `rabbitmq-server` - Message queue
- `certbot` - SSL certificates
- `nginx` - Web server proxy

## Quick Start (Local Development with Docker)

```bash
docker-compose up -d
```

Access: http://localhost (admin), http://localhost:2775 (SMPP)

## Quick Start (Local without Docker)

Requires: PHP 8.2+, Python 3.11+, PostgreSQL, Redis, RabbitMQ

```bash
cd laravel && composer install
cd ../smpp-engine && python -m venv venv && pip install -e .
cd ../laravel && cp .env.example .env && php artisan migrate
php artisan serve  # Laravel
python -m smpp_engine  # SMPP Engine
```

## Architecture

```
Laravel (Control Plane)       Python SMPP Engine
├── Admin UI                  ├── SMPP Server (2775)
├── REST API                  ├── Message Handler
├── Queue Workers             ├── Provider Connector
└── Database                ├── DLR Processor
                          └── Metrics (8000)
```

## Database Schema

- `tenants`, `smpp_clients`, `providers`, `routes`, `wallets`, `messages`, `delivery_receipts`, `audit_logs`

## API Endpoints

- POST `/api/v1/send` - Send SMS
- GET `/api/v1/messages/{id}` - Message status
- GET `/admin/clients` - SMPP client management

## Git-Based Deployment

**First deployment:** Install packages, clone repo, copy `scripts/deploy.sh` to server

**GitHub Actions:** Pushes to main branch → SSH → `scripts/deploy.sh`

## Backup/Restore

```bash
./scripts/backup.sh
./scripts/restore.sh backup_20240101.sql.gz
```