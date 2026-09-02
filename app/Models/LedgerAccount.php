<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    protected $fillable = [
        'public_id',
        'merchant_id',
        'asset_id',
        'type',
        'code',
        'name',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(BlockchainAsset::class, 'asset_id');
    }

    public function postings(): HasMany
    {
        return $this->hasMany(LedgerPosting::class, 'account_id');
    }
}
