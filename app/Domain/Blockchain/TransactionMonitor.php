<?php

namespace App\Domain\Blockchain;

use App\Domain\Blockchain\Contracts\TransactionMonitorInterface;
use App\Jobs\ProcessBlockchainTransactionJob;
use App\Models\BlockchainNetwork;
use App\Models\BlockchainSyncCursor;
use App\Models\PaymentAddress;
use Illuminate\Support\Facades\Log;

final class TransactionMonitor implements TransactionMonitorInterface
{
    public function __construct(private readonly BlockchainAdapterRegistry $registry) {}

    public function poll(BlockchainNetwork $network): int
    {
        $addresses = PaymentAddress::query()
            ->where('network_id', $network->id)
            ->where('status', 'assigned')
            ->whereHas('paymentRequest', function ($q) {
                $q->whereIn('status', [
                    'WAITING_FOR_PAYMENT',
                    'TRANSACTION_DETECTED',
                    'CONFIRMING',
                    'UNDERPAID',
                ]);
            })
            ->pluck('address')
            ->all();

        $cursor = BlockchainSyncCursor::query()->firstOrCreate(
            ['network_id' => $network->id],
            ['cursor' => null],
        );

        $adapter = $this->registry->forNetwork($network);
        $incoming = $adapter->fetchIncoming($network, $addresses, $cursor->cursor);
        $dispatched = 0;

        foreach ($incoming as $tx) {
            ProcessBlockchainTransactionJob::dispatch($network->id, [
                'networkCode' => $tx->networkCode,
                'assetCode' => $tx->assetCode,
                'txHash' => $tx->txHash,
                'logIndex' => $tx->logIndex,
                'fromAddress' => $tx->fromAddress,
                'toAddress' => $tx->toAddress,
                'amountDecimal' => $tx->amountDecimal,
                'blockNumber' => $tx->blockNumber,
                'confirmations' => $tx->confirmations,
                'contractAddress' => $tx->contractAddress,
                'raw' => $tx->raw,
            ]);
            $dispatched++;
        }

        $cursor->update(['last_polled_at' => now()]);

        Log::info('blockchain.poll_completed', [
            'network' => $network->code,
            'addresses' => count($addresses),
            'dispatched' => $dispatched,
        ]);

        return $dispatched;
    }
}
