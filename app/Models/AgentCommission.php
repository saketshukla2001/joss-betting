<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    protected $fillable = [
        'agent_id',
        'player_id',
        'bet_id',
        'stake',
        'rate',
        'basis',
        'amount',
        'status',
        'paid_at',
        'payout_reference',
    ];

    protected function casts(): array
    {
        return [
            'stake' => 'float',
            'rate' => 'float',
            'amount' => 'float',
            'paid_at' => 'datetime',
        ];
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function getBasisLabelAttribute(): string
    {
        return $this->basis === 'net_loss' ? 'Net loss' : 'Turnover';
    }

    public function getStatusClassAttribute(): string
    {
        return $this->status === 'paid' ? 'success' : 'warning';
    }
}
