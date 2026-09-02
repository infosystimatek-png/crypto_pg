# Disaster recovery

- **Journals are authoritative.** Restore MySQL from backup; re-run reconciliation before enabling payouts (V2).
- Projection rows can be rebuilt from postings if they drift.
- API keys cannot be recovered (hashed). Issue replacements.
- Webhook secrets can be rotated (encrypted with APP_KEY). If APP_KEY is lost, secrets cannot be decrypted — rotate endpoints.
- Mock/testnet wallets are disposable. Production custody must live in HSM/MPC/vault snapshots, not this database.
- Replay: re-poll the chain adapter; unique `(network, tx_hash, log_index)` and ledger idempotency keys prevent double credits.
- Keep encrypted offsite DB backups and tested restore runbooks.
