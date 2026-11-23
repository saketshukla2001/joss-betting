<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchGame extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'sport_id', 'league', 'home_team', 'away_team', 'start_time',
        'status', 'home_score', 'away_score', 'result', 'featured',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'featured' => 'boolean',
            'home_score' => 'integer',
            'away_score' => 'integer',
        ];
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function markets()
    {
        return $this->hasMany(Market::class, 'match_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'match_id');
    }

    public function getLabelAttribute(): string
    {
        return $this->home_team.' vs '.$this->away_team;
    }

    public function getScoreAttribute(): ?string
    {
        if ($this->home_score === null || $this->away_score === null) {
            return null;
        }

        return $this->home_score.' - '.$this->away_score;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'upcoming' => 'Upcoming',
            'live' => 'Live',
            'settled' => 'Settled',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'live' => 'success',
            'upcoming' => 'info',
            'settled' => 'muted',
            'cancelled' => 'danger',
            default => 'muted',
        };
    }

    public function getResultLabelAttribute(): ?string
    {
        if (! $this->result) {
            return null;
        }

        return match ($this->result) {
            'home' => $this->home_team,
            'away' => $this->away_team,
            'draw' => 'Draw',
            default => null,
        };
    }

    public function isBettingOpen(): bool
    {
        return in_array($this->status, ['upcoming', 'live'], true);
    }

    public function scopeBettingOpen($query)
    {
        return $query->whereIn('status', ['upcoming', 'live'])
            ->where(function ($query) {
                $query->where('status', 'live')
                    ->orWhere('start_time', '>=', now()->subHours(6));
            });
    }
}
