<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;

class MatchController extends Controller
{
    public function show(int $match)
    {
        $match = MatchGame::withoutGlobalScope('real_money')->findOrFail($match);

        $match->load([
            'sport',
            'markets' => fn ($query) => $query->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'suspended' THEN 1 WHEN 'settled' THEN 2 ELSE 3 END")->orderBy('sort_order'),
            'markets.selections' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $related = MatchGame::with([
            'sport',
            'markets' => fn ($query) => $query->where('status', 'open')->orderBy('sort_order'),
            'markets.selections' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order'),
        ])
            ->where('sport_id', $match->sport_id)
            ->where('id', '!=', $match->id)
            ->whereIn('status', ['upcoming', 'live'])
            ->orderBy('start_time')
            ->limit(4)
            ->get();

        return view('matches.show', compact('match', 'related'));
    }
}
