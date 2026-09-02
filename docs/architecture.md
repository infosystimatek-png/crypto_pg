# Architecture

Self-hosted **crypto-to-crypto** payment gateway. V1 settles USDT on TRON (testnet/mock by default) without fiat on-ramps, exchanges, or payouts.

## Bounded contexts

```
API / Dashboard
      │
      ▼
Payments ──► Wallets / Address Manager ──► Blockchain adapters
      │
      ▼
Transaction Processor ──► Ledger ──► Merchant projection
      │
      ▼
Signed webhooks + reconciliation
```

Business logic lives in `app/Domain/*`. Controllers are thin. Network-specific code is confined to `app/Domain/Blockchain/Adapters`.

## Payment attribution

One payment request allocates **one unique deposit address**. Incoming chain activity is matched by destination address, not by a shared merchant wallet balance.

## State machine

Valid transitions are enforced by `PaymentStateMachine`. CRUD cannot set `status` arbitrarily.

`CREATED → WAITING_FOR_PAYMENT → TRANSACTION_DETECTED → CONFIRMING → CONFIRMED|OVERPAID → CREDITED`

Failure/exception branches: `EXPIRED`, `UNDERPAID`, `WRONG_ASSET`, `WRONG_NETWORK`, `FAILED`, `CANCELLED`.

## Blockchain independence

`BlockchainAdapterInterface` is implemented by:

- `MockBlockchainAdapter` (local/dev/tests)
- `TronGridAdapter` (optional RPC/indexer provider)

Replace TronGrid with a private node by adding an adapter; payment, ledger, and webhook code stay unchanged.

## Ledger

Double-entry journals are the source of truth. `merchant_balance_projections` is a cache. Admins cannot edit balances; they post **adjustments**.

## Custody

Addresses are derived from an opaque `wallets.key_ref`. Private keys are never stored in MySQL, `.env`, logs, or API responses. V1 uses a mock custody backend; HSM/MPC/vault can implement the same interfaces later.

## Async processing

Customer APIs never wait for confirmations. The scheduler polls adapters, jobs process transactions, credit the ledger, and dispatch webhooks.
