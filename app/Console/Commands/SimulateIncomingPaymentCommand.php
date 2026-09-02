<?php

namespace App\Console\Commands;

use App\Domain\Blockchain\Adapters\MockBlockchainAdapter;
use App\Domain\Blockchain\TransactionProcessor;
use App\Models\PaymentRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateIncomingPaymentCommand extends Command
{
    protected $signature = 'gateway:simulate-incoming
        {payment : Payment public id}
        {--amount= : Amount decimal, defaults to expected}
        {--asset=USDT}
        {--network=TRON}
        {--confirmations=19}';

    protected $description = 'Inject a mock-chain incoming transfer against a payment address (local/test only).';

    public function handle(MockBlockchainAdapter $mock, TransactionProcessor $processor): int
    {
        $payment = PaymentRequest::query()
            ->with(['paymentAddress', 'network', 'asset'])
            ->where('public_id', $this->argument('payment'))
            ->firstOrFail();

        $hash = '0x'.Str::lower((string) Str::ulid());
        $amount = $this->option('amount') ?: $payment->expectedMoney()->toFixed();

        $mock->inject([
            'network_code' => strtoupper((string) $this->option('network')),
            'asset_code' => strtoupper((string) $this->option('asset')),
            'tx_hash' => $hash,
            'to_address' => $payment->paymentAddress->address,
            'amount_decimal' => $amount,
            'confirmations' => (int) $this->option('confirmations'),
            'block_number' => 1000,
        ]);

        $processor->process($payment->network_id, [
            'networkCode' => strtoupper((string) $this->option('network')),
            'assetCode' => strtoupper((string) $this->option('asset')),
            'txHash' => $hash,
            'logIndex' => 0,
            'fromAddress' => 'TMOCKFROM000000000000000000000001',
            'toAddress' => $payment->paymentAddress->address,
            'amountDecimal' => $amount,
            'blockNumber' => 1000,
            'confirmations' => (int) $this->option('confirmations'),
            'raw' => ['simulated' => true],
        ]);

        $this->info('Processed mock transaction '.$hash.' for '.$payment->public_id);
        $this->info('Status: '.$payment->fresh()->status->value);

        return self::SUCCESS;
    }
}
