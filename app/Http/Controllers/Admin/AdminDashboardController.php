<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Ledger\LedgerService;
use App\Domain\Merchants\MerchantProvisioningService;
use App\Domain\Reconciliation\ReconciliationService;
use App\Domain\Shared\Money;
use App\Domain\Webhooks\WebhookDeliveryService;
use App\Http\Controllers\Controller;
use App\Models\BlockchainAsset;
use App\Models\BlockchainTransaction;
use App\Models\LedgerJournalEntry;
use App\Models\Merchant;
use App\Models\PaymentRequest;
use App\Models\ReconciliationRun;
use App\Models\User;
use App\Models\WebhookDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'merchants' => Merchant::query()->count(),
            'payments' => PaymentRequest::query()->count(),
            'credited' => PaymentRequest::query()->where('status', 'CREDITED')->count(),
            'exceptions' => ReconciliationRun::query()->latest('id')->first(),
        ]);
    }

    public function merchants(): View
    {
        return view('admin.merchants', [
            'merchants' => Merchant::query()->with('balanceProjections.asset')->latest('id')->paginate(30),
        ]);
    }

    public function showMerchant(Merchant $merchant): View
    {
        $merchant->load(['users', 'apiCredentials', 'webhookEndpoints', 'balanceProjections.asset']);

        return view('admin.merchant-show', ['merchant' => $merchant]);
    }

    public function storeMerchant(Request $request, MerchantProvisioningService $provisioning): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'exists:users,email'],
            'callback_url' => ['nullable', 'url'],
        ]);

        $owner = User::query()->where('email', $data['owner_email'])->firstOrFail();
        $result = $provisioning->create($data['name'], $owner, $data['callback_url'] ?? null);

        return redirect()
            ->route('admin.merchants.show', $result['merchant'])
            ->with('status', 'Merchant created. API key (shown once): '.$result['api_key'].' webhook secret (shown once): '.$result['webhook_secret']);
    }

    public function payments(): View
    {
        return view('admin.payments', [
            'payments' => PaymentRequest::query()->with(['merchant', 'asset', 'network', 'paymentAddress', 'blockchainTransaction'])->latest('id')->paginate(40),
        ]);
    }

    public function showPayment(PaymentRequest $payment): View
    {
        $payment->load(['merchant', 'asset', 'network', 'paymentAddress', 'blockchainTransaction', 'journalEntries.postings']);

        return view('admin.payment-show', ['payment' => $payment]);
    }

    public function transactions(): View
    {
        return view('admin.transactions', [
            'transactions' => BlockchainTransaction::query()->with(['network', 'asset', 'paymentRequest'])->latest('id')->paginate(40),
        ]);
    }

    public function ledger(): View
    {
        return view('admin.ledger', [
            'entries' => LedgerJournalEntry::query()->with(['merchant', 'postings.account', 'paymentRequest'])->latest('id')->paginate(40),
        ]);
    }

    public function adjust(Request $request, Merchant $merchant, LedgerService $ledger): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:blockchain_assets,id'],
            'direction' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'string'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $asset = BlockchainAsset::query()->findOrFail($data['asset_id']);
        $money = Money::fromDecimal($data['amount'], $asset->decimals, $asset->code);
        $ledger->adjustMerchant(
            $merchant,
            $asset,
            $money,
            $data['direction'],
            $data['reason'],
            $request->user()->id,
            'adj:'.$merchant->id.':'.now()->timestamp.':'.$request->user()->id,
        );

        return back()->with('status', 'Adjustment posted.');
    }

    public function webhooks(): View
    {
        return view('admin.webhooks', [
            'deliveries' => WebhookDelivery::query()->with(['event.merchant', 'endpoint'])->latest('id')->paginate(40),
        ]);
    }

    public function retryWebhook(WebhookDelivery $delivery, WebhookDeliveryService $service): RedirectResponse
    {
        $service->retry($delivery);

        return back()->with('status', 'Webhook retry attempted.');
    }

    public function reconciliation(ReconciliationService $service, Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $run = $service->run();

            return redirect()->route('admin.reconciliation.show', $run);
        }

        return view('admin.reconciliation', [
            'runs' => ReconciliationRun::query()->latest('id')->paginate(20),
        ]);
    }

    public function showReconciliation(ReconciliationRun $run): View
    {
        $run->load('items.paymentRequest');

        return view('admin.reconciliation-show', ['run' => $run]);
    }
}
