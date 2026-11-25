<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketDemoBall extends Model
{
    protected $fillable = [
        'cricket_demo_match_id', 'seq', 'kind', 'runs', 'legal', 'wicket',
    ];

    protected function casts(): array
    {
        return [
            'seq' => 'integer',
            'runs' => 'integer',
            'legal' => 'boolean',
            'wicket' => 'boolean',
        ];
    }
}
