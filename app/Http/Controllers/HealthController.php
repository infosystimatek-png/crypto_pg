<?php

namespace App\Http\Controllers;

use App\Domain\Blockchain\BlockchainAdapterRegistry;
use App\Models\BlockchainNetwork;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(BlockchainAdapterRegistry $registry): JsonResponse
    {
        $checks = [
            'database' => $this->database(),
            'queue' => $this->queue(),
            'scheduler' => true,
            'blockchain' => $this->blockchain($registry),
        ];

        $ok = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }

    private function database(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function queue(): bool
    {
        try {
            return Schema::hasTable('jobs');
        } catch (Throwable) {
            return false;
        }
    }

    private function blockchain(BlockchainAdapterRegistry $registry): bool
    {
        try {
            $network = BlockchainNetwork::query()->where('is_enabled', true)->first();
            if (! $network) {
                return false;
            }

            return $registry->forNetwork($network)->healthCheck();
        } catch (Throwable) {
            return false;
        }
    }
}
