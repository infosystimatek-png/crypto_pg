# Payment flow

```
Merchant API  POST /api/v1/payments
      │  Idempotency-Key
      ▼
CreatePaymentService
      │  validate merchant, asset, network
      ▼
Payment CREATED
      ▼
AddressManager allocates unique address
      ▼
WAITING_FOR_PAYMENT + QR payload
      ▼
JSON PaymentResource (no keys)
```

Customer pays the address. Monitoring is asynchronous (see `blockchain-flow.md`).

Expiration: scheduler job transitions `WAITING_FOR_PAYMENT` past `expires_at` to `EXPIRED`. Late chain activity still persists as a transaction for reconciliation and is not auto-credited.
