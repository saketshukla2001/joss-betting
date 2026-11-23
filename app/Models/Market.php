<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $fillable = ['match_id', 'name', 'auto_settle', 'status', 'sort_order'];

    protected function casts(): array
    {
        return [
            'auto_settle' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function selections()
    {
        return $this->hasMany(Selection::class)->orderBy('sort_order');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
