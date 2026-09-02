<div class="relative min-h-screen overflow-hidden bg-slate-950">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_rgba(99,102,241,0.28),_transparent_50%)]"></div>
    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(148,163,184,0.07)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,0.07)_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_80%)]"></div>

    <div class="relative mx-auto grid min-h-screen max-w-6xl lg:grid-cols-2">
        <aside class="hidden flex-col justify-between px-10 py-10 text-slate-200 lg:flex">
            <a href="/" class="inline-flex items-center gap-2.5">
                <x-brand-mark class="h-9 w-9" />
                <span class="text-lg font-semibold text-white">Vaultgate</span>
            </a>
            <div class="max-w-md pb-16">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300">Self-hosted crypto gateway</p>
                <h2 class="mt-4 text-4xl font-semibold tracking-tight text-white">One invoice. One address. One ledger credit.</h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-400">
                    Unique deposit addresses, on-chain confirmation, an immutable merchant ledger, and signed webhooks — without routing money through a third-party processor.
                </p>
                <ul class="mt-8 space-y-3 text-sm text-slate-300">
                    <li class="flex gap-3"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-400"></span>USDT on TRON in V1, adapters for more networks later</li>
                    <li class="flex gap-3"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-400"></span>Idempotent APIs so retries never double-charge the books</li>
                    <li class="flex gap-3"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-400"></span>Admin reconciliation from day one</li>
                </ul>
            </div>
        </aside>

        <div class="flex items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <a href="/" class="inline-flex items-center gap-2">
                        <x-brand-mark class="h-8 w-8" />
                        <span class="font-semibold text-white">Vaultgate</span>
                    </a>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white p-8 shadow-2xl shadow-indigo-950/40">
                    @isset($title)
                        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
                    @endisset
                    @isset($subtitle)
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $subtitle }}</p>
                    @endisset
                    <div class="mt-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
