<?php

namespace App\Domain\Merchants;

use App\Domain\Audit\AuditLogger;
use App\Domain\Shared\PublicId;
use App\Models\ApiCredential;
use App\Models\Merchant;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class MerchantProvisioningService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @return array{merchant: Merchant, api_key: string, webhook_secret: string}
     */
    public function create(string $name, User $owner, ?string $callbackUrl = null): array
    {
        return DB::transaction(function () use ($name, $owner, $callbackUrl) {
            $merchant = Merchant::query()->create([
                'public_id' => PublicId::make('MER'),
                'name' => $name,
                'status' => 'active',
                'default_callback_url' => $callbackUrl,
            ]);

            $merchant->users()->attach($owner->id, ['role' => 'owner']);

            $issued = $this->issueApiKey($merchant, 'Default key');
            $webhookSecret = $this->createWebhookEndpoint($merchant, $callbackUrl);

            $this->audit->log('merchant.created', $merchant, [
                'name' => $name,
            ], 'system', $owner->id);

            return [
                'merchant' => $merchant,
                'api_key' => $issued['plain'],
                'webhook_secret' => $webhookSecret,
            ];
        });
    }

    /**
     * @return array{credential: ApiCredential, plain: string}
     */
    public function issueApiKey(Merchant $merchant, string $name, string $environment = 'live'): array
    {
        $lookup = Str::lower((string) Str::ulid());
        $plain = 'gw_'.$environment.'_'.$lookup.'_'.bin2hex(random_bytes(16));
        $prefix = $lookup;

        $credential = ApiCredential::query()->create([
            'merchant_id' => $merchant->id,
            'name' => $name,
            'key_prefix' => $prefix,
            'secret_hash' => Hash::make($plain),
            'environment' => $environment,
        ]);

        $this->audit->log('api_credential.issued', $credential, [
            'merchant_id' => $merchant->public_id,
            'prefix' => $prefix,
        ]);

        return ['credential' => $credential, 'plain' => $plain];
    }

    public function revokeApiKey(ApiCredential $credential): void
    {
        $credential->update(['revoked_at' => now()]);
        $this->audit->log('api_credential.revoked', $credential);
    }

    public function createWebhookEndpoint(Merchant $merchant, ?string $url = null): string
    {
        $secret = 'whsec_'.Str::random(48);

        WebhookEndpoint::query()->create([
            'merchant_id' => $merchant->id,
            'url' => $url ?: ($merchant->default_callback_url ?: 'https://invalid.local/webhooks/payment'),
            'secret_encrypted' => encrypt($secret),
            'is_active' => (bool) $url,
            'subscribed_events' => ['*'],
        ]);

        return $secret;
    }

    public function authenticateApiKey(string $plain): ?ApiCredential
    {
        if (! preg_match('/^gw_(?:live|test)_([0-9a-z]{26})_/', $plain, $matches)) {
            return null;
        }

        $credential = ApiCredential::query()
            ->where('key_prefix', $matches[1])
            ->whereNull('revoked_at')
            ->first();

        if (! $credential || ! Hash::check($plain, $credential->secret_hash)) {
            return null;
        }

        $credential->forceFill(['last_used_at' => now()])->save();

        return $credential;
    }
}
