# Phase 8: Observability and Operations

## Metrics

### SMPP Engine Metrics (Prometheus)
- `smpp_messages_sent_total` - Counter
- `smpp_dlr_received_total` - Counter  
- `smpp_active_sessions` - Gauge
- `smpp_queue_depth` - Gauge
- `smpp_provider_reconnects_total` - Counter
- `smpp_bind_attempts_total{result}` - Counter
- `smpp_submit_latency_seconds` - Histogram
- `smpp_dlr_latency_seconds` - Histogram

### Endpoints
- `http://localhost:8000/metrics` - Prometheus metrics

## Structured Logging

All logs in JSON format with fields:
- `timestamp` - ISO8601
- `level` - debug/info/warning/error
- `message` - Log message
- Context: request_id, tenant_id, message_id, system_id

## Health Checks

### Laravel
- GET `/health` - Returns database/redis/rabbitmq status
- 200 OK if all healthy, 503 if degraded

### SMPP Engine
- Port 8000 metrics endpoint
- systemd health check on service

## Backup and Restore

### Backup Script (`scripts/backup.sh`)
- Daily gzipped SQL dump
- Stored in `/opt/smpp-platform/backups/daily/`
- Retention: 7 days

### Restore Script (`scripts/restore.sh`)
- Uncompress and restore from SQL dump
- Usage: `./restore.sh backup_20240101.sql.gz`

## Alerting (Prometheus Rules)

```yaml
- alert: SMPPNoActiveSessions
  expr: smpp_active_sessions == 0
  for: 5m
  severity: warning

- alert: QueueDepthTooHigh
  expr: smpp_queue_depth > 10000
  for: 2m
  severity: critical

- alert: ProviderReconnects
  expr: rate(smpp_provider_reconnects_total[5m]) > 5
  severity: warning
```

## Grafana Dashboard

Pre-provisioned in `docker/grafana-dashboards/smpp.json`:
- Message throughput graph
- Active sessions gauge
- Queue depth chart
- DLR latency histogram

## Deployment Commands

```bash
# Backup
docker-compose exec postgres pg_dump -U smpp smpp_platform > backup.sql

# Restore  
cat backup.sql | docker-compose exec -T postgres psql -U smpp smpp_platform

# Health check
curl http://localhost/health
curl http://localhost:8000/metrics
```

---

**STOP - Awaiting approval to proceed to Phase 9 (Deployment on AlmaLinux/Vultr)**