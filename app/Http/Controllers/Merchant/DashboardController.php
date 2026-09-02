<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantBalanceProjection;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $merchant = $this->merchant($request);
        $merchantId = $merchant?->id;

        $balances = $merchantId
            ? MerchantBalanceProjection::query()->with('asset')->where('merchant_id', $merchantId)->get()
            : collect();

        $today = $merchantId
            ? PaymentRequest::query()->where('merchant_id', $merchantId)->whereDate('created_at', today())->count()
            : 0;

        return view('merchant.dashboard', [
            'merchant' => $merchant,
            'balances' => $balances,
            'today' => $today,
            'successful' => $merchantId ? PaymentRequest::query()->where('merchant_id', $merchantId)->where('status', 'CREDITED')->count() : 0,
            'pending' => $merchantId ? PaymentRequest::query()->where('merchant_id', $merchantId)->whereIn('status', ['CREATED', 'WAITING_FOR_PAYMENT', 'TRANSACTION_DETECTED', 'CONFIRMING'])->count() : 0,
            'expired' => $merchantId ? PaymentRequest::query()->where('merchant_id', $merchantId)->where('status', 'EXPIRED')->count() : 0,
            'recent' => $merchantId ? PaymentRequest::query()->with(['asset', 'network', 'paymentAddress'])->where('merchant_id', $merchantId)->latest('id')->limit(20)->get() : collect(),
        ]);
    }

    public function payments(Request $request): View
    {
        $merchant = $this->merchant($request);

        return view('merchant.payments', [
            'merchant' => $merchant,
            'payments' => PaymentRequest::query()
                ->with(['asset', 'network', 'paymentAddress', 'blockchainTransaction'])
                ->where('merchant_id', $merchant->id)
                ->latest('id')
                ->paginate(30),
        ]);
    }

    public function showPayment(Request $request, string $payment): View
    {
        $merchant = $this->merchant($request);
        $model = PaymentRequest::query()
            ->with(['asset', 'network', 'paymentAddress', 'blockchainTransaction'])
            ->where('merchant_id', $merchant->id)
            ->where('public_id', $payment)
            ->firstOrFail();

        return view('merchant.payment-show', ['payment' => $model, 'merchant' => $merchant]);
    }

    public function ledger(Request $request): View
    {
        $merchant = $this->merchant($request);

        return view('merchant.ledger', [
            'merchant' => $merchant,
            'entries' => $merchant->ledgerAccounts()->with(['asset'])->get(),
            'balances' => MerchantBalanceProjection::query()->with('asset')->where('merchant_id', $merchant->id)->get(),
        ]);
    }

    private function merchant(Request $request): ?Merchant
    {
        if ($request->user()->isAdmin() && $request->query('merchant_id')) {
            return Merchant::query()->where('public_id', $request->query('merchant_id'))->firstOrFail();
        }

        return $request->attributes->get('merchant') ?? $request->user()->primaryMerchant();
    }
}
