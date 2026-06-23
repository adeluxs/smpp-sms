# API Contracts: Laravel ↔ Python SMPP Engine

## Message Queue Contract (RabbitMQ)

### Queue: `smpp.submit`
**Direction:** Laravel → Python Engine
**Purpose:** New messages to be sent via SMPP

```json
{
    "message_id": "uuid",
    "tenant_id": "uuid",
    "smpp_client_id": "uuid",
    "api_key_id": "uuid",
    "source": "string (max 11 chars)",
    "destination": "string (E.164 format)",
    "content": "string",
    "encoding": "GSM7|UCS2|BINARY",
    "segments": "integer",
    "priority": "integer (default 100)",
    "submit_at": "ISO8601 timestamp",
    "callback_url": "string (optional, for delivery notification)"
}
```

### Queue: `smpp.dlr`
**Direction:** Python Engine → Laravel
**Purpose:** Delivery receipts from upstream providers

```json
{
    "message_id": "uuid",
    "provider_message_id": "string",
    "dlr_message_id": "string",
    "status": "delivered|failed|expired|undelivered",
    "received_at": "ISO8601 timestamp",
    "parsed_data": {
        "submr": "integer",
        "donedate": "string",
        "reason": "string"
    }
}
```

### Queue: `smpp.provider`
**Direction:** Laravel → Python Engine
**Purpose:** Provider configuration updates

```json
{
    "provider_id": "uuid",
    "host": "string",
    "port": "integer",
    "system_id": "string",
    "action": "connect|disconnect|status"
}
```

## SMPP Session Contract

### Bind Request (from SMPP client to Python engine)
- System ID validation against `smpp_clients` table
- IP allowlist check via Redis cache
- Throughput limit verification
- Session tracking in Redis with TTL

### Message Acknowledgment (from Python to Laravel)
- POST to `http://laravel/internal/api/v1/messages/{id}/ack`
- Headers: `X-SMPP-Signature: HMAC-SHA256`
- Body: `{status: "submitted|failed", provider_message_id: "string"}`

## Redis Key Patterns

| Key Pattern | Purpose | TTL |
|-------------|---------|-----|
| `smpp:session:{system_id}` | Active session data | 2h (bind timeout) |
| `smpp:rate:{system_id}` | Token bucket rate limit | 1h |
| `smpp:provider:{id}:conn` | Provider connection state | 5m |
| `smpp:msg:{id}:reserved` | Credit reservation lock | 5m |

## HTTP Endpoints (Internal)

### POST /internal/api/v1/messages/{id}/ack
Headers: `X-SMPP-Signature: sha256-hmac`
Body: `{"status": "string", "provider_message_id": "string"}`

### GET /internal/api/v1/routes/{prefix?}
Returns routing information for message prefix matching.