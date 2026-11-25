<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketMarketEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cricket_market_rule_id', 'provider_match_id', 'event', 'message', 'payload', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function rule()
    {
        return $this->belongsTo(CricketMarketRule::class, 'cricket_market_rule_id');
    }
}
