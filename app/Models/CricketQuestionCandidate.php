<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketQuestionCandidate extends Model
{
    protected $fillable = [
        'provider', 'provider_match_id', 'fingerprint', 'template', 'question',
        'parameters', 'source', 'status', 'market_id', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
        ];
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
