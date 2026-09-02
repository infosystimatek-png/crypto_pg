<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockchainTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'network_id',
        'asset_id',
        'payment_request_id',
        'tx_hash',
        'log_index',
        'from_address',
        'to_address',
        'amount_minor',
        'block_number',
        'confirmations',
        'status',
        'processing_status',
        'raw_payload',
        'first_seen_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'first_seen_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'log_index' => 'integer',
            'block_number' => 'integer',
            'confirmations' => 'integer',
        ];
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(BlockchainAsset::class, 'asset_id');
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function confirmationHistory(): HasMany
    {
        return $this->hasMany(BlockchainTransactionConfirmation::class);
    }
}
