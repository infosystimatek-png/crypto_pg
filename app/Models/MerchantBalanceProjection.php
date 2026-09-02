<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantBalanceProjection extends Model
{
    protected $fillable = [
        'merchant_id',
        'asset_id',
        'available_minor',
        'pending_minor',
        'reserved_minor',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(BlockchainAsset::class, 'asset_id');
    }
}
