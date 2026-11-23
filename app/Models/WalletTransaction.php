<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_after', 'status',
        'reference_type', 'reference_id', 'description', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'balance_after' => 'float',
            'meta' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'bet' => 'Bet Placed',
            'win' => 'Bet Won',
            'refund' => 'Refund',
            'admin_credit' => 'Admin Credit',
            'admin_debit' => 'Admin Debit',
            'bonus' => 'Bonus',
            default => ucfirst($this->type),
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'deposit' => '↓',
            'withdrawal' => '↑',
            'bet' => '🎯',
            'win' => '🏆',
            'refund' => '↩',
            'admin_credit' => '+',
            'admin_debit' => '−',
            'bonus' => '🎁',
            default => '•',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
