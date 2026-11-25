<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $fillable = [
        'bet_code', 'user_id', 'match_id', 'market_id', 'selection_id',
        'sport_name', 'league', 'match_label', 'market_name', 'selection_name',
        'odds', 'stake', 'potential_return', 'status', 'payout', 'settled_at', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'odds' => 'float',
            'stake' => 'float',
            'potential_return' => 'float',
            'payout' => 'float',
            'settled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id')->withoutGlobalScope('real_money');
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function selection()
    {
        return $this->belongsTo(Selection::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Open',
            'won' => 'Won',
            'lost' => 'Lost',
            'void' => 'Void',
            default => ucfirst($this->status),
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'won' => 'success',
            'lost' => 'danger',
            'void' => 'muted',
            default => 'muted',
        };
    }

    public function getNetAttribute(): float
    {
        return match ($this->status) {
            'won' => round($this->payout - $this->stake, 2),
            'lost' => -$this->stake,
            'void' => 0.0,
            default => 0.0,
        };
    }
}
