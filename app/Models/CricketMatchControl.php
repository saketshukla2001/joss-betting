<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CricketMatchControl extends Model
{
    protected $fillable = ['provider_match_id', 'generation_enabled'];

    protected function casts(): array
    {
        return [
            'generation_enabled' => 'boolean',
        ];
    }
}
