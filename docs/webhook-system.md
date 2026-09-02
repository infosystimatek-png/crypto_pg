# Webhooks

Events such as `payment.confirmed` and `payment.overpaid` are stored with ULID `event_id`s.

Headers:

- `X-Gateway-Signature` HMAC-SHA256 of `{timestamp}.{raw_body}`
- `X-Gateway-Timestamp`
- `X-Gateway-Event-Id`
- `X-Gateway-Event`

Retries use exponential backoff (capped) up to `GATEWAY_WEBHOOK_MAX_ATTEMPTS`, then `dead_letter`. Admins can manual retry. Deliveries are unique per event+endpoint so replays do not create duplicate rows.

Never assume exactly-once delivery. Merchants must treat `event_id` as idempotent.
