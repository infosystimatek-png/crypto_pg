<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockchainSyncCursor extends Model
{
    protected $fillable = [
        'network_id',
        'cursor',
        'last_polled_at',
    ];

    protected function casts(): array
    {
        return [
            'last_polled_at' => 'datetime',
        ];
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }
}
