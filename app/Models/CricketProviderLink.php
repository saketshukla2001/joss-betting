<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketProviderLink extends Model
{
    protected $fillable = ['match_id', 'provider', 'provider_match_id'];

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id')->withoutGlobalScope('real_money');
    }
}
