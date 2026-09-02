# Deployment

1. PHP 8.3+, MySQL 8, Redis (queues/cache in production).
2. `composer install --no-dev -o`
3. Copy `.env`, set `APP_KEY`, MySQL, Redis, `QUEUE_CONNECTION=redis`.
4. `php artisan migrate --force`
5. `php artisan db:seed` only for empty non-prod.
6. Run `queue:work`, `schedule:work`, and a web server (nginx/php-fpm or container).
7. `npm ci && npm run build` for Jetstream UI.

Docker: `docker compose up --build`. App is on port 8000.

Production blockchain: switch network `adapter` to `trongrid` and supply `TRONGRID_API_KEY`. Prefer your own node behind the same interface.
