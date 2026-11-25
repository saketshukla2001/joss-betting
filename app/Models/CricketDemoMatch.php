<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketDemoMatch extends Model
{
    protected $fillable = [
        'team1', 'team2', 'batting', 'status', 'innings', 'runs', 'wickets', 'legal_balls', 'auto_bowl',
    ];

    protected function casts(): array
    {
        return [
            'innings' => 'integer',
            'runs' => 'integer',
            'wickets' => 'integer',
            'legal_balls' => 'integer',
            'auto_bowl' => 'boolean',
        ];
    }

    public function balls()
    {
        return $this->hasMany(CricketDemoBall::class)->orderBy('seq');
    }

    public function providerMatchId(): string
    {
        return 'demo-'.$this->id;
    }
}
