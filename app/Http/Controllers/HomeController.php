<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\MatchGame;
use App\Models\Sport;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $with = [
            'sport',
            'markets' => fn ($query) => $query->where('status', 'open')->orderBy('sort_order'),
            'markets.selections' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order'),
        ];

        $staleHours = max(0, (int) setting('live_stale_hours', 6));

        $live = MatchGame::with($with)
            ->where('status', 'live')
            ->when(live_only_cricket(), fn ($query) => $query->whereHas('sport', fn ($sport) => $sport->where('slug', 'cricket')))
            ->when($staleHours > 0, fn ($query) => $query->where('start_time', '>=', now()->subHours($staleHours)))
            ->orderBy('start_time')
            ->limit(4)
            ->get();

        $featured = MatchGame::with($with)
            ->bettingOpen()
            ->orderByDesc('featured')
            ->orderBy('start_time')
            ->limit(9)
            ->get();

        $sports = Sport::active()
            ->withCount(['matches' => fn ($query) => $query->whereIn('status', ['upcoming', 'live'])])
            ->get();

        $heroFallback = $featured
            ->filter(fn ($match) => $match->status !== 'live' || $match->start_time->gte(now()->subHours($staleHours)))
            ->take(3);

        if (live_only_cricket()) {
            $heroFallback = $featured
                ->filter(fn ($match) => $match->sport?->slug === 'cricket')
                ->filter(fn ($match) => $match->status !== 'live' || $match->start_time->gte(now()->subHours($staleHours)))
                ->take(3);
        }

        $stats = [
            'matches' => MatchGame::whereIn('status', ['upcoming', 'live'])->count(),
            'live' => MatchGame::where('status', 'live')
                ->when(live_only_cricket(), fn ($query) => $query->whereHas('sport', fn ($sport) => $sport->where('slug', 'cricket')))
                ->when($staleHours > 0, fn ($query) => $query->where('start_time', '>=', now()->subHours($staleHours)))
                ->count(),
            'paid_out' => (float) Bet::where('status', 'won')->sum('payout'),
            'bets' => Bet::count(),
        ];

        return view('home', compact('live', 'featured', 'sports', 'stats', 'heroFallback'));
    }
}
