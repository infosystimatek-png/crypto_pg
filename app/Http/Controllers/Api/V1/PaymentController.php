<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payments\CreatePaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Merchant;
use App\Models\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(CreatePaymentRequest $request, CreatePaymentService $service): JsonResponse
    {
        /** @var Merchant $merchant */
        $merchant = $request->attributes->get('merchant');
        $hash = hash('sha256', json_encode($request->validated(), JSON_THROW_ON_ERROR));

        $payment = $service->create(
            $merchant,
            $request->validated(),
            $request->header('Idempotency-Key'),
            $hash,
        );

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $paymentId): PaymentResource
    {
        /** @var Merchant $merchant */
        $merchant = $request->attributes->get('merchant');

        $payment = PaymentRequest::query()
            ->with(['asset', 'network', 'paymentAddress', 'blockchainTransaction'])
            ->where('merchant_id', $merchant->id)
            ->where('public_id', $paymentId)
            ->firstOrFail();

        return new PaymentResource($payment);
    }

    public function index(Request $request)
    {
        /** @var Merchant $merchant */
        $merchant = $request->attributes->get('merchant');

        $payments = PaymentRequest::query()
            ->with(['asset', 'network', 'paymentAddress', 'blockchainTransaction'])
            ->where('merchant_id', $merchant->id)
            ->latest('id')
            ->paginate(50);

        return PaymentResource::collection($payments);
    }
}
