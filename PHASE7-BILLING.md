# Phase 7: Billing and Commercial Rules

## Wallet Reservation and Deduction

- Atomic credit reservation on submit
- Two-phase commit with database locks
- Failed messages release reserved credits
- Delivered messages deduct permanently

## Pricing Models

- Per-message pricing (default: $0.01)
- Per-segment pricing for multipart
- Tariff tables for route-specific rates
- Currency stored per tariff record

## Invoice Exports

- GET `/api/v1/reports/messages` - Paginated message list
- GET `/api/v1/reports/summary` - Status counts and totals
- Export formats: JSON (future: CSV/PDF)

## Low-Balance Enforcement

- `WalletService::checkLowBalance()` - Check against threshold
- Reject messages when balance below minimum
- Threshold configurable per tenant
- Future: Notification webhook for low balance

## Billing Flow

```
1. Client submits message
2. WalletService.reserve() checks balance
3. Credits reserved (locked row)
4. Message queued for processing
5. DLR received (delivered/failed)
6. If failed: WalletService.release()
7. If delivered: Credits permanently deducted
```

---

**STOP - Awaiting approval to proceed to Phase 8 (Observability and Operations)**