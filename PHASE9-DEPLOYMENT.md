# Phase 9: Deployment on AlmaLinux/Vultr

## Deployment Steps

1. **Server Setup (Vultr)**
   - Deploy AlmaLinux 9 cloud instance
   - 4 vCPUs, 8GB RAM, 100GB SSD recommended
   - Private network optional but recommended

2. **Package Installation**
   ```bash
   dnf install -y nginx php82-php-fpm php82-php-pgsql \
       redis postgresql15-server rabbitmq-server python311
   ```

3. **Directory Setup**
   ```bash
   mkdir -p /opt/smpp-platform/{laravel,smpp-engine,backups,docker,logs}
   useradd -r -s /bin/false smpp
   chown -R smpp:smpp /opt/smpp-platform
   ```

4. **TLS Configuration**
   ```bash
   bash deploy/tls-setup.sh sms.example.com
   ```

5. **Firewall**
   ```bash
   bash deploy/firewallsetup.sh
   ```

6. **Systemd Services**
   ```bash
   cp smpp-engine/smpp-engine.service /etc/systemd/system/
   cp laravel/laravel-worker.service /etc/systemd/system/
   systemctl daemon-reload
   systemctl enable --now smpp-engine laravel-worker
   ```

## Service Definitions

- `smpp-engine.service` - Python SMPP protocol engine
- `laravel-worker.service` - Laravel Horizon queue workers

## Nginx Config

- HTTP/2 enabled
- TLS 1.2/1.3 only
- FastCGI timeout 300s for long operations

## Release/Rollback Strategy

- Git-based deployment
- `deploy.sh` - Deploy latest from main
- `rollback.sh` - Revert to previous commit

## Scaling Notes

- Multiple SMPP engine instances: Use Redis for session state
- Multiple Laravel workers: Use database for queue coordination
- Horizontal scaling: Add systemd template services

## Database Backup

- Daily cron job with `scripts/backup.sh`
- Retention: 7 days
- Restore via `scripts/restore.sh`

---

**STOP - Awaiting approval to proceed to Phase 10 (Testing, Load Testing, and Hardening)**