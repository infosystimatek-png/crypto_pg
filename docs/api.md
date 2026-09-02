# Merchant API

Base: `/api/v1`

Auth: `Authorization: Bearer gw_live_...` or `X-Api-Key`.

Idempotency: `Idempotency-Key` on `POST /payments`.

## POST /api/v1/payments

```json
{
  "merchant_order_id": "ORDER-12345",
  "amount": "100.00",
  "currency": "USDT",
  "network": "TRON",
  "callback_url": "https://merchant.example.com/webhooks/payment"
}
```

## GET /api/v1/payments/{payment_id}

## GET /api/v1/payments

## GET /api/v1/balances

## GET /health

Operational checks for database, queue table, and blockchain adapter.
