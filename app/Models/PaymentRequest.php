<?php

namespace App\Models;

use App\Domain\Payments\PaymentStatus;
use App\Domain\Shared\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'merchant_id',
        'merchant_order_id',
        'network_id',
        'asset_id',
        'payment_address_id',
        'blockchain_transaction_id',
        'amount_minor',
        'received_amount_minor',
        'status',
        'qr_payload',
        'callback_url',
        'required_confirmations',
        'confirmations',
        'correlation_id',
        'metadata',
        'expires_at',
        'detected_at',
        'confirmed_at',
        'credited_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'metadata' => 'array',
            'expires_at' => 'datetime',
            'detected_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'credited_at' => 'datetime',
            'required_confirmations' => 'integer',
            'confirmations' => 'integer',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(BlockchainNetwork::class, 'network_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(BlockchainAsset::class, 'asset_id');
    }

    public function paymentAddress(): BelongsTo
    {
        return $this->belongsTo(PaymentAddress::class);
    }

    public function blockchainTransaction(): BelongsTo
    {
        return $this->belongsTo(BlockchainTransaction::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(LedgerJournalEntry::class);
    }

    public function expectedMoney(): Money
    {
        $this->loadMissing('asset');

        return new Money($this->amount_minor, $this->asset->decimals, $this->asset->code);
    }

    public function receivedMoney(): Money
    {
        $this->loadMissing('asset');

        return new Money($this->received_amount_minor, $this->asset->decimals, $this->asset->code);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
