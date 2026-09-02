# Reconciliation

Hourly job and admin “Run now” compare:

- chain transactions vs internal `blockchain_transactions`
- payments vs credits
- journal nets vs projections

Exceptions include unmatched chain txs, credited-without-tx, uncredited confirmed payments, credited-but-unconfirmed, orphaned addresses, projection drift.
