# Phase 1: Infrastructure Foundation

## Server Topology

Single server deployment with potential for horizontal scaling:

```
Production: 1x AlmaLinux 9 server (Vultr Cloud)
- 4 vCPUs, 8GB RAM minimum
- 100GB SSD storage
- Private networking recommended
- Monthly backup snapshots
```

## OS Hardening Steps (AlmaLinux 9)

### System Updates
```bash
dnf update -y
dnf install -y dnf-automatic
systemctl enable --now dnf-automatic.timer
```

### Firewall (firewalld)
```bash
# Public ports only
firewall-cmd --permanent --add-service=ssh
firewall-cmd --permanent --add-service=https
firewall-cmd --permanent --add-port=2775/tcp  # SMPP
firewall-cmd --permanent --remove-service=dhcpv6-client
firewall-cmd --reload
```

### SELinux Configuration
```bash
# Enable enforcing mode
setenforce 1
sed -i 's/SELINUX=permissive/SELINUX=enforcing/' /etc/selinux/config

# Service-specific policies
semanage port -a -t http_port_t -p tcp 2775  # SMPP port
```

### Fail2Ban
```bash
dnf install -y fail2ban
systemctl enable --now fail2ban
```

## Package Dependencies

### Required Packages
```bash
dnf install -y \
  nginx \
  php82-php-fpm \
  php82-php-cli \
  php82-php-pgsql \
  php82-php-redis \
  php82-php-bcmath \
  php82-php-xml \
  php82-php-mbstring \
  python311 \
  python311-pip \
  python311-venv \
  redis \
  postgresql15-server \
  postgresql15-contrib \
  rabbitmq-server \
  certbot \
  certbot-nginx
```

## Service Configurations

### Nginx Configuration
`/etc/nginx/conf.d/smpp-platform.conf`
```nginx
server {
    listen 443 ssl http2;
    server_name sms.example.com;

    ssl_certificate /etc/letsencrypt/live/sms.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sms.example.com/privkey.pem;

    root /opt/smpp-platform/laravel/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /smpp-websocket {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

### PHP-FPM Configuration
`/etc/php-fpm.d/smpp-platform.conf`
```ini
[smpp-platform]
user = smpp
group = smpp
listen = /run/php-fpm/www.sock
listen.owner = nginx
listen.group = nginx
pm = ondemand
pm.max_children = 100
pm.process_idle_timeout = 10s
```

### Redis Configuration
`/etc/redis.conf`
- bind 127.0.0.1
- protected-mode yes
- requirepass ${REDIS_PASSWORD}

### PostgreSQL Configuration
`/var/lib/pgsql/15/data/postgresql.conf`
- listen_addresses = 'localhost'
- max_connections = 200

## Directory Layout

```
/opt/smpp-platform/
├── laravel/
│   ├── public/
│   ├── app/
│   ├── config/
│   ├── .env
│   └── artisan
├── smpp-engine/
│   ├── src/
│   ├── pyproject.toml
│   ├── smpp_engine.service
│   └── .env
├── smpp-mock/
│   ├── src/
│   └── pyproject.toml
├── backups/
│   ├── daily/
│   └── weekly/
└── logs/
    ├── nginx/
    ├── laravel/
    └── smpp-engine/
```

## Environment and Secret Management

### Production (.env)
```bash
# Laravel
APP_ENV=production
APP_KEY=base64:$(openssl rand -base64 32)
DB_PASSWORD=${DB_PASSWORD}
REDIS_PASSWORD=${REDIS_PASSWORD}
RABBITMQ_PASSWORD=${RABBITMQ_PASSWORD}

# SMPP Engine
SMpp_BIND_PORT=2775
SMpp_PROVIDER_HOST=provider.example.com
SMpp_PROVIDER_PASSWORD=${SMpp_PROVIDER_PASSWORD}

# All sensitive values injected via systemd EnvironmentFile or secrets manager
```

### Secrets Rotation Strategy
- Database passwords: Rotate quarterly via Vault or systemd
- SMPP provider passwords: Rotate annually with overlap period
- API keys: Rotate on-demand via admin UI

## Local Docker Compose Setup

### docker-compose.yml
```yaml
version: '3.8'
services:
  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: smpp_platform
      POSTGRES_USER: smpp
      POSTGRES_PASSWORD: smpp_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./laravel/database/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports: ["5432:5432"]

  redis:
    image: redis:7-alpine
    command: redis-server --protected-mode yes
    ports: ["6379:6379"]

  rabbitmq:
    image: rabbitmq:3-management
    environment:
      RABBITMQ_DEFAULT_USER: smpp
      RABBITMQ_DEFAULT_PASS: smpp_password
    ports: ["5672:5672", "15672:15672"]

  nginx:
    image: nginx:1.24
    ports: ["80:80", "443:443"]
    volumes:
      - ./laravel/public:/var/www/html
      - ./ docker/nginx.conf:/etc/nginx/conf.d/default.conf

volumes:
  postgres_data:
```

### Bootstrap Commands (local)
```bash
# 1. Start infrastructure
docker-compose up -d

# 2. Setup Laravel
cd laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# 3. Setup Python SMPP engine
cd ../smpp-engine
python -m venv venv
source venv/bin/activate
pip install -e .

# 4. Run SMPP mock provider
cd ../smpp-mock
pip install -e .
python -m smpp_mock --port 2776 &
```

## Health Checks

### Nginx
```bash
curl -f https://localhost/health
```

### Laravel
```bash
curl -f https://localhost/api/health
```

### SMPP Engine
```bash
telnet localhost 2775  # Port check
# Or check systemd status
systemctl status smpp-engine
```

### RabbitMQ
```bash
rabbitmqctl status
```

### PostgreSQL
```bash
pg_isready -U smpp
```

---

**STOP - Awaiting approval to proceed to Phase 2 (Core Data Model and Laravel Control Plane)**