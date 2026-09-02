# Blockchain flow

```
Scheduler (every minute)
      ▼
PollBlockchainNetworksJob
      ▼
TransactionMonitor → adapter.fetchIncoming(assigned addresses)
      ▼
ProcessBlockchainTransactionJob (unique per network+hash+log)
      ▼
TransactionProcessor (DB transaction + row locks)
      ▼
match address → payment → amount/asset/network checks
      ▼
pending ledger credit → confirmations → available credit
```

V1 default adapter is **mock**. Inject transfers with:

```bash
php artisan gateway:simulate-incoming PAY_01XXXX --amount=100.00 --confirmations=19
```

Production TRON: set `blockchain_networks.adapter` to `trongrid` and `GATEWAY_BLOCKCHAIN_DRIVER=trongrid`. The payment engine does not change.
