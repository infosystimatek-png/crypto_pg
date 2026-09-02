<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockchainTransactionConfirmation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'blockchain_transaction_id',
        'confirmations',
        'block_number',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
            'confirmations' => 'integer',
            'block_number' => 'integer',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(BlockchainTransaction::class, 'blockchain_transaction_id');
    }
}
