<?php

use App\Domain\Merchants\MerchantProvisioningService;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake(['merchant.test/*' => Http::response(['ok' => true], 200)]);
});

test('revoked api keys cannot create payments', function () {
    $ctx = provisionMerchant();
    $credential = $ctx['merchant']->apiCredentials()->first();
    app(MerchantProvisioningService::class)->revokeApiKey($credential);

    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'SEC-1',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi($ctx['api_key']))->assertUnauthorized();
});

test('merchants cannot read another merchant payment', function () {
    $a = provisionMerchant();
    $b = provisionMerchant();

    $created = $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'SEC-2',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi($a['api_key']));

    $id = $created->json('data.payment_id');

    $this->getJson('/api/v1/payments/'.$id, authApi($b['api_key']))->assertNotFound();
    $this->getJson('/api/v1/payments/'.$id, authApi($a['api_key']))->assertOk();
});

test('non-admin cannot access admin panel', function () {
    $ctx = provisionMerchant();
    $this->actingAs($ctx['owner'])->get('/admin')->assertForbidden();
});

test('admin can access admin panel', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('admin without a merchant is redirected from merchant payment pages', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/payments')->assertRedirect(route('admin.payments'));
    $this->actingAs($admin)->get('/ledger')->assertRedirect(route('admin.ledger'));
});

test('admin ledger page highlights ledger instead of admin in the primary nav', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)->get('/admin/ledger')->assertOk()->getContent();

    expect($html)->toContain('border-indigo-400');
    expect(substr_count($html, 'border-b-2 border-indigo-400'))->toBeGreaterThan(0);
});
