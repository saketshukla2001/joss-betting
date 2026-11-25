<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketMarketRule extends Model
{
    protected $fillable = [
        'market_id', 'provider', 'provider_match_id', 'fingerprint', 'template',
        'batting_team', 'innings', 'target_over', 'target_ball', 'threshold',
        'trigger_event', 'close_rule', 'resolve_rule', 'source', 'phase',
        'parameters', 'snapshot', 'generation_meta', 'opened_at', 'closed_at', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'snapshot' => 'array',
            'generation_meta' => 'array',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'settled_at' => 'datetime',
            'innings' => 'integer',
            'target_over' => 'integer',
            'target_ball' => 'integer',
            'threshold' => 'integer',
        ];
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function events()
    {
        return $this->hasMany(CricketMarketEvent::class);
    }
}
