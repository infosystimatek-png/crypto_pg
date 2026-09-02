<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'merchant_id',
        'network_id',
        'label',
        'custody_backend',
        'key_ref',
        'status',
        'next_derivation_index',
    ];

    protected $hidden = [
        'key_ref',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PaymentAddress::class);
    }
}
