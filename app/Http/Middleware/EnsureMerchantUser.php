<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMerchantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $merchant = $user->primaryMerchant();
        if (! $merchant instanceof Merchant) {
            abort(403, 'No merchant account is linked to this user.');
        }

        $request->attributes->set('merchant', $merchant);

        return $next($request);
    }
}
