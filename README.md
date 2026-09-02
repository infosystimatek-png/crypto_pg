# Self-hosted crypto payment gateway (V1)

Laravel + Jetstream gateway for **crypto-to-crypto** pay-in. V1 attributes each order to a unique deposit address, monitors the chain asynchronously, credits an internal double-entry ledger, and delivers signed merchant webhooks.

Fiat on-ramps, UPI, cards, exchanges, and merchant payouts are **out of scope** for V1.

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Demo logins (from seeder):

- Admin: `admin@gateway.test` / `password`
- Merchant user: `merchant@gateway.test` / `password`
- API key and webhook secret are printed once during `db:seed`

### Docker

```bash
docker compose up --build
```

MySQL, Redis, app (`:8000`), queue worker, and scheduler start together. Set `.env` to the compose MySQL/Redis hosts.

## Create a payment

```http
POST /api/v1/payments
Authorization: Bearer gw_live_...
Idempotency-Key: unique-key
Content-Type: application/json

{
  "merchant_order_id": "ORDER-12345",
  "amount": "100.00",
  "currency": "USDT",
  "network": "TRON",
  "callback_url": "https://merchant.example.com/webhooks/payment"
}
```

Simulate an incoming transfer on the mock chain:

```bash
php artisan gateway:simulate-incoming PAY_01XXXX --amount=100.00 --confirmations=19
```

## Tests

```bash
php artisan test
```

## Docs

See `docs/` for architecture, schema, payment/blockchain/ledger/webhook flows, security, reconciliation, API, deployment, and disaster recovery.
