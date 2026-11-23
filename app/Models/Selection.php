<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Selection extends Model
{
    protected $fillable = ['market_id', 'name', 'result_key', 'odds', 'status', 'result', 'sort_order'];

    protected function casts(): array
    {
        return [
            'odds' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
