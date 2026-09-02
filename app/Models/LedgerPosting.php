<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerPosting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'asset_id',
        'direction',
        'amount_minor',
        'balance_after_minor',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(LedgerJournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(BlockchainAsset::class, 'asset_id');
    }
}
