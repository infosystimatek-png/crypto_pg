<?php

namespace App\Http\Middleware;

use App\Domain\Merchants\MerchantProvisioningService;
use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMerchantApi
{
    public function __construct(private readonly MerchantProvisioningService $provisioning) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-Api-Key');
        if (! $token) {
            return response()->json(['message' => 'Missing API credentials.'], 401);
        }

        $credential = $this->provisioning->authenticateApiKey($token);
        if (! $credential) {
            return response()->json(['message' => 'Invalid or revoked API key.'], 401);
        }

        $merchant = $credential->merchant;
        if (! $merchant || ! $merchant->isActive()) {
            return response()->json(['message' => 'Merchant is not active.'], 403);
        }

        $request->attributes->set('merchant', $merchant);
        $request->attributes->set('api_credential', $credential);
        app()->instance(Merchant::class, $merchant);

        return $next($request);
    }
}
