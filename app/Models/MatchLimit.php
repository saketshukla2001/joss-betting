<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchLimit extends Model
{
    protected $fillable = ['match_id', 'user_id', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'float'];
    }

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
