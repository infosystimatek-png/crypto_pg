<?php

use App\Domain\Merchants\MerchantProvisioningService;
use App\Models\BlockchainAsset;
use App\Models\BlockchainNetwork;
use App\Models\Merchant;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Database\Seeders\BlockchainCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function seedGatewayCatalog(): BlockchainAsset
{
    (new BlockchainCatalogSeeder)->run();
    $network = BlockchainNetwork::query()->where('code', 'TRON')->firstOrFail();
    $network->update(['confirmation_threshold' => 1]);

    return $network->assets()->firstOrFail();
}

/**
 * @return array{merchant: Merchant, api_key: string, webhook_secret: string, owner: User, asset: BlockchainAsset}
 */
function provisionMerchant(array $overrides = []): array
{
    $asset = seedGatewayCatalog();
    $owner = User::factory()->create($overrides);
    $created = app(MerchantProvisioningService::class)->create(
        'Acme',
        $owner,
        'https://merchant.test/webhooks/payment',
    );

    WebhookEndpoint::query()->where('merchant_id', $created['merchant']->id)->update(['is_active' => true]);

    return $created + ['owner' => $owner, 'asset' => $asset];
}

function authApi(string $apiKey): array
{
    return ['Authorization' => 'Bearer '.$apiKey, 'Accept' => 'application/json'];
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
