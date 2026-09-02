# Security

- Merchant API keys are stored as hashes; plaintext is shown once at issuance.
- Webhook secrets are encrypted at rest (`encrypt()` / application key).
- Wallet `key_ref` is hidden; no private keys in MySQL or `.env`.
- RBAC: `users.role` admin|merchant; `merchant_users.role` owner|operator|viewer.
- Policies prevent cross-merchant payment reads.
- Rate limit: 60/min per API key prefix.
- Security headers middleware; correlation `X-Request-Id`.
- Audit log redacts secret-like keys.
- Do not log API keys, seeds, or private keys.

## Assumptions

- V1 runs against mock chain or TRON testnet.
- APP_KEY protects encrypted webhook secrets; rotate with Laravel's encryption rotation procedure.
- Database backups are the recovery path for journals (immutable history).
