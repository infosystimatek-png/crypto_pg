<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockchainNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'chain_id',
        'is_testnet',
        'is_enabled',
        'confirmation_threshold',
        'adapter',
        'explorer_url',
        'native_symbol',
    ];

    protected function casts(): array
    {
        return [
            'is_testnet' => 'boolean',
            'is_enabled' => 'boolean',
            'confirmation_threshold' => 'integer',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BlockchainAsset::class, 'network_id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'network_id');
    }
}
