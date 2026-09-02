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

        $merchant = $user->primaryMerchant();
        if ($merchant instanceof Merchant) {
            $request->attributes->set('merchant', $merchant);

            return $next($request);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        abort(403, 'No merchant account is linked to this user.');
    }
}
