<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerJournalEntry extends Model
{
    protected $fillable = [
        'public_id',
        'merchant_id',
        'type',
        'status',
        'description',
        'payment_request_id',
        'blockchain_transaction_id',
        'idempotency_key',
        'created_by_user_id',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function blockchainTransaction(): BelongsTo
    {
        return $this->belongsTo(BlockchainTransaction::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function postings(): HasMany
    {
        return $this->hasMany(LedgerPosting::class, 'journal_entry_id');
    }
}
