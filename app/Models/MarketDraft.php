<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketDraft extends Model
{
    protected $fillable = [
        'provider', 'provider_match_id', 'match_id', 'match_label', 'name',
        'selections', 'status', 'notes', 'created_by', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'selections' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function match()
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'review'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
