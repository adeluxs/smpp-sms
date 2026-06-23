# Phase 5: DLR and Status Correlation

## Delivery Receipt Parsing

The mock provider sends DLRs with:
- `provider_message_id` - Maps back to internal message
- `status` - delivered/failed/expired/undelivered
- `received_at` - ISO8601 timestamp

## ID Mapping

| Direction | Mapping | Storage |
|-----------|---------|---------|
| Internal → Provider | Internal UUID in message record | PostgreSQL messages table |
| Provider → Internal | `provider_message_id` field | Query and update |

## Status Transitions

Allowed transitions:
- `queued` → `submitted` → `delivered`
- `queued` → `submitted` → `failed`
- `queued` → `submitted` → `expired`
- Any → `rejected` (on error)

## Laravel DLR Handler

- POST `/internal/api/v1/dlr/update` receives DLR from Python
- Updates message status in database
- Dispatches `DLRReceived` job for callbacks
- Returns 200 OK even if message unknown (idempotent)

## Mock Provider DLR Flow

1. Accept bind_transceiver
2. On submit_sm, send submit_sm_resp with message_id
3. Publish DLR to RabbitMQ `smpp.dlr` queue
4. Python DLRProcessor consumes and calls Laravel internal API
5. Laravel updates message status, triggers client notification

## Test Script

```bash
docker-compose up -d
# The mock provider will emit DLRs for test messages
```

---

**STOP - Awaiting approval to proceed to Phase 6 (Security, Rate Limiting, Abuse Controls)**