<?php

namespace App\Domain\Blockchain\Adapters;

use App\Domain\Blockchain\Contracts\BlockchainAdapterInterface;
use App\Domain\Blockchain\DTO\IncomingTransaction;
use App\Models\BlockchainNetwork;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TronGrid-backed TRC-20 adapter. Hidden behind BlockchainAdapterInterface so
 * it can be replaced with a self-hosted node/indexer later.
 */
final class TronGridAdapter implements BlockchainAdapterInterface
{
    public function networkCode(): string
    {
        return 'TRON';
    }

    public function supports(BlockchainNetwork $network): bool
    {
        return $network->adapter === 'trongrid' && strtoupper($network->code) === 'TRON';
    }

    public function fetchIncoming(BlockchainNetwork $network, array $addresses, mixed $cursor): array
    {
        $out = [];
        $base = rtrim((string) config('gateway.trongrid.base_url'), '/');
        $key = config('gateway.trongrid.api_key');

        foreach ($addresses as $address) {
            $request = Http::timeout(15)->acceptJson();
            if ($key) {
                $request = $request->withHeaders(['TRON-PRO-API-KEY' => $key]);
            }

            $response = $request->get($base.'/v1/accounts/'.$address.'/transactions/trc20', [
                'limit' => 50,
                'only_to' => true,
            ]);

            if (! $response->successful()) {
                Log::warning('trongrid.fetch_failed', [
                    'address' => $address,
                    'status' => $response->status(),
                ]);

                continue;
            }

            foreach ($response->json('data') ?? [] as $row) {
                $out[] = new IncomingTransaction(
                    networkCode: 'TRON',
                    assetCode: $row['token_info']['symbol'] ?? 'USDT',
                    txHash: $row['transaction_id'],
                    logIndex: 0,
                    fromAddress: $row['from'],
                    toAddress: $row['to'],
                    amountDecimal: $this->fromSun((string) ($row['value'] ?? '0'), (int) ($row['token_info']['decimals'] ?? 6)),
                    blockNumber: isset($row['block']) ? (int) $row['block'] : null,
                    confirmations: 1,
                    contractAddress: $row['token_info']['address'] ?? null,
                    raw: $row,
                );
            }
        }

        return $out;
    }

    public function fetchTransaction(BlockchainNetwork $network, string $txHash): ?IncomingTransaction
    {
        return null;
    }

    public function healthCheck(): bool
    {
        $base = rtrim((string) config('gateway.trongrid.base_url'), '/');

        try {
            return Http::timeout(5)->get($base.'/wallet/getnowblock')->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    private function fromSun(string $value, int $decimals): string
    {
        if ($value === '') {
            return '0';
        }

        $padded = str_pad($value, $decimals + 1, '0', STR_PAD_LEFT);
        $int = substr($padded, 0, strlen($padded) - $decimals);
        $frac = substr($padded, -$decimals);

        return rtrim($int.'.'.$frac, '0') ?: '0';
    }
}
