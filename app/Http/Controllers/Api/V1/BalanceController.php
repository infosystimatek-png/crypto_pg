<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Money;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantBalanceProjection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Merchant $merchant */
        $merchant = $request->attributes->get('merchant');

        $rows = MerchantBalanceProjection::query()
            ->with('asset.network')
            ->where('merchant_id', $merchant->id)
            ->get()
            ->map(function (MerchantBalanceProjection $row) {
                $decimals = $row->asset->decimals;
                $code = $row->asset->code;

                return [
                    'currency' => $code,
                    'network' => $row->asset->network->code,
                    'available' => (new Money($row->available_minor, $decimals, $code))->toFixed(),
                    'pending' => (new Money($row->pending_minor, $decimals, $code))->toFixed(),
                    'reserved' => (new Money($row->reserved_minor, $decimals, $code))->toFixed(),
                ];
            });

        return response()->json(['data' => $rows]);
    }
}
