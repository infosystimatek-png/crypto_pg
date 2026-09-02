<?php

namespace App\Policies;

use App\Models\Merchant;
use App\Models\User;

class MerchantPolicy
{
    public function view(User $user, Merchant $merchant): bool
    {
        return $user->isAdmin() || $user->merchants()->where('merchants.id', $merchant->id)->exists();
    }

    public function manage(User $user, Merchant $merchant): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->merchants()->where('merchants.id', $merchant->id)->wherePivot('role', 'owner')->exists();
    }
}
