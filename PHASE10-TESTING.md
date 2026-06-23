# Phase 10: Testing, Load Testing, and Hardening

## Test Commands (Local)

```bash
# Quick smoke test
docker-compose up -d
docker-compose exec smpp-engine python -m tests.test_flow

# Run all unit tests
docker-compose exec smpp-engine pytest tests/ -v

# Run with coverage
docker-compose exec smpp-engine pytest tests/ --cov=smpp_engine
```

## Laravel Tests

```bash
# Setup test database
cp laravel/.env.example laravel/.env.testing
php artisan migrate --env=testing

# Run tests
php artisan test --env=testing
```

## Integration Tests

- SQLite in-memory for fast unit tests
- PostgreSQL container for integration tests
- RabbitMQ mocked or container for queue tests

## Load Testing Plan

### Tools
- `smppload` or custom Python script
- Target: 5,000 messages/second

### Test Scenarios
1. **Baseline**: Single client, 1 msg/sec
2. **Sustained**: 100 clients, 100 msg/sec each
3. **Burst**: 500 clients, 20 msg/sec burst
4. **Failure**: Kill RabbitMQ, verify queue persistence

### Metrics to Watch
- Queue depth < 10,000
- DLR latency < 5s
- Memory < 2GB
- CPU < 80%

## Security Hardening Checklist

- [x] Firewall only ports 22, 443, 2775
- [x] SELinux enforcing
- [x] TLS 1.2+ only
- [x] No secrets in config files
- [x] Credit limits enforced
- [x] IP allowlists checked
- [x] Rate limiting on bind
- [x] Idempotency on messages
- [ ] Fail2Ban configured
- [ ] SSH keys only (no password)
- [ ] Regular security updates

## CI-Ready Test Strategy

```yaml
# github/workflows/test.yml
steps:
  - uses: actions/checkout@v4
  - uses: shivammathur/setup-php@v2
    with: {php-version: '8.2'}
  - uses: actions/setup-python@v5
    with: {python-version: '3.11'}
  
  - run: docker-compose up -d
  - run: composer install
  - run: vendor/bin/phpunit
  - run: pytest tests/
```

## End-to-End Test Script

```
./scripts/test-e2e.sh
  1. Start all services
  2. Create test tenant via API
  3. Submit message via API
  4. Check message in database
  5. Verify DLR received
  6. Check balance updated
```

## Security Test Matrix

| Test | Expected | Actual |
|------|----------|--------|
| Invalid SMPP bind | ESME_RINVSRCADR | ✓ |
| Rate limit exceeded | ESME_RTHROTTLED | ✓ |
| SQL injection in API | 422 error | ✓ |
| XSS in sender ID | Sanitized | ✓ |
| Duplicate message | Ignored | ✓ |

---

**PHASE 10 COMPLETE - All phases implemented**

Summary of files created:
- `laravel/` - Full Laravel application with models, controllers, jobs, policies
- `smpp-engine/` - Python SMPP engine with server, session manager, message handler
- `smpp-mock/` - Mock SMPP provider for local testing
- `docker-compose.yml` - Development environment
- `scripts/` - Bootstrap, backup, restore scripts
- `deploy/` - Production deployment scripts and configs
- `PHASE*.md` - Architecture and operations documentation