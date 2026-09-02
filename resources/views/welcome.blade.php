<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vaultgate — Self-hosted crypto payment gateway</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 font-sans text-slate-200 antialiased">
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(99,102,241,0.25),_transparent_55%)]"></div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,0.08)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>

        <header class="relative mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <a href="/" class="flex items-center gap-2.5">
                <x-brand-mark class="h-9 w-9" />
                <span class="text-lg font-semibold text-white">Vaultgate</span>
            </a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="#product" class="hidden text-slate-300 hover:text-white sm:inline">Product</a>
                <a href="#flow" class="hidden text-slate-300 hover:text-white sm:inline">How it works</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-full bg-white px-4 py-2 font-medium text-slate-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-slate-300 hover:text-white">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-indigo-500 px-4 py-2 font-medium text-white hover:bg-indigo-400">Start building</a>
                @endauth
            </nav>
        </header>

        <section class="relative mx-auto max-w-6xl px-6 pb-24 pt-10 lg:pb-32 lg:pt-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-200">
                V1 · Crypto → crypto · Self-hosted
            </div>
            <h1 class="mt-6 max-w-3xl text-4xl font-semibold tracking-tight text-white sm:text-6xl">
                Accept stablecoin payments without giving up the ledger.
            </h1>
            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-slate-300">
                Vaultgate is a production-oriented, self-hosted payment gateway. Each order gets a unique deposit address, incoming USDT is watched on-chain, the merchant ledger is credited once, and a signed webhook tells you when it is done.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="rounded-full bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-400">Create a merchant account</a>
                <a href="#flow" class="rounded-full border border-white/15 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/5">See the payment flow</a>
            </div>
            <div class="mt-16 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="text-sm text-slate-400">Attribution</div>
                    <div class="mt-1 text-lg font-semibold text-white">One payment, one address</div>
                    <p class="mt-2 text-sm text-slate-400">Never guess which order a chain transfer belongs to.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="text-sm text-slate-400">Accounting</div>
                    <div class="mt-1 text-lg font-semibold text-white">Immutable double-entry</div>
                    <p class="mt-2 text-sm text-slate-400">Balances come from the journal, not a hot wallet.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="text-sm text-slate-400">Operations</div>
                    <div class="mt-1 text-lg font-semibold text-white">Signed webhooks + recon</div>
                    <p class="mt-2 text-sm text-slate-400">Retries, HMAC, and an admin exception desk from day one.</p>
                </div>
            </div>
        </section>
    </div>

    <section id="product" class="bg-slate-50 py-20 text-slate-900">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-3xl font-semibold tracking-tight">Built like a real gateway, not a demo wallet.</h2>
            <p class="mt-3 max-w-2xl text-slate-600">V1 is crypto-to-crypto only. Fiat on-ramps, cards, and payouts stay out — the architecture is ready for them later.</p>
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Merchant API', 'Authenticated, rate-limited REST with Idempotency-Key so a timeout never creates two invoices.'],
                    ['Unique addresses', 'Every payment request is assigned its own destination. Matching is address → payment → merchant.'],
                    ['QR for checkout', 'Each invoice carries a payment URI the merchant can render as a QR. No shared merchant QR.'],
                    ['Chain monitor', 'Adapters watch TRON (mock locally, TronGrid-ready). Confirmations are processed on the queue.'],
                    ['Internal ledger', 'Pending then available credits, overpayment suspense, admin adjustments — never edit history.'],
                    ['Admin control room', 'Merchants, payments, chain txs, journals, webhooks, and reconciliation in one console.'],
                ] as [$title, $body])
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="flow" class="bg-white py-20 text-slate-900">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-3xl font-semibold tracking-tight">From API call to credited balance</h2>
            <ol class="mt-10 grid gap-4 md:grid-cols-4">
                @foreach ([
                    ['01', 'Create', 'POST /api/v1/payments returns a unique address, amount, expiry, and QR payload.'],
                    ['02', 'Pay', 'The customer sends USDT on TRON to that address — they already hold the asset.'],
                    ['03', 'Confirm', 'The monitor detects the transfer, tracks confirmations, and matches the invoice.'],
                    ['04', 'Settle', 'The ledger is credited once. A signed webhook fires. The dashboard updates.'],
                ] as [$n, $title, $body])
                    <li class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="text-xs font-semibold text-indigo-600">{{ $n }}</div>
                        <div class="mt-2 font-semibold">{{ $title }}</div>
                        <p class="mt-2 text-sm text-slate-600">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>
            <pre class="mt-10 overflow-x-auto rounded-2xl bg-slate-950 p-6 text-sm leading-relaxed text-indigo-100"><code>POST /api/v1/payments
Authorization: Bearer gw_live_…
Idempotency-Key: checkout-8831

{
  "merchant_order_id": "ORDER-12345",
  "amount": "100.00",
  "currency": "USDT",
  "network": "TRON"
}</code></pre>
        </div>
    </section>

    <section class="bg-slate-950 py-16">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-6 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-white">Run it on your own infrastructure.</h2>
                <p class="mt-2 text-slate-400">Laravel, MySQL, Redis, queues, and a mock chain for local development.</p>
            </div>
            <a href="{{ route('login') }}" class="rounded-full bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-400">Open the console</a>
        </div>
        <footer class="mx-auto mt-12 max-w-6xl border-t border-white/10 px-6 pt-6 text-sm text-slate-500">
            Vaultgate V1 · Crypto-to-crypto payment gateway · Not a bank · Not an exchange
        </footer>
    </section>
</body>
</html>
