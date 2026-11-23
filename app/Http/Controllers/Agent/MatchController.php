<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchGame::with('sport')->whereIn('status', ['live', 'upcoming']);

        if ($request->filled('sport')) {
            $query->whereHas('sport', fn ($q) => $q->where('slug', $request->input('sport')));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league', 'like', "%{$search}%");
            });
        }

        $matches = $query->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('start_time')
            ->paginate(12)
            ->withQueryString();

        $sports = \App\Models\Sport::orderBy('sort_order')->get();

        return view('agent.matches.index', compact('matches', 'sports'));
    }

    public function show(MatchGame $match)
    {
        $match->load([
            'sport',
            'markets' => fn ($q) => $q->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'suspended' THEN 1 WHEN 'settled' THEN 2 ELSE 3 END")->orderBy('sort_order'),
            'markets.selections' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return view('agent.matches.show', compact('match'));
    }
}
