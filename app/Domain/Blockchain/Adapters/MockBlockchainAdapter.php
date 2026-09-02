<?php

namespace App\Domain\Blockchain\Adapters;

use App\Domain\Blockchain\Contracts\BlockchainAdapterInterface;
use App\Domain\Blockchain\DTO\IncomingTransaction;
use App\Models\BlockchainNetwork;
use App\Models\MockChainTransaction;

final class MockBlockchainAdapter implements BlockchainAdapterInterface
{
    public function networkCode(): string
    {
        return '*';
    }

    public function supports(BlockchainNetwork $network): bool
    {
        return $network->adapter === 'mock' || config('gateway.blockchain_driver') === 'mock';
    }

    public function fetchIncoming(BlockchainNetwork $network, array $addresses, mixed $cursor): array
    {
        if ($addresses === []) {
            return [];
        }

        return MockChainTransaction::query()
            ->where('network_code', $network->code)
            ->where('consumed', false)
            ->whereIn('to_address', $addresses)
            ->orderBy('id')
            ->get()
            ->map(fn (MockChainTransaction $row) => $this->toDto($row))
            ->all();
    }

    public function fetchTransaction(BlockchainNetwork $network, string $txHash): ?IncomingTransaction
    {
        $row = MockChainTransaction::query()
            ->where('network_code', $network->code)
            ->where('tx_hash', $txHash)
            ->first();

        return $row ? $this->toDto($row) : null;
    }

    public function healthCheck(): bool
    {
        return true;
    }

    public function inject(array $attributes): MockChainTransaction
    {
        return MockChainTransaction::query()->create(array_merge([
            'log_index' => 0,
            'from_address' => 'TMOCKFROM000000000000000000000001',
            'block_number' => 1,
            'confirmations' => 1,
            'consumed' => false,
            'raw' => [],
        ], $attributes));
    }

    public function markConsumed(string $txHash): void
    {
        MockChainTransaction::query()->where('tx_hash', $txHash)->update(['consumed' => true]);
    }

    public function setConfirmations(string $txHash, int $confirmations): void
    {
        MockChainTransaction::query()->where('tx_hash', $txHash)->update(['confirmations' => $confirmations]);
    }

    private function toDto(MockChainTransaction $row): IncomingTransaction
    {
        return new IncomingTransaction(
            networkCode: $row->network_code,
            assetCode: $row->asset_code,
            txHash: $row->tx_hash,
            logIndex: $row->log_index,
            fromAddress: $row->from_address,
            toAddress: $row->to_address,
            amountDecimal: $row->amount_decimal,
            blockNumber: $row->block_number,
            confirmations: $row->confirmations,
            raw: $row->raw ?? [],
        );
    }
}
