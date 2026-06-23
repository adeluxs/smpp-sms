# SMPP Platform Architecture (Phase 0)

## Assumptions

1. Single-server deployment initially, with horizontal scaling capability
2. PostgreSQL chosen as primary database for strong consistency and JSON support
3. RabbitMQ for durable message queuing between Laravel and Python services
4. Redis for caching, distributed locks, and rate limiting
5. Nginx as reverse proxy with PHP-FPM for Laravel
6. Python 3.11+ with asyncio for SMPP protocol handling
7. Systemd for process supervision on AlmaLinux
8. TLS 1.3 everywhere with Let's Encrypt certificates in production

## End-to-End System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              AlmaLinux Server                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐     ┌─────────────────┐     ┌────────────────────────┐  │
│  │   Nginx      │────▶│   Laravel       │────▶│   PostgreSQL           │  │
│  │ (443/80)     │     │  (Control Plane)│     │   (Primary DB)         │  │
│  └──────────────┘     └─────────────────┘     └────────────────────────┘  │
│         │                      │                        │                   │
│         │                      │                        │                   │
│         │              ┌───────▼────────┐              │                   │
│         │              │   Redis        │◀─────────────┘                   │
│         │              │ (Cache/Locks)  │        │                          │
│         │              └───────┬────────┘        │                          │
│         │                      │                   │                       │
│  ┌──────▼──────┐      ┌────────▼─────────┐        │                       │
│  │             │      │   RabbitMQ       │◀───────┘                       │
│  │   Python    │◀───▶│ (Message Queue)  │                                  │
│  │  SMPP Engine│      └──────────────────┘                                  │
│  │             │               │                                            │
│  └──────┬──────┘               │                                            │
│         │              ┌───────▼────────┐                                    │
│         │              │  SMPP Provider   │                                    │
│         │              │     (or Mock)    │                                    │
│         │              └──────────────────┘                                    │
│         │                                                                              │
│  ┌──────▼──────┐                                                                    │
│  │  Metrics &  │                                                                    │
│  │ Monitoring   │                                                                    │
│  │ (Prometheus) │                                                                    │
│  └─────────────┘                                                                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Major Services

| Service | Technology | Port | Purpose |
|---------|------------|------|---------|
| nginx | Nginx 1.24+ | 443/80 | Reverse proxy, TLS termination |
| laravel | PHP 8.2+ FPM | - | Control plane, API, admin UI |
| smpp-engine | Python 3.11+ | 2775 | SMPP server, message processing |
| smpp-mock | Python 3.11+ | 2776 | Local SMPP provider simulation |
| postgres | PostgreSQL 15+ | 5432 | Primary database |
| redis | Redis 7+ | 6379 | Cache, locks, rate limiting |
| rabbitmq | RabbitMQ 3+ | 5672/15672 | Message broker |
| prometheus | Prometheus | 9090 | Metrics collection |
| grafana | Grafana | 3000 | Metrics visualization |

## Data Flow: Client Bind to Delivery Receipt

```
1. SMPP Client ─bind_transceiver─▶ smpp-engine (Python)
   - Authentication via system_id/password
   - IP allowlist check
   - Bind rate limiting

2. smpp-engine validates and creates session
   - Stores session metadata in Redis
   - Returns bind response

3. Client submits message via submit_sm
   - smpp-engine validates and generates internal message_id
   - Writes message to database (status: queued)
   - Publishes to RabbitMQ: smpp.submit queue

4. Laravel API/consumer receives message
   - Validates message (encoding, length, destination)
   - Checks client balance/credits
   - Reserves credits atomically
   - Routes to upstream provider via RabbitMQ: routing.queue

5. smpp-engine upstream worker processes
   - Selects provider based on routing rules
   - Establishes/uses upstream SMPP connection
   - Sends submit_sm to provider
   - Maps internal ID to provider message_id

6. Provider delivers to handset
   - Provider sends DLR via deliver_sm

7. smpp-engine receives DLR
   - Maps provider message_id back to internal message_id
   - Publishes to RabbitMQ: dlr.queue

8. Laravel DLR handler processes
   - Updates message status in database
   - Releases unused credits (failed messages)
   - Notifies client via API callback/SMPP

9. Client receives DLR via deliver_sm
```

## Security Model

### Authentication Boundaries
- **SMPP Clients**: System ID + password (hashed with Argon2id), per-client bind limits
- **Admin Users**: Laravel Fortify/Jetstream authentication, 2FA optional
- **API Clients**: Bearer tokens (hashed, scoped to client permissions)
- **Service-to-Service**: Mutual TLS or shared secrets via environment

### Credential Encryption
- SMPP provider passwords: AES-256-GCM at rest
- API keys: Argon2id hashing (one-way, no decryption needed)
- Client passwords: Argon2id hashing (for web login)

### Network Security
- Firewall: Only ports 22, 443, 2775 exposed publicly
- Redis/RabbitMQ: Bind to localhost only or internal network
- Database: localhost/VPC only
- SELinux: Enforcing with targeted policies for each service

### Rate Limiting
- Redis-backed token bucket per client
- SMPP TPS: Configurable bursts up to max throughput
- API: 100 req/min default, configurable
- Bind attempts: Exponential backoff after failures

## Deployment Model (AlmaLinux/Vultr)

### Directory Layout
```
/opt/smpp-platform/
├── laravel/           # Laravel application
│   ├── public/
│   ├── app/
│   ├── config/
│   └── .env
├── smpp-engine/       # Python SMPP service
│   ├── src/
│   ├── pyproject.toml
│   └── .env
├── smpp-mock/         # Mock provider (non-prod)
├── docker/            # Optional container deployment
├── backups/           # Daily database dumps
└── logs/              # Centralized logs
```

### Process Supervision
- systemd units for each service
- Automatic restart on failure
- Health check endpoints
- Log rotation via logrotate

## Local Development Architecture

### Docker Compose Services
```
services:
  postgres:    # Database with seeded data
  redis:       # Cache and locks
  rabbitmq:    # Message broker with management UI
  smpp-mock:   # Simulated upstream provider
  smpp-engine: # Python SMPP engine (hot-reload)
  laravel:     # Laravel with Horizon workers
  prometheus:  # Metrics
  grafana:     # Dashboard
```

### Local Bootstrap Commands
1. `docker-compose up -d` - Start infrastructure
2. `cd laravel && composer install && php artisan migrate --seed`
3. `cd smpp-engine && pip install -e . && python -m smpp_engine`
4. `cd smpp-mock && python -m smpp_mock`
5. `./scripts/test-flow.sh` - Run end-to-end test

### Testing Without Production Providers
- SMPP Mock server simulates upstream behavior
- Pre-seeded test tenant with API key
- Pre-seeded SMPP client credentials
- Pre-seeded upstream provider configuration
- End-to-end test script sends message, checks status, validates DLR

## Success Criteria

### Functional
- [ ] SMPP client can bind and submit messages
- [ ] Messages routed to upstream provider(s)
- [ ] Delivery receipts received and correlated
- [ ] Client balance deducted correctly
- [ ] Admin UI shows real-time message status

### Non-Functional
- [ ] 5,000+ messages/second throughput
- [ ] <100ms API response time
- [ ] 99.9% uptime SLA capability
- [ ] All credentials encrypted at rest
- [ ] Full audit trail for all actions
- [ ] Zero-downtime deployment capability

### Local Development
- [ ] Single command to start all services
- [ ] End-to-end test passes in <30 seconds
- [ ] Hot reload for both Laravel and Python
- [ ] Mock provider simulates failures for testing

---

**STOP - Awaiting approval to proceed to Phase 1**