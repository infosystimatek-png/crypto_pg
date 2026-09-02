<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockChainTransaction extends Model
{
    protected $fillable = [
        'network_code',
        'asset_code',
        'tx_hash',
        'log_index',
        'from_address',
        'to_address',
        'amount_decimal',
        'block_number',
        'confirmations',
        'consumed',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'consumed' => 'boolean',
            'raw' => 'array',
            'log_index' => 'integer',
            'block_number' => 'integer',
            'confirmations' => 'integer',
        ];
    }
}
