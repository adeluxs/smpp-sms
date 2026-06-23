# Phase 6: Security, Rate Limiting, Abuse Controls

## Client-Level TPS Control

- Token bucket rate limiter in Redis
- Configurable per-client throughput limit (default 100/s)
- Window-based limiting to prevent burst abuse
- Lua script for atomic operations

## IP Allowlists

- CIDR notation support for ranges
- Exact IP matching for single addresses
- Checked on bind attempt, rejected if not in list

## Credential Encryption

- SMPP provider passwords: AES-256-GCM via Laravel Crypt
- API keys: SHA256 hash storage (one-way)
- Client SMPP passwords: SHA256 (upgradeable to Argon2id)
- No plaintext credentials in logs

## Audit Logs

- Model observer for create/update/delete
- Action, resource type, resource ID
- Before/after values stored as JSON
- IP address and user agent captured

## Suspension and Quarantine

- `smpp_clients.status`: active/suspended/disabled
- Inactive clients rejected at bind time
- No automatic quarantine (manual admin action)

## Input Validation

- E.164 destination format
- Content length: 1-65535 chars
- Encoding auto-detected and stored
- Sender ID length limit: 11 chars

## Security Test Cases

```
1. Bind with invalid credentials → ESME_RINVSRCADR
2. Bind from non-allowlisted IP → Rejected
3. Exceed bind rate limit → ESME_RTHROTTLED
4. Exceed submit_sm rate limit → ESME_RTHROTTLED
5. Duplicate message_id → Ignored (idempotency)
```

---

**STOP - Awaiting approval to proceed to Phase 7 (Billing and Commercial Rules)**