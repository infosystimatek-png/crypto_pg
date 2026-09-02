# Ledger

## Accounts (per merchant + asset)

- `merchant_available`
- `merchant_pending`
- `merchant_reserved` (payouts in V2)
- `system_clearing`
- `overpayment_suspense`

## Payment credit

1. Detection of exact/over payment: debit clearing, credit pending (`payment:{id}:pending`)
2. Confirmation: debit pending, credit available (`payment:{id}:available`)
3. Overpay excess: debit clearing, credit suspense (`payment:{id}:overpayment`)

All keys are unique. Re-running the processor 50 times posts each journal once.

Balance projection is updated in the same database transaction. `LedgerService::reconcileMerchantAsset()` rebuilds nets from postings and compares.
