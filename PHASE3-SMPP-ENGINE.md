# Phase 3: Python SMPP Engine Foundation

## Service Architecture

```
smpp-engine/
├── __main__.py          # Entry point, metrics server
├── config.py            # Pydantic settings
├── server.py            # SmppServer wrapper
├── session_manager.py   # Bind/auth/session handling
├── client_repository.py   # Database/Redis client lookup
├── message_handler.py   # Queue consumer for submit_sm
├── metrics.py           # Prometheus metrics
└── provider_connector.py # (Phase 4) Upstream connections
```

## SMPP Session Management

- Accepts `bind_transceiver`, `bind_transmitter`, `bind_receiver`
- Validates system_id against database
- Checks IP allowlist from Redis cache
- Enforces throughput limits via token bucket
- Tracks active sessions in memory and Redis

## Bind/Auth Logic

1. Extract system_id/password from bind request
2. Query `smpp_clients` table for client record
3. Verify password hash (SHA256, upgradeable to Argon2)
4. Check client status is `active`
5. Verify rate limit not exceeded
6. Return bind response with success/failure

## Long-running Worker Model

- Single async event loop with `asyncio`
- Connection pool to PostgreSQL (5-20 connections)
- Persistent RabbitMQ connection with reconnection
- Background task for session cleanup (TTL expired)
- Prometheus metrics on port 8000

## Message Intake

- Consumes from `smpp.submit` queue
- Acknowledges after processing
- Publishes DLR responses to `smpp.dlr` queue
- Uses idempotency keys to prevent duplicates

## Reconnect and Recovery

- RabbitMQ auto-reconnect on connection loss
- Redis connection reuse with health checks
- Session timeout cleanup after 2h idle
- Graceful shutdown on SIGTERM/SIGINT

---

**STOP - Awaiting approval to proceed to Phase 4 (Message Processing Pipeline)**