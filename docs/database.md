# Database

Financial tables are append-oriented. Journal entries and postings are not updated after insert.

## Core entities

- `merchants`, `merchant_users`, `api_credentials`, `webhook_endpoints`
- `blockchain_networks`, `blockchain_assets`
- `wallets`, `payment_addresses` (unique per network+address; unique wallet derivation index)
- `payment_requests` (unique merchant+order id)
- `blockchain_transactions` unique `(network_id, tx_hash, log_index)`
- `ledger_accounts`, `ledger_journal_entries` (unique `idempotency_key`), `ledger_postings`
- `merchant_balance_projections`
- `webhook_events`, `webhook_deliveries` unique `(event, endpoint)`
- `reconciliation_runs`, `reconciliation_items`
- `audit_logs`, `idempotency_keys`, `system_events`

## Money

Amounts are **integer minor-unit strings** (`amount_minor`) plus asset `decimals`. PHP floats are not used.

## Query patterns indexed

- Payment list by merchant+status
- Address lookup by network+address
- Due retries (`webhook_deliveries.next_retry_at`)
- Idempotency (`merchant_id, key`)
- Expiration (`expires_at` + waiting status)
