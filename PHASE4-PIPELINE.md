# Phase 4: Message Processing Pipeline

## Submission Queueing

- Messages consumed from `smpp.submit` queue (durable)
- Idempotency check before processing (Redis SET with 24h TTL)
- Dead-letter queue for failed messages (`dlq.failed`)

## Message Validation

- Destination: E.164 format, max 20 chars
- Content: 1-65535 characters
- Encoding auto-detection (GSM7 vs UCS2)
- Segmentation: 153 chars per segment for long messages

## Provider Selection & Routing

- Routes loaded from database
- Selection strategies: priority, least_cost, round_robin, failover
- Provider health check before routing
- Per-route throughput limits

## Retry Policy

- Exponential backoff: 2^n seconds (max 60s)
- Max 3 delivery attempts
- Failed messages published to dead-letter queue
- Reason logged with each failure

## Idempotency Protection

- Redis key: `smpp:idempotency:{message_id}`
- 24-hour TTL
- Prevents duplicate sends on retries

## End-to-End Local Test

```
1. Publish test message to smpp.submit
2. Python engine validates and routes
3. Mock provider accepts and returns DLR
4. DLR published to smpp.dlr queue
5. Laravel consumes DLR and updates status
```

## Test Command
```bash
docker-compose up -d
docker-compose exec smpp-engine python -m tests.test_flow
```

---

**STOP - Awaiting approval to proceed to Phase 5 (DLR and Status Correlation)**