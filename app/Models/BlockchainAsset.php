<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockchainAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'network_id',
        'code',
        'name',
        'contract_address',
        'decimals',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'decimals' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class, 'asset_id');
    }
}
